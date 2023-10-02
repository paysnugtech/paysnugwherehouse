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
        Schema::create('hooks', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('dept_id');
            $table->uuid('wallet_id');
            $table->string('notification')->nullable();
            $table->string('reversal')->nullable();
            $table->string('settlement')->nullable();
            $table->string('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hooks');
    }
};
