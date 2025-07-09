<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_seeker_id');
            $table->unsignedBigInteger('job_post_id');
            $table->unsignedBigInteger('resume_id');
            $table->enum('status', ['applied', 'shortlisted', 'rejected', 'hired'])->default('applied');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->foreign('job_seeker_id')->references('id')->on('job_seeker_details')->onDelete('cascade');
            $table->foreign('job_post_id')->references('id')->on('job_posts')->onDelete('cascade');
            $table->foreign('resume_id')->references('id')->on('resumes')->onDelete('cascade');

            $table->unique(['job_seeker_id', 'job_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
