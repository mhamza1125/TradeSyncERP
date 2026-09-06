<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('task_key');
            $table->index(['attachable_type', 'attachable_id', 'sort_order'], 'attachments_attachable_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex('attachments_attachable_sort_index');
            $table->dropColumn('sort_order');
        });
    }
};
