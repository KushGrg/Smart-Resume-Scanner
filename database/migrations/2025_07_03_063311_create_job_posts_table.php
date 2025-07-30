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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hid'); // Foreign key to users table
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->date('deadline')->nullable();
            $table->text('requirement')->nullable();
            $table->string('experience')->nullable();
            $table->unsignedBigInteger('min_salary')->nullable();
            $table->unsignedBigInteger('max_salary')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->foreign(columns: 'hid')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
