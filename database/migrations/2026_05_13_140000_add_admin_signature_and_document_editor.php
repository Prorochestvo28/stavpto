<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signature_pin')->nullable()->after('password');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('head_user_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('last_edited_by')->nullable()->after('author_id')->constrained('users')->nullOnDelete();
        });

        if (Schema::hasTable('documents')) {
            DB::table('documents')->whereNull('last_edited_by')->update(['last_edited_by' => DB::raw('author_id')]);
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['last_edited_by']);
            $table->dropColumn('last_edited_by');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_user_id']);
            $table->dropColumn('head_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signature_pin');
        });
    }
};
