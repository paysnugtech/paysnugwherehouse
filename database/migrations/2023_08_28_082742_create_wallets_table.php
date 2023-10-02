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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('dept_id');
            $table->string('currency');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->decimal('legal_balance', 10, 2)->default(0.00);
            $table->timestamps();
            $table->foreign('dept_id')->references('id')->on('department')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
