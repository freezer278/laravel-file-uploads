<?php

namespace Vmorozov\FileUploads;

use Intervention\Image\Facades\Image;

class ImageCompressor
{
    public static function transformLocalImage(string $path, $width = 0, $height = 0)
    {
        $quality = config('file_uploads.image_quality');

        if (self::checkImageIsPng($path)) {
            self::compressPng($path, $quality);
        }
        else {
            $file = Image::make(public_path($path));

            if ($width != 0 && $height != 0) {
                $file->fit($width, $height);
            }

            $file->encode(config('file_uploads.image_extension'), $quality);

            $file->save(public_path($path), $quality);
        }
    }

    protected static function compressPng(string $path, $quality = 90)
    {
        if (!file_exists($path)) {
            throw new Exception('File does not exist: $path');
        }

        $compressed_png_content = shell_exec('pngquant --quality=5-'.$quality.' - < '.escapeshellarg($path));

        if (!$compressed_png_content) {
//            throw new Exception('Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?');
            return;
        }

        file_put_contents($path, $compressed_png_content);
    }

    protected static function checkImageIsPng(string $path)
    {
        $info = getimagesize($path);

        return $info['mime'] === 'image/png';
    }

}