<?php

namespace Vmorozov\FileUploads;

use Illuminate\Http\UploadedFile;
use Vmorozov\FileUploads\Jobs\SaveAndResizeImage;

class Uploader
{
    /**
     * Checks given file and uploads it to the server
     *
     * @param UploadedFile $file
     * @param string $uploadFolder
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function uploadFile(UploadedFile $file, $uploadFolder = '', int $width = 0, int $height = 0, string $storage = FilesSaver::STORAGE_LOCAL): string
    {
        $localPath = FilesSaver::uploadFile($file, $uploadFolder, true);

        if (env('FILES_UPLOAD') === FilesSaver::STORAGE_AMAZON_S3) {
            $path = FilesSaver::uploadFile($file, $uploadFolder, false, true, $localPath);

            dispatch(new SaveAndResizeImage($localPath, $width, $height));
        }
        else {
            $path = $localPath;
        }

        return $path;
    }

    public static function uploadBase64Image(string $value, string $uploadFolder = '', int $width = 0, int $height = 0, string $storage = FilesSaver::STORAGE_LOCAL): string
    {
        $localPath = static::saveBase64ImageLocally($value, $uploadFolder);
        $file = UploadedFilesCreator::createUploadedFileFromPath($localPath);

        if (env('FILES_UPLOAD') === FilesSaver::STORAGE_AMAZON_S3) {
            $path = FilesSaver::uploadFile($file, $uploadFolder, false, true, $localPath);

            dispatch(new SaveAndResizeImage($localPath, $width, $height));
        }
        else {
            $path = $localPath;
        }

        return $path;
    }

    public static function deleteFile(string $path, string $storage = FilesSaver::STORAGE_LOCAL)
    {
        FilesSaver::deleteFile($path);
    }

    public static function saveBase64ImageLocally(string $value, string $uploadsFolder = ''): string
    {
        $file = UploadedFilesCreator::createUploadedFileFromBase64($value, $uploadsFolder);

        return $uploadsFolder . '/' . $file->getBasename();
    }

    public static function saveFileLocally(UploadedFile $file, string $uploadFolder = ''): string
    {
        $localPath = FilesSaver::uploadFile($file, $uploadFolder, true);

        return $localPath;
    }
}