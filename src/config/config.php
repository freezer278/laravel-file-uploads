<?php

return [

    /*
    |--------------------------------------------------------------------------
    | This is the storage to upload files by default
    |--------------------------------------------------------------------------
    |
    | Now you have two options here:
    | 1. To store locally FilesSaver::STORAGE_LOCAL (this is the default option)
    | 2. To store in s3 storage FilesSaver::STORAGE_AMAZON_S3
    |
    */
    'files_upload_storage' => env('FILES_UPLOAD', \Vmorozov\FileUploads\FilesSaver::STORAGE_LOCAL),

];