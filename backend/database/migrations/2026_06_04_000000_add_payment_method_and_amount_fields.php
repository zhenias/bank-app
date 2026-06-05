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
        // Add amount to flik_codes - kwota zapamiętana przy generacji kodu
        Schema::table('flik_codes', function (Blueprint $table) {
            $table->bigInteger('amount')->nullable()->after('code');
        });

        // Add payment_method to transactions - typ metody płatności (transfer, flik_payment)
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method', 30)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flik_codes', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};

