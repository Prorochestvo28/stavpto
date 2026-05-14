<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(1)->after('process_id');
        });
    }

    public function down(): void
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
