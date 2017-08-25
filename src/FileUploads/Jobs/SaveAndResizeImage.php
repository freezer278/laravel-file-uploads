<?php

namespace Vmorozov\FileUploads\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\Facades\Image;
use Intervention\Image\File;
use Vmorozov\FileUploads\FilesSaver;

class SaveAndResizeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $path;
    private $width;
    private $height;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param int $width
     * @param int $height
     */
    public function __construct(string $filePath, int $width = 0, int $height = 0)
    {
        $this->width = $width;
        $this->height = $height;
        $this->path = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = $this->createUploadedFileFromPath($this->path);

        FilesSaver::uploadFile($file, '', false, false, $this->path);

        FilesSaver::deleteFile(public_path($this->path), true);
    }

    private function createUploadedFileFromPath(string $path)
    {
        if (FilesSaver::checkFileIsImageByPath(public_path($path))) {
            $file = Image::make(public_path($path));

            if ($this->width != 0 && $this->height != 0) {
                $file->fit($this->width, $this->height);
            }

            $file->encode(FilesSaver::DEFAULT_IMAGE_EXTENSION, FilesSaver::DEFAULT_IMAGE_QUALITY);

            $file->save(public_path($path), FilesSaver::DEFAULT_IMAGE_QUALITY);
        }
        else {
            $file = (new File())->setFileInfoFromPath($path);
        }

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
