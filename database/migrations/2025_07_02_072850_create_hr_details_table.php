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
        Schema::create('hr_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hid'); // Foreign key to users table
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('orgainzation_name')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();

            $table->foreign(columns: 'hid')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_details');
    }
};
