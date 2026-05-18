<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->resolveUsers();
        if ($users === null) {
            return;
        }

        $deptIds = Department::query()->pluck('id')->all();
        if ($deptIds === []) {
            return;
        }

        $contracts = Category::query()->firstOrCreate(
            ['name' => 'Договоры (тест)'],
            ['parent_id' => null]
        );
        $orders = Category::query()->firstOrCreate(
            ['name' => 'Приказы (тест)'],
            ['parent_id' => null]
        );

        $items = [
            [
                'name' => 'Договор поставки №101',
                'category_id' => $contracts->id,
                'status' => 'draft',
                'author' => 'accounting',
                'editor' => 'accounting',
                'created_at' => '2025-10-08 10:15:00',
                'updated_at' => '2026-02-14 16:40:00',
                'versions' => [
                    ['n' => 1, 'file' => 'dogovor-postavki-101.pdf', 'author' => 'accounting', 'at' => '2025-10-08 10:15:00'],
                    ['n' => 2, 'file' => 'dogovor-postavki-101-v2.pdf', 'author' => 'accounting', 'at' => '2026-02-14 16:40:00', 'comment' => 'Обновлены сроки поставки'],
                ],
            ],
            [
                'name' => 'Договор аренды офиса',
                'category_id' => $contracts->id,
                'status' => 'draft',
                'author' => 'legal',
                'editor' => 'legal',
                'created_at' => '2025-11-22 09:00:00',
                'updated_at' => '2025-12-03 11:20:00',
                'versions' => [
                    ['n' => 1, 'file' => 'arenda-ofis.docx', 'author' => 'legal', 'at' => '2025-11-22 09:00:00'],
                ],
            ],
            [
                'name' => 'Служебная записка о командировке',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'hr',
                'editor' => 'hr',
                'created_at' => '2026-01-17 14:30:00',
                'updated_at' => '2026-01-17 14:30:00',
            ],
            [
                'name' => 'Приказ о приёме на работу',
                'category_id' => $orders->id,
                'status' => 'draft',
                'author' => 'hr',
                'editor' => 'head',
                'created_at' => '2025-09-12 08:45:00',
                'updated_at' => '2026-03-05 13:10:00',
                'versions' => [
                    ['n' => 1, 'file' => 'prikaz-priem.pdf', 'author' => 'hr', 'at' => '2025-09-12 08:45:00'],
                    ['n' => 2, 'file' => 'prikaz-priem-redakciya.docx', 'author' => 'hr', 'at' => '2025-11-01 15:00:00', 'comment' => 'Правки отдела кадров'],
                    ['n' => 3, 'file' => 'prikaz-priem-final.pdf', 'author' => 'head', 'at' => '2026-03-05 13:10:00', 'comment' => 'Утверждено руководителем'],
                ],
            ],
            [
                'name' => 'Регламент IT-безопасности',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'it',
                'editor' => 'it',
                'created_at' => '2025-12-18 16:00:00',
                'updated_at' => '2026-04-02 10:05:00',
                'versions' => [
                    ['n' => 1, 'file' => 'reglament-it.pdf', 'author' => 'it', 'at' => '2025-12-18 16:00:00'],
                    ['n' => 2, 'file' => 'reglament-it-v2.pdf', 'author' => 'it', 'at' => '2026-04-02 10:05:00', 'comment' => 'Добавлен раздел о VPN'],
                ],
            ],
            [
                'name' => 'Акт сверки за Q1',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'accounting',
                'editor' => 'head',
                'created_at' => '2026-04-10 11:30:00',
                'updated_at' => '2026-04-11 09:15:00',
                'versions' => [
                    ['n' => 1, 'file' => 'akt-sverki-q1.xlsx', 'author' => 'accounting', 'at' => '2026-04-10 11:30:00'],
                ],
            ],
            [
                'name' => 'Заявка на закупку оборудования',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'it',
                'editor' => 'it2',
                'created_at' => '2026-02-28 12:00:00',
                'updated_at' => '2026-03-18 17:45:00',
            ],
            [
                'name' => 'Договор подряда №205',
                'category_id' => $contracts->id,
                'status' => 'draft',
                'author' => 'legal',
                'editor' => 'legal',
                'created_at' => '2025-08-20 13:20:00',
                'updated_at' => '2026-01-08 14:55:00',
                'versions' => [
                    ['n' => 1, 'file' => 'dogovor-podryad-205.pdf', 'author' => 'legal', 'at' => '2025-08-20 13:20:00'],
                    ['n' => 2, 'file' => 'dogovor-podryad-205-signed.pdf', 'author' => 'legal', 'at' => '2026-01-08 14:55:00', 'comment' => 'Версия для согласования'],
                ],
            ],
            [
                'name' => 'Положение об оплате труда',
                'category_id' => $orders->id,
                'status' => 'draft',
                'author' => 'hr',
                'editor' => 'hr',
                'created_at' => '2025-07-05 10:00:00',
                'updated_at' => '2025-10-19 12:30:00',
                'versions' => [
                    ['n' => 1, 'file' => 'polozhenie-oplata-truda.docx', 'author' => 'hr', 'at' => '2025-07-05 10:00:00'],
                ],
            ],
            [
                'name' => 'Инструкция по охране труда',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'head',
                'editor' => 'head',
                'created_at' => '2025-11-30 08:10:00',
                'updated_at' => '2025-11-30 08:10:00',
            ],
            [
                'name' => 'Протокол совещания 12.03',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'head',
                'editor' => 'it',
                'created_at' => '2026-03-12 18:00:00',
                'updated_at' => '2026-03-13 09:40:00',
                'versions' => [
                    ['n' => 1, 'file' => 'protokol-12-03.pdf', 'author' => 'head', 'at' => '2026-03-12 18:00:00'],
                    ['n' => 2, 'file' => 'protokol-12-03-isp.pdf', 'author' => 'it', 'at' => '2026-03-13 09:40:00', 'comment' => 'Добавлены пункты по IT'],
                ],
            ],
            [
                'name' => 'Договор ГПХ с Ивановой',
                'category_id' => $contracts->id,
                'status' => 'draft',
                'author' => 'legal',
                'editor' => 'legal',
                'created_at' => '2026-04-15 15:25:00',
                'updated_at' => '2026-04-15 15:25:00',
            ],
            [
                'name' => 'План закупок 2026',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'accounting',
                'editor' => 'accounting',
                'created_at' => '2026-01-05 09:50:00',
                'updated_at' => '2026-02-20 11:00:00',
                'versions' => [
                    ['n' => 1, 'file' => 'plan-zakupok-2026.xlsx', 'author' => 'accounting', 'at' => '2026-01-05 09:50:00'],
                    ['n' => 2, 'file' => 'plan-zakupok-2026-v2.xlsx', 'author' => 'accounting', 'at' => '2026-02-20 11:00:00', 'comment' => 'Скорректированы суммы по статьям'],
                ],
            ],
            [
                'name' => 'Отчёт о расходах',
                'category_id' => null,
                'status' => 'draft',
                'author' => 'accounting',
                'editor' => 'accounting',
                'created_at' => '2025-12-01 17:00:00',
                'updated_at' => '2025-12-28 10:30:00',
                'versions' => [
                    ['n' => 1, 'file' => 'otchet-rashody.pdf', 'author' => 'accounting', 'at' => '2025-12-01 17:00:00'],
                    ['n' => 2, 'file' => 'otchet-rashody-dekabr.pdf', 'author' => 'accounting', 'at' => '2025-12-28 10:30:00', 'comment' => 'Данные за декабрь'],
                ],
            ],
        ];

        foreach ($items as $item) {
            $author = $users[$item['author']];
            $editor = $users[$item['editor']];

            $document = Document::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $item['category_id'],
                    'status' => $item['status'],
                    'author_id' => $author->id,
                    'last_edited_by' => $editor->id,
                ]
            );

            $document->created_at = Carbon::parse($item['created_at']);
            $document->updated_at = Carbon::parse($item['updated_at']);
            $document->saveQuietly();

            $document->departments()->sync($deptIds);

            $document->versions()->delete();

            foreach ($item['versions'] ?? [] as $ver) {
                $versionAuthor = $users[$ver['author']];
                $version = DocumentVersion::query()->create([
                    'document_id' => $document->id,
                    'version_number' => $ver['n'],
                    'file_name' => $ver['file'],
                    'file_url' => null,
                    'file_size' => 0,
                    'change_comment' => $ver['comment'] ?? null,
                    'author_id' => $versionAuthor->id,
                ]);
                $version->created_at = Carbon::parse($ver['at']);
                $version->saveQuietly();
            }
        }
    }

    /**
     * @return array<string, User>|null
     */
    private function resolveUsers(): ?array
    {
        $map = [
            'head' => 'prorokov_2019@mail.ru',
            'it' => 'kozlov_it@mail.ru',
            'it2' => 'prorokov_2018@mail.ru',
            'legal' => 'ivanova_legal@mail.ru',
            'hr' => 'petrov_hr@mail.ru',
            'accounting' => 'sidorova_buh@mail.ru',
        ];

        $users = [];
        foreach ($map as $key => $email) {
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                return null;
            }
            $users[$key] = $user;
        }

        return $users;
    }
}
