<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_seeker_details', function (Blueprint $table) {
            $table->id();
            // $table->string('jid');
            $table->unsignedBigInteger('jid');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('designation')->nullable();

            $table->timestamps();

            // $table->foreign(columns: 'jid')->references('id')->on('users');
            $table->foreign('jid')->references('id')->on('users')->onDelete('cascade'); // FIXED
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seeker_details');
    }
};
