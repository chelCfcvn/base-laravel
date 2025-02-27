<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Full Path Not Domain
     *
     * @param $path
     * @return string|null
     */
    public static function fullPathNotDomain($path): ?string
    {
        if (!$path) {
            return null;
        }//end if

        $urlParsed = parse_url($path);

        return trim($urlParsed['path'] ?? '', '/');
    }

    /**
     * Get Full Url
     *
     * @param $path
     * @return string|null
     */
    public static function getFullUrl($path): ?string
    {
        if (!$path) {
            return null;
        }//end if

        $newPath = strstr($path, config('upload.path_origin_image'));

        return self::storageImages()->url($newPath);
    }

    /**
     * @param $path
     * @return string|null
     */
    public static function getFullUrlThumb($path): ?string
    {
        if (!$path) {
            return null;
        }//end if

        $getPathThumb = str_replace(config('upload.path_origin_image'), config('upload.path_thumbnail'), $path);
        $newPathThumb = strstr($getPathThumb, config('upload.path_thumbnail'));

        return self::storageImages()->url($newPathThumb);
    }

    /**
     * Storage Images
     *
     * @return Filesystem
     */
    public static function storageImages(): Filesystem
    {
        $diskName = config('upload.disk');

        return Storage::disk($diskName);
    }

    /**
     * Get File Webp Name
     *
     * @param null $baseName
     * @param null $fileExtension
     * @return string
     */
    public static function constructFileName($baseName = null, $fileExtension = null): string
    {
        if ($baseName) {
            $pathInfo = pathinfo($baseName);
            $fileName = $pathInfo['basename'];
        } else {
            $fileName = md5(StringHelper::uniqueCode(15) . Carbon::now() . StringHelper::uniqueCode(25));
        }

        $fileExtension = $fileExtension ?? config('upload.webp_ext');

        return Str::lower($fileName . '.' . $fileExtension);
    }

    /**
     * Path Url
     *
     * @param $fileName
     * @param $namePath
     * @return string
     */
    public static function pathUrl($fileName, $namePath): string
    {
        return $namePath . '/' . $fileName;
    }
}
