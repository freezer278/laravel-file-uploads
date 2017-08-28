<?php

namespace Vmorozov\FileUploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class FilesSaver
{
    const DEFAULT_UPLOADS_FOLDER = 'user-uploads';

    const STORAGE_LOCAL = 'local';
    const STORAGE_AMAZON_S3 = 's3';

    const DEFAULT_IMAGE_QUALITY = 50;
    const DEFAULT_IMAGE_EXTENSION = 'jpg';

    /**
     * Checks given file and uploads it to the server
     *
     * @param UploadedFile $file
     * @param string $uploadFolder
     * @param bool $local
     * @param string $fileName
     * @param bool $dryRun
     * @return string
     */
    public static function uploadFile(UploadedFile $file, string $uploadFolder = '', bool $local = true, bool $dryRun = false, string $fileName = ''): string
    {
        $path = '';

        $fileNameGiven = ($fileName !== '');

        if ($uploadFolder == '')
            $uploadFolder = self::DEFAULT_UPLOADS_FOLDER;

        if (self::checkFileIsValid($file, $local)) {

            if (!$fileNameGiven)
                $path = $uploadFolder . '/' . md5($file->getFilename().microtime());
            else
                $path = $fileName;

//            if (self::checkFileIsImage($file)) {
//                if (!$fileNameGiven)
//                    $path .= '.'.self::DEFAULT_IMAGE_EXTENSION;
//
//                if (!$dryRun) {
//                    $image = Image::make($file->getRealPath())->encode(self::DEFAULT_IMAGE_EXTENSION, self::DEFAULT_IMAGE_QUALITY);
//                    $image = $image->stream()->__toString();
//                }
//                else
//                    $image = $file;
//
//                $path = self::checkStorageAndSaveFile($image, $path, $local, $dryRun);
//            }
//            else {
            if (!$fileNameGiven)
                $path .= '.'.$file->extension();

            $path = self::checkStorageAndSaveFile($file, $path, $local, $dryRun);
//            }
        }

        // todo: if file is not valid throw some exception

        return $path;
    }

    public static function getFileNameFromPath(string $path): string
    {
        return array_last(explode('/', $path));
    }

    public static function checkFileIsValid(UploadedFile $file, bool $local): bool
    {
        $fileIsValid = $local ? $file->isValid() : true;

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

    private static function checkStorageAndSaveFile($file, string $path, bool $local = true, bool $dryRun = false, bool $public = true): string
    {
        if (!$local && config('file_uploads.files_upload_storage') === self::STORAGE_AMAZON_S3) {
            if (!$dryRun) {
                if ($public)
                    Storage::disk(self::STORAGE_AMAZON_S3)->putFileAs('/', $file, $path, 'public');
                else
                    Storage::disk(self::STORAGE_AMAZON_S3)->putFileAs('/', $file, $path);
            }

            return Storage::disk(self::STORAGE_AMAZON_S3)->url($path);
        }
        else {
            if (!$dryRun)
                self::saveFileLocally($file, $path);

            return $path;
        }
    }

    private static function saveFileLocally($file, string $path)
    {
        $explodedPath = explode('/', $path);
        $filename = array_pop($explodedPath);
        $uploadFolder = public_path(implode('/', $explodedPath));

//        if (!$file instanceof UploadedFile)
//         Todo: do something

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

    public static function deleteFile(string $path, $local = false)
    {
        if (!$local && config('file_uploads.files_upload_storage') === self::STORAGE_AMAZON_S3) {
            return Storage::disk(self::STORAGE_AMAZON_S3)->delete(self::getPathFromAmazonS3Url($path));
        }
        else {
            return @unlink(public_path($path));
        }
    }

    public static function createUploadedFileFromPath(string $path): UploadedFile
    {
        $file = (new File())->setFileInfoFromPath($path);

        try {
            return new UploadedFile(
                $path,
                $file->basename,
                $file->mime,
                $file->filesize(),
                UPLOAD_ERR_OK,
                true
            );
        } catch (FileNotFoundException $exception) {
            return new UploadedFile(
                public_path($path),
                $file->basename,
                $file->mime,
                $file->filesize(),
                UPLOAD_ERR_OK,
                true
            );
        }
    }

    public static function createUploadedFileFromBase64(string $value, string $uploadFolder = ''): UploadedFile
    {
        if ($uploadFolder == '')
            $uploadFolder = self::DEFAULT_UPLOADS_FOLDER;

        $image = Image::make($value);

        $filename = md5($image->basename.microtime()).'.jpg';

        $path = $uploadFolder.'/'.$filename;

        if (!file_exists(public_path($uploadFolder))) {
            mkdir(public_path($uploadFolder));
        }

        $image->save(public_path($path), self::DEFAULT_IMAGE_QUALITY);

        return new UploadedFile(
            $image->basePath(),
            $image->basename,
            $image->mime,
            $image->filesize(),
            UPLOAD_ERR_OK,
            true
        );
    }
}