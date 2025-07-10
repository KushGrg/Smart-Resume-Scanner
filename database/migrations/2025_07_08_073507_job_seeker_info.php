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
        Schema::create('job_seeker_infos', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('job_seeker_id');
            $table->string('name');
            $table->string('designation');
            $table->string('phone');
            $table->string('email');
            $table->string('country');
            $table->string('city');
            $table->text('address');
            $table->text('summary');
            $table->timestamps();

            $table->foreign('job_seeker_id')->references('id')->on('users')->onDelete('cascade'); // FIXED
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seeker_infos');
    }

    //
};
