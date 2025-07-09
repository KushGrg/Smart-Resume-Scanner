<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Note: This migration is deprecated - foreign key naming conventions
        // have been updated in the fix_foreign_key_naming_conventions migration
        // which includes proper indexes. This migration is kept for backward compatibility.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is deprecated - no rollback needed
    }
};
