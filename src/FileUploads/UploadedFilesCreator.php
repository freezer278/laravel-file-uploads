<?php

namespace Vmorozov\FileUploads;


use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Intervention\Image\File;

class UploadedFilesCreator
{
    /**
     * Creates new UploadedFile instance from the given base64 image string.
     *
     * @param string $value
     * @param string $uploadFolder
     * @param string $extension
     * @return UploadedFile
     */
    public static function createUploadedFileFromBase64(string $value, string $uploadFolder = '', string $extension = ''): UploadedFile
    {
        if ($uploadFolder == '')
            $uploadFolder = config('file_uploads.default_uploads_folder');

        $image = Image::make($value);

        if ($extension === '')
            $extension = config('file_uploads.image_extension');

        $filename = md5($image->basename.microtime()).'.'.$extension;

        $path = $uploadFolder.'/'.$filename;

        if (!file_exists(public_path($uploadFolder))) {
            mkdir(public_path($uploadFolder));
        }

        $image->save(public_path($path), config('file_uploads.image_quality'));

        return new UploadedFile(
            $image->basePath(),
            $image->basename,
            $image->mime,
            $image->filesize(),
            UPLOAD_ERR_OK,
            true
        );
    }

    /**
     * Creates new UploadedFile instance from the given path.
     *
     * @param string $path
     * @return UploadedFile
     */
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
}