<?php

return [

    /*
    |--------------------------------------------------------------------------
    | This is the storage to upload files by default
    |--------------------------------------------------------------------------
    */
    'files_upload_storage' => env('FILES_UPLOAD', \Vmorozov\FileUploads\FilesSaver::STORAGE_LOCAL),

];