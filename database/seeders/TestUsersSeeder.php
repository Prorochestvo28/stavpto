<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $it = Department::query()->where('name', 'IT-отдел')->first();
        $legal = Department::query()->where('name', 'Юридический отдел')->first();
        $hr = Department::query()->where('name', 'Отдел кадров')->first();
        $accounting = Department::query()->where('name', 'Бухгалтерия')->first();

        if (! $it || ! $legal || ! $hr || ! $accounting) {
            $this->command?->warn('TestUsersSeeder: не найдены отделы из DepartmentSeeder, пропуск.');

            return;
        }

        $head = User::query()->updateOrCreate(
            ['email' => 'prorokov_2019@mail.ru'],
            [
                'name' => 'prorokov_2019',
                'password' => Hash::make('12345678'),
                'full_name' => 'Пророков М.Е.',
                'department_id' => $it->id,
                'role' => 'department_head',
                'is_active' => true,
            ]
        );

        $it->refresh();
        $this->demoteFormerHeadIfReplaced($it, (int) $head->id);
        $it->update(['head_user_id' => $head->id]);

        User::query()->updateOrCreate(
            ['email' => 'prorokov_2018@mail.ru'],
            [
                'name' => 'prorokov_2018',
                'password' => Hash::make('12345678'),
                'full_name' => 'Пророков М.Е.',
                'department_id' => $it->id,
                'role' => 'user',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'ivanova_legal@mail.ru'],
            [
                'name' => 'ivanova_legal',
                'password' => Hash::make('12345678'),
                'full_name' => 'Иванова Е.С.',
                'department_id' => $legal->id,
                'role' => 'user',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'petrov_hr@mail.ru'],
            [
                'name' => 'petrov_hr',
                'password' => Hash::make('12345678'),
                'full_name' => 'Пётров К.В.',
                'department_id' => $hr->id,
                'role' => 'user',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'sidorova_buh@mail.ru'],
            [
                'name' => 'sidorova_buh',
                'password' => Hash::make('12345678'),
                'full_name' => 'Сидорова М.П.',
                'department_id' => $accounting->id,
                'role' => 'user',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'kozlov_it@mail.ru'],
            [
                'name' => 'kozlov_it',
                'password' => Hash::make('12345678'),
                'full_name' => 'Козлов И.О.',
                'department_id' => $it->id,
                'role' => 'user',
                'is_active' => true,
            ]
        );
    }

    /**
     * Снимает роль начальника с прежнего главы отдела, если назначается другой пользователь.
     */
    private function demoteFormerHeadIfReplaced(Department $department, int $newHeadId): void
    {
        $oldId = $department->head_user_id;
        if (! $oldId || (int) $oldId === $newHeadId) {
            return;
        }

        $former = User::query()->find($oldId);
        if ($former && ! $former->isAdmin() && $former->role === 'department_head') {
            $former->update(['role' => 'user']);
        }
    }
}
