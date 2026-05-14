<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Юридический отдел',
            'Бухгалтерия',
            'Отдел кадров',
            'IT-отдел',
            'Производство',
            'Руководство',
        ];

        foreach ($items as $name) {
            Department::query()->firstOrCreate(
                ['name' => $name],
                ['description' => null]
            );
        }
    }
}
