<?php

namespace App\Support;

use App\Models\ApprovalStep;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ActivityRouteLabels
{
    /**
     * @return array<string, string>
     */
    public static function map(): array
    {
        return [
            'logout' => 'Выход из системы',
            'settings.signature' => 'Изменение электронной подписи',
            'settings.profile' => 'Изменение данных профиля',
            'settings.password' => 'Смена пароля',
            'documents.store' => 'Загрузка документа',
            'documents.update' => 'Изменение документа',
            'documents.destroy' => 'Удаление документа',
            'documents.reopen-draft' => 'Возврат документа в черновик',
            'documents.comments.store' => 'Комментарий к документу',
            'documents.approval-process.store' => 'Запуск согласования документа',
            'categories.store' => 'Создание папки',
            'categories.update' => 'Переименование папки',
            'categories.destroy' => 'Удаление папки',
            'approval-steps.approve' => 'Согласование этапа',
            'approval-steps.reject' => 'Отклонение на этапе согласования',
            'admin.users.store' => 'Создание пользователя',
            'admin.users.update' => 'Изменение пользователя',
            'admin.users.destroy' => 'Отключение пользователя',
            'admin.users.restore' => 'Включение пользователя',
            'admin.departments.update' => 'Изменение отдела',
        ];
    }

    public static function describe(Request $request): string
    {
        $routeName = $request->route()?->getName();
        $method = $request->method();
        $path = '/'.$request->path();

        if ($routeName !== null && isset(self::map()[$routeName])) {
            $base = self::map()[$routeName];
        } elseif ($routeName !== null) {
            $base = sprintf('Действие «%s» (%s)', $routeName, $method);
        } else {
            $base = sprintf('Запрос %s %s', $method, $path);
        }

        $withContext = self::appendDocumentContext($request, $routeName, $base);

        return Str::limit($withContext, 500, '…');
    }

    private static function appendDocumentContext(Request $request, ?string $routeName, string $base): string
    {
        $document = $request->route('document');
        if ($document instanceof Document) {
            return $base.' — «'.self::sanitizeTitle($document->name).'»';
        }

        $version = $request->route('document_version');
        if ($version instanceof DocumentVersion) {
            $version->loadMissing('document');
            $name = $version->document?->name;
            if ($name !== null && $name !== '') {
                $suffix = ' (v'.$version->version_number.')';

                return $base.' — «'.self::sanitizeTitle($name).'»'.$suffix;
            }
        }

        if ($routeName === 'documents.store') {
            $name = trim((string) $request->input('name', ''));
            if ($name !== '') {
                return $base.' — «'.self::sanitizeTitle($name).'»';
            }
        }

        $step = $request->route('approval_step');
        if ($step instanceof ApprovalStep) {
            $step->loadMissing('process.document');
            $name = $step->process?->document?->name;
            if ($name !== null && $name !== '') {
                return $base.' — «'.self::sanitizeTitle($name).'»';
            }
        }

        return $base;
    }

    private static function sanitizeTitle(string $name): string
    {
        $t = preg_replace('/\s+/u', ' ', trim($name)) ?? '';

        return Str::limit($t, 180, '…');
    }
}
