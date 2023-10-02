<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->renameColumn('is_reverserd', 'is_reversed');
    });

    Schema::table('bank_transactions', function (Blueprint $table) {
        $table->renameColumn('is_reverserd', 'is_reversed');
    });

    Schema::table('reversal_transactions', function (Blueprint $table) {
        $table->renameColumn('is_reverserd', 'is_reversed');
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
