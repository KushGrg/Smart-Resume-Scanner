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
        //
        Schema::create('job_seeker_skill_and_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_seeker_id');

            // $table->foreignId('job_seeker_info_id')->constrained()->onDelete('cascade');
            $table->string(column: 'skills');
            $table->string(column: 'summary');
            $table->timestamps();
            $table->foreign('job_seeker_id')->references('id')->on('users')->onDelete('cascade'); // FIXED

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seeker_skill_and_summaries');
    }
};
