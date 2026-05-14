<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('departments')
            ->whereNotNull('head_user_id')
            ->distinct()
            ->pluck('head_user_id');

        foreach ($ids as $userId) {
            DB::table('users')
                ->where('id', $userId)
                ->where('role', '!=', 'admin')
                ->update(['role' => 'department_head']);
        }
    }

    public function down(): void
    {
        $ids = DB::table('departments')
            ->whereNotNull('head_user_id')
            ->distinct()
            ->pluck('head_user_id');

        foreach ($ids as $userId) {
            DB::table('users')
                ->where('id', $userId)
                ->where('role', 'department_head')
                ->update(['role' => 'user']);
        }
    }
};
