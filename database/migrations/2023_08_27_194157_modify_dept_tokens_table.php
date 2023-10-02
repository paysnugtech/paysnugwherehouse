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
            $table->text('public_key')->change();
            $table->text('secret_key')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dept_tokens', function (Blueprint $table) {
            $table->string('public_key')->change();
            $table->string('secret_key')->change();
        });
    }
};
