<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status', 'documents_status_idx');
            $table->index('updated_at', 'documents_updated_at_idx');
            $table->index('category_id', 'documents_category_id_idx');
        });

        Schema::create('document_department', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->primary(['document_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_department');
        Schema::dropIfExists('documents');
    }
};
