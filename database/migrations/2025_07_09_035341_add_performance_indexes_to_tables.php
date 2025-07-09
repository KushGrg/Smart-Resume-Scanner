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
        // Add performance indexes to job_posts table
        Schema::table('job_posts', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_job_posts_status_created');
            $table->index(['status', 'location'], 'idx_job_posts_status_location');
            // Skip fulltext index for SQLite compatibility
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['title', 'description', 'requirements'], 'idx_job_posts_fulltext');
            }
        });

        // Add performance indexes to resumes table
        Schema::table('resumes', function (Blueprint $table) {
            $table->index(['jsid', 'created_at'], 'idx_resumes_jsid_created');
            $table->index(['jpostid', 'similarity_score'], 'idx_resumes_jpostid_score');
            $table->index(['processed', 'similarity_score'], 'idx_resumes_processed_score');
            $table->index(['text_extracted', 'processed'], 'idx_resumes_extraction_status');
        });

        // Add performance indexes to job_seeker_details table
        Schema::table('job_seeker_details', function (Blueprint $table) {
            $table->index('jid', 'idx_job_seeker_details_jid');
            $table->index('email', 'idx_job_seeker_details_email');
        });

        // Add performance indexes to hr_details table
        Schema::table('hr_details', function (Blueprint $table) {
            $table->index('hid', 'idx_hr_details_hid');
            $table->index('email', 'idx_hr_details_email');
        });

        // Add performance indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email_verified_at', 'created_at'], 'idx_users_verified_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from job_posts table
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_status_created');
            $table->dropIndex('idx_job_posts_status_location');
            if (config('database.default') !== 'sqlite') {
                $table->dropIndex('idx_job_posts_fulltext');
            }
        });

        // Remove indexes from resumes table
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropIndex('idx_resumes_jsid_created');
            $table->dropIndex('idx_resumes_jpostid_score');
            $table->dropIndex('idx_resumes_processed_score');
            $table->dropIndex('idx_resumes_extraction_status');
        });

        // Remove indexes from job_seeker_details table
        Schema::table('job_seeker_details', function (Blueprint $table) {
            $table->dropIndex('idx_job_seeker_details_jid');
            $table->dropIndex('idx_job_seeker_details_email');
        });

        // Remove indexes from hr_details table
        Schema::table('hr_details', function (Blueprint $table) {
            $table->dropIndex('idx_hr_details_hid');
            $table->dropIndex('idx_hr_details_email');
        });

        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_verified_created');
        });
    }
};
