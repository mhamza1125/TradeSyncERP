<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE inspection_sections MODIFY COLUMN section_type
            ENUM('images','workmanship','aql','checklist','container','verification','review',
                 'task_list','quantity_sampling','cartons','cover_photo','files_review','defects','finish',
                 'article_results','conclusion','general_info')
            NOT NULL DEFAULT 'checklist'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE inspection_sections MODIFY COLUMN section_type
            ENUM('images','workmanship','aql','checklist','container','verification','review',
                 'task_list','quantity_sampling','cartons','cover_photo','files_review','defects','finish',
                 'article_results','conclusion')
            NOT NULL DEFAULT 'checklist'");
    }
};
