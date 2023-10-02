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
        Schema::create('ip_whitelists', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('dept_id'); // Department ID to associate with the whitelist
            $table->ipAddress('ip_address');
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('dept_id')->references('id')->on('department')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ip_whitelists');
    }
};
