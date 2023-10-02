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
        Schema::table('account_details', function (Blueprint $table) {
        $table->uuid('country_id')->after("wallet_id");
        $table->uuid('currency_code')->after("country_id");
        $table->uuid('country')->after("currency_code");
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
