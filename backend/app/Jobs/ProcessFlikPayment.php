<?php

namespace App\Jobs;

use App\Enums\Flik\FlikCodeStatus;
use App\Enums\Transaction\TransactionStatus;
use App\Exceptions\Financial\InsufficientFundsException;
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
                $tx = Transaction::find($transaction->id);

                if (! $tx || $tx->status !== TransactionStatus::PENDING->value) {
                    $logger->info('ProcessFlikPayment: transaction changed while acquiring lock, skipping', ['transaction_id' => $transaction->id]);
                    return;
                }

                // Funds were already reserved (debited from payer) during flikRedeem.
                // Here we only need to credit the receiver.
                $toAccount = $tx->toAccount()->first();

                if (! $toAccount) {
                    // Destination missing - refund payer and mark failed
                    $fromAccount = $tx->fromAccount()->first();
                    if ($fromAccount) {
                        $fromAccount->update([
                            'balance' => $fromAccount->balance + $tx->amount,
                        ]);
                    }
                    $tx->update(['status' => TransactionStatus::FAILED->value, 'failure_reason' => 'NO_TO_ACCOUNT']);
                    return;
                }

                $toAccount->update([
                    'balance' => $toAccount->balance + $tx->amount,
                ]);

                // Update transaction to completed
                $tx->update(['status' => TransactionStatus::COMPLETED->value]);

                // If associated flik code exists, mark it used (should already be used, but double-check)
                if ($tx->flikCode && $tx->flikCode->status !== FlikCodeStatus::USED->value) {
                    $flikCodeService->markAsUsed($tx->flikCode, $tx);
                }
            });

            $logger->info('ProcessFlikPayment: transaction processed', ['transaction_id' => $transaction->id]);
        } catch (InsufficientFundsException $e) {
            $logger->warning('ProcessFlikPayment: insufficient funds', ['transaction_id' => $transaction->id]);
            $this->failed($e);
            return;
        } catch (\Exception $e) {
            $logger->error('ProcessFlikPayment: unexpected error, rethrowing to allow retry', ['transaction_id' => $transaction->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $transaction = Transaction::where('id', $this->transactionId)->first();
        if (! $transaction) {
            return;
        }

        if ($transaction->status === TransactionStatus::PENDING->value) {
            DB::transaction(function () use ($transaction, $exception) {
                $tx = Transaction::find($transaction->id);
                if (! $tx) {
                    return;
                }

                // Refund reserved funds to payer
                $fromAccount = $tx->fromAccount()->first();
                if ($fromAccount) {
                    $fromAccount->update([
                        'balance' => $fromAccount->balance + $tx->amount,
                    ]);
                }

                $tx->update([
                    'status' => TransactionStatus::FAILED->value,
                    'failure_reason' => substr($exception->getMessage(), 0, 191),
                ]);
            });
        }
    }
}
