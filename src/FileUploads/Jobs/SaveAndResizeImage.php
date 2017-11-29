<?php

namespace Vmorozov\FileUploads\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\Facades\Image as FacadeImage;
use Intervention\Image\File;
use Intervention\Image\Image;
use Vmorozov\FileUploads\FilesSaver;
use Vmorozov\FileUploads\ImageCompressor;
use Vmorozov\FileUploads\UploadedFilesCreator;

class SaveAndResizeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $storage;
    private $path;
    private $width;
    private $height;
    private $uploadToRemote;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param string $storage
     * @param int $width
     * @param int $height
     */
    public function __construct(string $filePath, string $storage, int $width = 0, int $height = 0, bool $upload = true)
    {
        $this->width = $width;
        $this->height = $height;

        $this->path = $filePath;

        $this->storage = $storage;

        $this->uploadToRemote = $upload;
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

        if ($this->uploadToRemote) {
            $file = UploadedFilesCreator::createUploadedFileFromPath($this->path);

            FilesSaver::uploadFile($file, '', $this->storage, false, $this->path);
            FilesSaver::deleteFile($this->path, FilesSaver::STORAGE_LOCAL);
        }
    }

    private function transformLocalImage(string $path)
    {
        ImageCompressor::transformLocalImage($path, $this->width, $this->height);
    }
}
