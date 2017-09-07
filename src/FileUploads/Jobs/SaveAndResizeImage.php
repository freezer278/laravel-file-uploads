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
use Vmorozov\FileUploads\UploadedFilesCreator;

class SaveAndResizeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $storage;
    private $path;
    private $width;
    private $height;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param string $storage
     * @param int $width
     * @param int $height
     */
    public function __construct(string $filePath, string $storage, int $width = 0, int $height = 0)
    {
        $this->width = $width;
        $this->height = $height;

        $this->path = $filePath;

        $this->storage = $storage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (FilesSaver::checkFileIsImageByPath(public_path($this->path))) {
            $this->transformLocalImage($this->path);
        }

        $file = UploadedFilesCreator::createUploadedFileFromPath($this->path);

        FilesSaver::uploadFile($file, '', $this->storage, false, $this->path);
        FilesSaver::deleteFile($this->path, FilesSaver::STORAGE_LOCAL);
    }

    private function transformLocalImage(string $path): Image
    {
        $file = Image::make(public_path($path));

        if ($this->width != 0 && $this->height != 0) {
            $file->fit($this->width, $this->height);
        }

        $file->encode(FilesSaver::DEFAULT_IMAGE_EXTENSION, FilesSaver::DEFAULT_IMAGE_QUALITY);

        $file->save(public_path($path), FilesSaver::DEFAULT_IMAGE_QUALITY);

        return $file;
    }
}
