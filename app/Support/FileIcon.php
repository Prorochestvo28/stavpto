<?php

namespace App\Support;

final class FileIcon
{
    private const EXT_TO_ICON = [
        'pdf' => 'pdf_icon.png',
        'doc' => 'docx_icon.png',
        'docx' => 'docx_icon.png',
        'xls' => 'xls_icon.png',
        'xlsx' => 'xls_icon.png',
        'ppt' => 'pptx_icon.png',
        'pptx' => 'pptx_icon.png',
    ];

    private const FALLBACK = 'other_icon.svg';

    private const FOLDER = 'folder_icon.svg';

    public static function iconFilenameForExtension(?string $ext): string
    {
        if ($ext === null || $ext === '') {
            return self::FALLBACK;
        }

        $ext = strtolower($ext);

        return self::EXT_TO_ICON[$ext] ?? self::FALLBACK;
    }

    public static function iconFilenameForFilename(?string $fileName): string
    {
        if ($fileName === null || $fileName === '') {
            return self::FALLBACK;
        }

        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        return self::iconFilenameForExtension(is_string($ext) ? $ext : null);
    }

    public static function url(string $filename): string
    {
        $safe = basename($filename);
        $path = public_path('img/'.$safe);
        if (! is_file($path)) {
            $safe = self::FALLBACK;
        }

        return asset('img/'.$safe);
    }

    public static function urlForFolder(): string
    {
        return self::url(self::FOLDER);
    }

    public static function urlForDocument(?string $fileName): string
    {
        return self::url(self::iconFilenameForFilename($fileName));
    }
}
