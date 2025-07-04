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
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jsid');
            $table->unsignedBigInteger('jpostid');
            $table->string('resume_path');
            $table->boolean('status')->default(value: 1);
            $table->timestamps();

            // $table->foreign(columns: 'jsid')->references('id')->on('job_seeker_details');
            // $table->foreign(columns: 'jpostid')->references('id')->on('job_posts');
            $table->foreign('jsid')->references('id')->on('job_seeker_details')->onDelete('cascade');
            $table->foreign('jpostid')->references('id')->on('job_posts')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
