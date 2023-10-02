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
        Schema::create('account_details', function (Blueprint $table) {
            $table->uuid();
            $table->uuid('dept_id');
            $table->uuid('wallet_id');
            $table->string('account_number', 20)->unique();
            $table->string('account_holder_name');
            $table->string('provider_code');
            $table->string('account_type');
            $table->string('provider_name');
            $table->boolean('status');
            $table->timestamp('expired_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_details');
    }
};
