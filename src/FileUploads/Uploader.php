<?php

namespace Vmorozov\FileUploads;

use Illuminate\Http\UploadedFile;

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
    public static function uploadFile(UploadedFile $file, $uploadFolder = '', int $width = 0, int $height = 0)
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

    public function deleteFile(string $path)
    {
        FilesSaver::deleteFile($path);
    }

    public static function saveBase64ImageLocally(string $value, string $uploadsFolder = ''): string
    {
        $file = FilesSaver::createUploadedFileFromBase64($value, $uploadsFolder);

        return $file->path();
    }

    public static function saveFileLocally(UploadedFile $file, string $uploadFolder = ''): string
    {
        $localPath = FilesSaver::uploadFile($file, $uploadFolder, true);

        return $localPath;
    }
}