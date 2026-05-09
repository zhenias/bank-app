<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->string('account_number', 32)->unique();
            $table->bigInteger('balance')->default(0);
            $table->string('currency', 3)->default('PLN');
            $table->string('type', 20)->default('current');
            $table->timestamps();
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained()->onDelete('cascade');
            $table->string('card_number', 16)->unique();
            $table->unsignedTinyInteger('exp_month');
            $table->unsignedSmallInteger('exp_year');
            $table->string('cvv', 4);
            $table->string('status', 20)->default('active');
            $table->string('type', 20)->default('debit');
            $table->timestamps();

            $table->index('account_id');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('from_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignUuid('to_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignUuid('from_card_id')->nullable()->constrained('cards')->onDelete('set null');
            $table->bigInteger('amount');
            $table->string('description')->nullable();
            $table->string('type', 30);
            $table->string('status', 20)->default('pending');
            $table->string('reference', 64)->nullable();
            $table->timestamps();

            $table->index('from_account_id');
            $table->index('to_account_id');
            $table->index('from_card_id');
            $table->index('reference');
        });

        Schema::create('flik_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('card_id')->constrained()->onDelete('cascade');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('active');
            $table->foreignUuid('used_in_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->timestamps();

            $table->index('code');
            $table->index('card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flik_codes');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('accounts');
    }
};
