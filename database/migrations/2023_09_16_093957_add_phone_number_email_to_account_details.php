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
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_intra')->default(false);
            $table->boolean('status')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_details', function (Blueprint $table) {
            //
        });
    }
};
