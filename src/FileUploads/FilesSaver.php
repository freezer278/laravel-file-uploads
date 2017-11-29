<?php

namespace Vmorozov\FileUploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FilesSaver
{
    const DEFAULT_UPLOADS_FOLDER = 'user-uploads';

    const STORAGE_LOCAL = 'local';
    const STORAGE_AMAZON_S3 = 's3';

    const DEFAULT_IMAGE_QUALITY = 100;
    const DEFAULT_IMAGE_EXTENSION = 'png';

    /**
     * Checks given file and uploads it to the server
     *
     * @param UploadedFile $file
     * @param string $uploadFolder
     * @param string $storage
     * @param bool $dryRun
     * @param string $fileName
     * @return string
     */
    public static function uploadFile(UploadedFile $file, string $uploadFolder = '', string $storage = FilesSaver::STORAGE_LOCAL, bool $dryRun = false, string $fileName = ''): string
    {
        $path = '';

        $fileNameGiven = ($fileName !== '');

        if ($uploadFolder == '')
            $uploadFolder = config('file_uploads.default_uploads_folder');

        if (self::checkFileIsValid($file, $storage)) {

            if (!$fileNameGiven) {
                $path = $uploadFolder . '/' . md5($file->getFilename().microtime()) . '.'.$file->getClientOriginalExtension();
            }
            else
                $path = $fileName;

            $path = self::checkStorageAndSaveFile($file, $path, $storage, $dryRun);
        }

        // todo: if file is not valid throw some exception

        return $path;
    }

    public static function getFileNameFromPath(string $path): string
    {
        return array_last(explode('/', $path));
    }

    public static function checkFileIsValid(UploadedFile $file, string $storage): bool
    {
        $fileIsValid = $storage === FilesSaver::STORAGE_LOCAL ? $file->isValid() : true;

        return ($file != null && $fileIsValid);
    }

    public static function checkFileIsImage(UploadedFile $file): bool
    {
        return self::checkMimeIsImage($file->getMimeType());
    }

    public static function checkFileIsImageByPath(string $path): bool
    {
        return self::checkMimeIsImage(mime_content_type($path));
    }

    private static function checkMimeIsImage(string $mime): bool
    {
        return (substr($mime, 0, 5) == 'image');
    }

    private static function checkStorageAndSaveFile(
        $file,
        string $path,
        string $storage = FilesSaver::STORAGE_LOCAL,
        bool $dryRun = false,
        bool $public = true
    ): string
    {
        if (!$dryRun) {
            if ($storage !== FilesSaver::STORAGE_LOCAL) {
                Storage::disk($storage)->putFileAs('/', $file, $path, ($public ? 'public' : []));

                $path = Storage::disk($storage)->url($path);
            }
            else {
                static::saveFileLocally($file, $path);
            }
        }

        return $path;
    }

    private static function saveFileLocally($file, string $path)
    {
        $explodedPath = explode('/', $path);
        $filename = array_pop($explodedPath);
        $uploadFolder = public_path(implode('/', $explodedPath));

//        if (!$file instanceof UploadedFile)
//         Todo: throw some exception

        $file->move($uploadFolder, $filename);
    }

    public static function getPathFromAmazonS3Url(string $path): string
    {
        if (stripos($path, '//')) {
            $newPath = explode('//', $path);

            $newPath = array_last($newPath);

            $newPath = explode('/', $newPath);

            array_shift($newPath);

            return '/'.implode('/', $newPath);
        }
        else {
            return $path;
        }

    }

    public static function deleteFile(string $path, string $storage = FilesSaver::STORAGE_LOCAL)
    {
        switch ($storage) {
            case FilesSaver::STORAGE_AMAZON_S3:
                return Storage::disk($storage)->delete(self::getPathFromAmazonS3Url($path));
                break;

            default:
                return @unlink(public_path($path));
                break;
        }
    }
}