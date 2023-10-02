<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('dept_id');
            $table->uuid('trx_id');
            $table->uuid('wallet_id');
            $table->string('currency');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('provider_name');
            $table->string('provider_ref');
            $table->string('reference');
            $table->string('session_id')->nulable();
            $table->string('channel');
            $table->string('bank_name');
            $table->string('bank_code');
            $table->string('account_no');
            $table->string('status')->default(2);
            $table->string('is_reverserd')->default(0);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
