<?php

use App\Models\Job_seeker\JobSeekerExperiences;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        // Schema::create('job_seeker_info', function (Blueprint $table) {
        //     $table->id();
        //     // $table->foreignId('user_id')->constrained()->onDelete('cascade');
        //     $table->unsignedBigInteger('job_seeker_id');
        //     $table->string('name');
        //     $table->string('designation');
        //     $table->string('phone');
        //     $table->string('email');
        //     $table->string('country');
        //     $table->string('city');
        //     $table->text('address');
        //     $table->text('summary');
        //     $table->timestamps();

        //     $table->foreign('jid')->references('id')->on('users')->onDelete('cascade'); // FIXED

        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    // 
};
