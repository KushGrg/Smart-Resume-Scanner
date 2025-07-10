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
        // Fix hr_details table
        Schema::table('hr_details', function (Blueprint $table) {
            // Drop old foreign key constraint
            $table->dropForeign(['hid']);

            // Drop old indexes if they exist
            $indexes = \DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='hr_details'");
            $indexNames = collect($indexes)->pluck('name')->toArray();

            if (in_array('idx_hr_details_hid', $indexNames)) {
                $table->dropIndex('idx_hr_details_hid');
            }

            // Rename column from hid to user_id
            $table->renameColumn('hid', 'user_id');

            // Fix the typo in organization_name
            $table->renameColumn('orgainzation_name', 'organization_name');
        });

        // Add proper foreign key constraint with cascading delete
        Schema::table('hr_details', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_hr_details_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Add proper indexes
            $table->unique('user_id', 'uk_hr_details_user_id');
            $table->index('organization_name', 'idx_hr_details_organization');
            $table->index('email', 'idx_hr_details_email');
        });

        // Fix job_seeker_details table
        Schema::table('job_seeker_details', function (Blueprint $table) {
            // Drop old foreign key constraint
            $table->dropForeign(['jid']);

            // Drop old indexes if they exist
            $indexes = \DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='job_seeker_details'");
            $indexNames = collect($indexes)->pluck('name')->toArray();

            if (in_array('idx_job_seeker_details_jid', $indexNames)) {
                $table->dropIndex('idx_job_seeker_details_jid');
            }

            // Rename column from jid to user_id
            $table->renameColumn('jid', 'user_id');
            $table->renameColumn('designation', 'current_designation');

            // Add new fields for enhanced job seeker profile
            $table->string('experience_years')->nullable()->after('current_designation');
            $table->text('skills')->nullable()->after('experience_years');
            $table->text('summary')->nullable()->after('skills');
        });

        // Add proper foreign key constraint with cascading delete
        Schema::table('job_seeker_details', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_job_seeker_details_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Add proper indexes
            $table->unique('user_id', 'uk_job_seeker_details_user_id');
            $table->index('experience_years', 'idx_job_seeker_details_experience');
            $table->index('email', 'idx_job_seeker_details_email');
        });

        // Fix job_posts table
        Schema::table('job_posts', function (Blueprint $table) {
            // Drop old foreign key constraint
            $table->dropForeign(['hid']);

            // Rename column from hid to user_id
            $table->renameColumn('hid', 'user_id');
            $table->renameColumn('requirement', 'requirements');
            $table->renameColumn('experience', 'experience_level');

            // Update description column to longText for better capacity
            $table->longText('description')->change();
            $table->longText('requirements')->nullable()->change();

            // Add job type enum and salary fields
            $table->enum('type', ['full-time', 'part-time', 'remote', 'contract'])->default('full-time')->change();
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active')->change();
            $table->decimal('salary_min', 10, 2)->nullable()->after('experience_level');
            $table->decimal('salary_max', 10, 2)->nullable()->after('salary_min');
        });

        // Add proper foreign key constraint with cascading delete
        Schema::table('job_posts', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_job_posts_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Add proper composite indexes for performance
            $table->index(['status', 'deadline'], 'idx_job_posts_status_deadline');
            $table->index(['user_id', 'status'], 'idx_job_posts_user_status');

            // Add full-text search index (skip for SQLite)
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['title', 'description'], 'ft_job_posts_search');
            }
        });

        // Fix resumes table
        Schema::table('resumes', function (Blueprint $table) {
            // Drop old foreign key constraints
            $table->dropForeign(['jsid']);
            $table->dropForeign(['jpostid']);

            // Drop old indexes if they exist
            $indexes = \DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='resumes'");
            $indexNames = collect($indexes)->pluck('name')->toArray();

            if (in_array('idx_resumes_jsid_created', $indexNames)) {
                $table->dropIndex('idx_resumes_jsid_created');
            }
            if (in_array('idx_resumes_jpostid_score', $indexNames)) {
                $table->dropIndex('idx_resumes_jpostid_score');
            }
            if (in_array('idx_resumes_processed_score', $indexNames)) {
                $table->dropIndex('idx_resumes_processed_score');
            }
            if (in_array('idx_resumes_extraction_status', $indexNames)) {
                $table->dropIndex('idx_resumes_extraction_status');
            }

            // Rename columns to proper Laravel conventions
            $table->renameColumn('jsid', 'job_seeker_detail_id');
            $table->renameColumn('jpostid', 'job_post_id');
            $table->renameColumn('resume_path', 'file_path');

            // Add enhanced resume fields
            $table->string('file_name')->after('job_post_id');
            $table->string('file_type', 10)->after('file_name')->nullable(); // pdf, doc, docx
            $table->integer('file_size')->after('file_type')->nullable(); // in bytes
            $table->decimal('similarity_score', 5, 4)->nullable()->after('processed_at'); // 0.0000 to 1.0000
            $table->timestamp('applied_at')->useCurrent()->after('similarity_score');
            $table->enum('application_status', ['pending', 'reviewed', 'shortlisted', 'rejected'])
                ->default('pending')->after('applied_at');

            // Drop old status column
            $table->dropColumn('status');
        });

        // Add proper foreign key constraints with cascading delete
        Schema::table('resumes', function (Blueprint $table) {
            $table->foreign('job_seeker_detail_id', 'fk_resumes_job_seeker_detail_id')
                ->references('id')
                ->on('job_seeker_details')
                ->onDelete('cascade');

            $table->foreign('job_post_id', 'fk_resumes_job_post_id')
                ->references('id')
                ->on('job_posts')
                ->onDelete('cascade');

            // Add proper indexes for performance
            $table->index('job_seeker_detail_id', 'idx_resumes_job_seeker_detail_id');
            $table->index('job_post_id', 'idx_resumes_job_post_id');
            $table->index('processed', 'idx_resumes_processed');
            $table->index('similarity_score', 'idx_resumes_similarity_score');
            $table->index('application_status', 'idx_resumes_application_status');

            // Unique constraint to prevent duplicate applications
            $table->unique(['job_seeker_detail_id', 'job_post_id'], 'uk_resumes_application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse resumes table changes
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropForeign('fk_resumes_job_seeker_detail_id');
            $table->dropForeign('fk_resumes_job_post_id');
            $table->dropIndex('idx_resumes_job_seeker_detail_id');
            $table->dropIndex('idx_resumes_job_post_id');
            $table->dropIndex('idx_resumes_processed');
            $table->dropIndex('idx_resumes_similarity_score');
            $table->dropIndex('idx_resumes_application_status');
            $table->dropUnique('uk_resumes_application');

            $table->renameColumn('job_seeker_detail_id', 'jsid');
            $table->renameColumn('job_post_id', 'jpostid');
            $table->renameColumn('file_path', 'resume_path');
            $table->dropColumn(['file_name', 'file_type', 'file_size', 'similarity_score', 'applied_at', 'application_status']);
            $table->boolean('status')->default(true);

            $table->foreign('jsid')->references('id')->on('job_seeker_details')->onDelete('cascade');
            $table->foreign('jpostid')->references('id')->on('job_posts')->onDelete('cascade');
        });

        // Reverse job_posts table changes
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropForeign('fk_job_posts_user_id');
            $table->dropIndex('idx_job_posts_status_deadline');
            $table->dropIndex('idx_job_posts_user_status');
            if (config('database.default') !== 'sqlite') {
                $table->dropIndex('ft_job_posts_search');
            }

            $table->renameColumn('user_id', 'hid');
            $table->renameColumn('requirements', 'requirement');
            $table->renameColumn('experience_level', 'experience');
            $table->dropColumn(['salary_min', 'salary_max']);

            $table->foreign('hid')->references('id')->on('users');
        });

        // Reverse job_seeker_details table changes
        Schema::table('job_seeker_details', function (Blueprint $table) {
            $table->dropForeign('fk_job_seeker_details_user_id');
            $table->dropUnique('uk_job_seeker_details_user_id');
            $table->dropIndex('idx_job_seeker_details_experience');
            $table->dropIndex('idx_job_seeker_details_email');

            $table->renameColumn('user_id', 'jid');
            $table->renameColumn('current_designation', 'designation');
            $table->dropColumn(['experience_years', 'skills', 'summary']);

            $table->foreign('jid')->references('id')->on('users')->onDelete('cascade');
        });

        // Reverse hr_details table changes
        Schema::table('hr_details', function (Blueprint $table) {
            $table->dropForeign('fk_hr_details_user_id');
            $table->dropUnique('uk_hr_details_user_id');
            $table->dropIndex('idx_hr_details_organization');
            $table->dropIndex('idx_hr_details_email');

            $table->renameColumn('user_id', 'hid');
            $table->renameColumn('organization_name', 'orgainzation_name');

            $table->foreign('hid')->references('id')->on('users');
        });
    }
};
