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
        Schema::create('reversal_transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('dept_id');
            $table->uuid('trx_id');
            $table->uuid('wallet_id');
            $table->string('currency');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('provider_name');
            $table->string('provider_ref');
            $table->string('reference');
            $table->string('channel');
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
        Schema::dropIfExists('reversal_transactions');
    }
};
