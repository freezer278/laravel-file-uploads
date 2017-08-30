<?php

namespace Vmorozov\FileUploads;

use Illuminate\Support\ServiceProvider;

class FileUploadsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/file_uploads.php' => app()->basePath() . '/config/file_uploads.php',
        ]);
    }
}