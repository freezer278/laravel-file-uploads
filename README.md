# laravel-file-uploads
A package for convenient way to upload files to the different storages

## Installation
1. Run the command below to add this package:  
```
composer require vmorozov/laravel-file-uploads
```

2. Open your `config/app.php` and add the following to the providers array:
```php
Vmorozov\FileUploads\FileUploadsServiceProvider::class
```

3. Run the command below to publish the package config file config/file_uploads.php:  
```
php artisan vendor:publish
```

## Usage
#### To upload file:
```php
public function store(Request $request)
{   
    // This will upload your file to the default folder of selected in config storage
    Uploader::uploadFile($request->file('some_file'));
    
    // This will upload your file to the given as second parameter path of selected in config storage
    Uploader::uploadFile($request->file('some_file'), 'path/to/upload');
}
```

#### To upload base64 string of image:
```php
public function store(Request $request)
{   
    // This will upload your file to the default folder of selected in config storage
    Uploader::uploadBase64Image($request->input('image'));
    
    // This will upload your file to the given as second parameter path of selected in config storage
    Uploader::uploadFile($request->input('image'), 'path/to/upload');
}
```
