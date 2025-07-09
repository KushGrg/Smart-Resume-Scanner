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
        // //
        Schema::create('job_seeker_educations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_seeker_id');

            // $table->foreignId('job_seeker_info_id')->constrained()->onDelete('cascade');
            $table->string('school_name');
            $table->string('location');
            $table->string('degree');
            $table->string('field_of_study');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('job_seeker_id')->references('id')->on('users')->onDelete('cascade'); // FIXED

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('job_seeker_educations');

    }
};
