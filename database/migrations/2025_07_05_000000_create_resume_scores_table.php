<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resume_id');
            $table->unsignedBigInteger('job_post_id');
            $table->decimal('score', 5, 4)->nullable();
            $table->json('tfidf_vector')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->foreign('resume_id')->references('id')->on('resumes')->onDelete('cascade');
            $table->foreign('job_post_id')->references('id')->on('job_posts')->onDelete('cascade');

            $table->unique(['resume_id', 'job_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_scores');
    }
};
