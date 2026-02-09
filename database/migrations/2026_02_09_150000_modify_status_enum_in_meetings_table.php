<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'draft'
        DB::statement("ALTER TABLE meetings MODIFY COLUMN status ENUM('pending', 'processing', 'processed', 'failed', 'draft') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any 'draft' status to 'pending' to avoid truncation error
        DB::table('meetings')->where('status', 'draft')->update(['status' => 'pending']);

        // Revert to original enum
        DB::statement("ALTER TABLE meetings MODIFY COLUMN status ENUM('pending', 'processing', 'processed', 'failed') DEFAULT 'pending'");
    }
};
