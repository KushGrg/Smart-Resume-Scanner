<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jsid');
            $table->unsignedBigInteger('jpostid');
            $table->string('resume_path');
            $table->boolean('status')->default(true);
            $table->text('extracted_text')->nullable();
            $table->boolean('text_extracted')->default(false);
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('jsid')->references('id')->on('job_seeker_details')->onDelete('cascade');
            $table->foreign('jpostid')->references('id')->on('job_posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
