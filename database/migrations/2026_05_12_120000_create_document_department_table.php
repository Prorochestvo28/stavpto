<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_department', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->primary(['document_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_department');
    }
};
