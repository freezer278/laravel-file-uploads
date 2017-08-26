<?php

/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 26.08.17
 * Time: 09:51
 */
class FileUploadsServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => app()->basePath() . '/config/file_uploads.php',
        ]);
    }
}