<?php

namespace App\Jobs;

use App\Enums\Flik\FlikCodeStatus;
use App\Enums\Transaction\TransactionStatus;
use App\Exceptions\Financial\InsufficientFundsException;
use App\Models\Account\Flik\FlikCode;
use App\Models\Account\Transaction\Transaction;
use App\Services\Account\Flik\FlikCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

class ProcessFlikPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $transactionId;

    public int $tries = 5;

    public int $timeout = 120;

    public function __construct(string $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function handle(FlikCodeService $flikCodeService, LoggerInterface $logger): void
    {
        // Load transaction with lock to prevent races
        $transaction = Transaction::where('id', $this->transactionId)->first();

        if (! $transaction) {
            $logger->warning('ProcessFlikPayment: transaction not found', ['transaction_id' => $this->transactionId]);
            return;
        }

        if ($transaction->status !== TransactionStatus::PENDING->value) {
            $logger->info('ProcessFlikPayment: transaction not pending, skipping', ['transaction_id' => $transaction->id, 'status' => $transaction->status]);
            return;
        }

        try {
            DB::transaction(function () use ($transaction, $flikCodeService, $logger) {
                // Re-lock transaction row
                $tx = Transaction::lockForUpdate()->find($transaction->id);

                if (! $tx || $tx->status !== TransactionStatus::PENDING->value) {
                    $logger->info('ProcessFlikPayment: transaction changed while acquiring lock, skipping', ['transaction_id' => $transaction->id]);
                    return;
                }

                // Here we assume funds were reserved (debited) at transaction creation.
                $fromAccount = $tx->fromAccount()->lockForUpdate()->first();

                if (! $fromAccount) {
                    $tx->update(['status' => TransactionStatus::FAILED->value, 'failure_reason' => 'NO_FROM_ACCOUNT']);
                    return;
                }

                // External processor call placeholder - synchronous
                // If there were an external API, call it here and throw on transient error to trigger retry
                // For now we assume success

                // Credit destination account
                if ($tx->to_account_id) {
                    $toAccount = $tx->toAccount()->lockForUpdate()->first();
                    if (! $toAccount) {
                        // Destination missing - refund payer and mark failed
                        $fromAccount->balance += $tx->amount;
                        $fromAccount->save();
                        $tx->update(['status' => TransactionStatus::FAILED->value, 'failure_reason' => 'NO_TO_ACCOUNT']);
                        return;
                    }

                    $toAccount->balance += $tx->amount;
                    $toAccount->save();

                    $fromAccount->balance -= $tx->amount;
                    $fromAccount->save();
                } else {
                    // No destination account - refund and fail
                    $fromAccount->balance += $tx->amount;
                    $fromAccount->save();
                    $tx->update(['status' => TransactionStatus::FAILED->value, 'failure_reason' => 'NO_TO_ACCOUNT']);
                    return;
                }

                // Update transaction to completed
                $tx->update(['status' => TransactionStatus::COMPLETED->value]);

                // If associated flik code exists, mark it used
                if ($tx->flikCode && $tx->flikCode->status !== FlikCodeStatus::USED->value) {
                    $flikCodeService->markAsUsed($tx->flikCode, $tx);
                }
            });

            // emit events / logging
            $logger->info('ProcessFlikPayment: transaction processed', ['transaction_id' => $transaction->id]);
        } catch (InsufficientFundsException $e) {
            // Permanent failure - mark failed and refund in failed()
            $logger->warning('ProcessFlikPayment: insufficient funds', ['transaction_id' => $transaction->id]);
            $this->failed($e);
            return;
        } catch (\Exception $e) {
            // Unexpected error - let the job retry. Do not mark as failed yet; failed() will be called after retries.
            $logger->error('ProcessFlikPayment: unexpected error, rethrowing to allow retry', ['transaction_id' => $transaction->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Called when the job fails permanently after retries
        $transaction = Transaction::where('id', $this->transactionId)->first();
        if (! $transaction) {
            return;
        }

        if ($transaction->status === TransactionStatus::PENDING->value) {
            // Attempt to refund reserved funds to payer
            DB::transaction(function () use ($transaction, $exception) {
                $tx = Transaction::lockForUpdate()->find($transaction->id);
                if (! $tx) {
                    return;
                }

                $fromAccount = $tx->fromAccount()->lockForUpdate()->first();
                if ($fromAccount) {
                    $fromAccount->balance += $tx->amount;
                    $fromAccount->save();
                }

                $tx->update([ 'status' => TransactionStatus::FAILED->value, 'failure_reason' => substr($exception->getMessage(), 0, 191) ]);
            });
        }
    }
}

