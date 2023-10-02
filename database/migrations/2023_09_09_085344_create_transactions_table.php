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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('dept_id');
            $table->uuid('wallet_id');
            $table->string('currency');
            $table->decimal('before_balance', 12, 2)->default(0.00);
            $table->decimal('after_balance', 12, 2)->default(0.00);
            $table->string('transaction_type');
            $table->string('provider_name');
            $table->string('provider_ref');
            $table->string('reference');
            $table->string('session_id')->default("NULL");
            $table->string('channel');
            $table->string('status')->default(2);
            $table->string('is_reverserd')->default(0);


            $table->timestamps();
            $table->foreign('dept_id')->references('id')->on('department')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
