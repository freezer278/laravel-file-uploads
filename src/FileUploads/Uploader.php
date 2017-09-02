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
     * @param string $storage
     * @param array $options
     * @return string
     */
    public static function uploadFile(UploadedFile $file, $uploadFolder = '', string $storage = '', array $options = []): string
    {
        if ($storage === '')
            $storage = config('file_uploads.files_upload_storage');

        $localPath = FilesSaver::uploadFile($file, $uploadFolder);

        if ($storage !== FilesSaver::STORAGE_LOCAL)
            $path = static::saveFileToRemoteStorage($file, $localPath, $storage, $options);
        else
            $path = $localPath;

        return $path;
    }

    public static function uploadBase64Image(string $value, string $uploadFolder = '', string $storage = '', array $options = []): string
    {
        if ($storage === '')
            $storage = config('file_uploads.files_upload_storage');

        $localPath = static::saveBase64ImageLocally($value, $uploadFolder);
        $file = UploadedFilesCreator::createUploadedFileFromPath($localPath);

        if ($storage !== FilesSaver::STORAGE_LOCAL)
            $path = static::saveFileToRemoteStorage($file, $localPath, $storage, $options);
        else
            $path = $localPath;

        return $path;
    }

    public static function deleteFile(string $path, string $storage = '')
    {
        if ($storage === '')
            $storage = config('file_uploads.files_upload_storage');

        FilesSaver::deleteFile($path, $storage);
    }

    public static function saveFileToRemoteStorage(UploadedFile $file, string $localPath, string $storage = '', array $options = []): string
    {
        $width = (isset($options['width']) ? $options['width'] : 0);
        $height = (isset($options['height']) ? $options['height'] : 0);

        dispatch(new SaveAndResizeImage($localPath, $width, $height));

        return FilesSaver::uploadFile($file, '', $storage, true, $localPath);
    }

    public static function saveBase64ImageLocally(string $value, string $uploadsFolder = ''): string
    {
        $file = UploadedFilesCreator::createUploadedFileFromBase64($value, $uploadsFolder);

        return $uploadsFolder . '/' . $file->getBasename();
    }

    public static function saveFileLocally(UploadedFile $file, string $uploadFolder = ''): string
    {
        $localPath = FilesSaver::uploadFile($file, $uploadFolder);

        return $localPath;
    }
}