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
        Schema::table('dept_tokens', function (Blueprint $table) {
            $table->uuid('dept_id'); 
            $table->string('public_key');
            $table->string('secret_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dept_tokens', function (Blueprint $table) {
            //
        });
    }
};
