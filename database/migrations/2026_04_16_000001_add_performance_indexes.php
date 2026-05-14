<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->index('status', 'documents_status_idx');
            $table->index('updated_at', 'documents_updated_at_idx');
            $table->index('category_id', 'documents_category_id_idx');
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->index(['document_id', 'version_number'], 'document_versions_doc_ver_idx');
            $table->index('author_id', 'document_versions_author_id_idx');
        });

        Schema::table('approval_steps', function (Blueprint $table) {
            $table->index(['process_id', 'id'], 'approval_steps_process_id_id_idx');
            $table->index(['assignee_id', 'status'], 'approval_steps_assignee_status_idx');
            $table->index(['process_id', 'status'], 'approval_steps_process_status_idx');
        });

        Schema::table('approval_processes', function (Blueprint $table) {
            $table->index('status', 'approval_processes_status_idx');
            $table->index('initiator_id', 'approval_processes_initiator_id_idx');
            $table->index('deadline', 'approval_processes_deadline_idx');
        });
    }

    public function down(): void
    {
        Schema::table('approval_processes', function (Blueprint $table) {
            $table->dropIndex('approval_processes_deadline_idx');
            $table->dropIndex('approval_processes_initiator_id_idx');
            $table->dropIndex('approval_processes_status_idx');
        });

        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropIndex('approval_steps_process_status_idx');
            $table->dropIndex('approval_steps_assignee_status_idx');
            $table->dropIndex('approval_steps_process_id_id_idx');
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropIndex('document_versions_author_id_idx');
            $table->dropIndex('document_versions_doc_ver_idx');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_category_id_idx');
            $table->dropIndex('documents_updated_at_idx');
            $table->dropIndex('documents_status_idx');
        });
    }
};

