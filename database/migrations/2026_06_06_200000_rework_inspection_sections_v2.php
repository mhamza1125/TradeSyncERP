<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend section_type enum with new task-based types
        DB::statement("ALTER TABLE inspection_sections MODIFY COLUMN section_type
            ENUM('images','workmanship','aql','checklist','container','verification','review',
                 'task_list','quantity_sampling','cartons','cover_photo','files_review','defects','finish')
            NOT NULL DEFAULT 'checklist'");

        // 2. Add task_key to attachments for per-task grouping within a section
        if (! Schema::hasColumn('attachments', 'task_key')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->string('task_key', 100)->nullable()->after('attachment_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attachments', 'task_key')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropColumn('task_key');
            });
        }

        DB::statement("ALTER TABLE inspection_sections MODIFY COLUMN section_type
            ENUM('images','workmanship','aql','checklist','container','verification','review')
            NOT NULL DEFAULT 'checklist'");
    }
};
