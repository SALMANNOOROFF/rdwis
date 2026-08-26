<?php

namespace App\Facades;

use App\Services\FileStorageService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string store(\Illuminate\Http\UploadedFile $file, string $module, string $prefix, string $objectId)
 * @method static string|null url(?string $relativePath)
 * @method static bool exists(?string $relativePath)
 * @method static string|null path(?string $relativePath)
 * @method static bool delete(?string $relativePath)
 * @method static \Symfony\Component\HttpFoundation\BinaryFileResponse response(?string $relativePath, ?string $downloadName = null, bool $download = false)
 * @method static object findOrCreateSlot(string $moduleOrTable, string $objType, mixed $objId, string $type)
 * @method static string storeAndAttach(\Illuminate\Http\UploadedFile $file, string $moduleOrTable, string $prefix, mixed $objectId, string $type, ?string $objType = null)
 * @method static string normalizePath(string $path)
 *
 * @see \App\Services\FileStorageService
 */
class FileStorage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FileStorageService::class;
    }
}
