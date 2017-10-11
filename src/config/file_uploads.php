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

    /*
    |--------------------------------------------------------------------------
    | This is the default level of quality for the stored images
    |--------------------------------------------------------------------------
    | Possible values are from 1 to 100
    |
    */
    'image_quality' => env('IMAGE_QUALITY', \Vmorozov\FileUploads\FilesSaver::DEFAULT_IMAGE_QUALITY),


    /*
    |--------------------------------------------------------------------------
    | This is the default level of quality for the stored images
    |--------------------------------------------------------------------------
    | Possible values are from 1 to 100
    |
    */
    'image_extension' => env('IMAGE_EXTENSION', \Vmorozov\FileUploads\FilesSaver::DEFAULT_IMAGE_EXTENSION),

    /*
    |--------------------------------------------------------------------------
    | This is the default level of quality for the stored images
    |--------------------------------------------------------------------------
    | Possible values are from 1 to 100
    |
    */
    'default_uploads_folder' => env('DEFAULT_UPLOADS_FOLDER', \Vmorozov\FileUploads\FilesSaver::DEFAULT_UPLOADS_FOLDER),

];