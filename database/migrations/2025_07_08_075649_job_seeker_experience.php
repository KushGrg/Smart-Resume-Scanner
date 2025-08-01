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
        Schema::create('job_seeker_experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger(column: 'job_seeker_id');

            // $table->foreignId('job_seeker_info_id')->constrained()->onDelete('cascade');
            $table->string('job_title');
            $table->string('employer');
            $table->string('location');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('work_summary')->nullable();
            $table->timestamps();

            $table->foreign(columns: 'job_seeker_id')->references(columns: 'id')->on('users')->onDelete('cascade'); // FIXED

        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seeker_experiences');
        //
    }
};
