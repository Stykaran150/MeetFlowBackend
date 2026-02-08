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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'confidence_score')) {
                $table->integer('confidence_score')->nullable()->after('position')->comment('AI confidence score (0-100)');
            }
            if (!Schema::hasColumn('tasks', 'suggested_deadline')) {
                $table->dateTime('suggested_deadline')->nullable()->after('deadline')->comment('AI suggested deadline if not specified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['confidence_score', 'suggested_deadline']);
        });
    }
};
