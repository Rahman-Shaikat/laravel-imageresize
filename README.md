# Laravel Image Resize Package

A simple and reusable Laravel package for uploading and resizing images with automatic aspect ratio handling. You can define multiple sizes dynamically (small, thumb, custom), and it supports optional width and height. Perfect for books, sliders, profiles, banners, and any image resizing needs in Laravel.

## Requirements

- PHP >= 8.0

- Laravel 9.x or 10.x
---

## 🌐 Features

- Resize images to multiple sizes in one function call.

- Automatic aspect ratio calculation if height is not provided.

- Supports JPEG, PNG, GIF, and WebP formats.

- Returns all resized image paths for easy database storage.

- Configurable image quality via config/imageresizer.php.

- Works with GdImage objects or uploaded files.

- Can be used in any Laravel application via Composer.

## 📦 Installation

Require the package via Composer:

```bash
composer require wall-e/laravel-imageresize
```

## ⚙️ Configuration

If your Laravel version is auto-discoverable, the service provider is loaded automatically. Otherwise, add the provider manually in config/app.php:

```bash 
'providers' => [
    // Other Service Providers
    WallE\LaravelImageresize\ImageResizerServiceProvider::class,
],
```

Publish the config file (optional):

```bash 
php artisan vendor:publish --provider="WallE\LaravelImageresize\ImageResizerServiceProvider" --tag=config
```

This will create config/imageresizer.php:

```bash 
return [
    'quality' => 90, // Default image quality (JPEG/WebP)
];
```

## 🛠️ Usage

Import the class:

```bash 
use WallE\LaravelImageresize\ImageFunctions;
```

Basic Example:

```bash 
$imagePaths = ImageFunctions::upload(
    $request->file('image_path'), // Uploaded file
    'media/uploads/books', // Storage path
    [
        'small' => ['width' => 158],
        'thumb' => ['width' => 72],
    ]
);
```

Returns an array with resized image paths:

```bash
[
    'original' => 'storage/media/uploads/book/filename-original.webp',
    'small'    => 'storage/media/uploads/book/filename-small.webp',
    'thumb'    => 'storage/media/uploads/book/filename-thumb.webp',
]
```

Save in database:

```bash
$book->image_path       = $imagePaths['original'];
$book->image_sm_path    = $imagePaths['small'];
$book->image_thumb_path = $imagePaths['thumb'];
$book->save();
```

Custom Sizes with Optional Height:

```bash
$imagePaths = ImageFunctions::upload(
    $request->file('image_path'),
    'media/uploads/sliders',
    [
        'large' => ['width' => 1920, 'height' => 600], // exact dimensions
        'medium' => ['width' => 1280],                // height calculated automatically
        'thumb' => ['width' => 320],
    ]
);
```

Using GdImage Objects:

```bash
$gdImage = imagecreatefromjpeg('example.jpg');

$imagePaths = ImageFunctions::upload(
    $gdImage,
    'media/uploads/custom',
    [
        'small' => ['width' => 200],
        'thumb' => ['width' => 100],
    ]
);
```

Advanced Usage:

You can define any number of sizes dynamically:

```bash
$sizes = [
    'small'  => ['width' => 150],
    'medium' => ['width' => 300, 'height' => 300],
    'large'  => ['width' => 600],
    'custom' => ['width' => 1024, 'height' => 768],
];

$imagePaths = ImageFunctions::upload($request->file('image_path'), 'uploads/gallery', $sizes);
```
If height is omitted, it is automatically calculated from the original aspect ratio.


Configuration

You can configure default image quality in config/imageresizer.php:

```bash
return [
    'quality' => 90, // Range 0 - 100
];
```

Supported Formats:

- JPEG (.jpg / .jpeg)

- PNG (.png)

- GIF (.gif)

- WebP (.webp)

All images are converted to WebP by default except GIFs.


Notes:

- Temporary images are created in storage/app/temp-images during processing.

- The package is fully compatible with Laravel’s storage disks. You can modify it to save directly to S3 or other disks.

- Returns array of paths, so you can save multiple sizes to DB.


Example in Controller:

```bash
public function store(Request $request)
{
    $request->validate(['image_path' => 'required|image']);

    $imagePaths = ImageFunctions::upload(
        $request->file('image_path'),
        'media/uploads/books',
        [
            'small' => ['width' => 158],
            'thumb' => ['width' => 72],
        ]
    );

    $book = Book::create([
        'title'        => $request->title,
        'image_path'     => $imagePaths['original'],
        'image_sm_path'  => $imagePaths['small'],
        'image_thumb_path' => $imagePaths['thumb'],
    ]);

    return response()->json([
        'message' => 'Book created successfully',
        'data'    => $book
    ]);
}
```



## 📝 Contributing

Fork the repository and submit pull requests.

Bug reports, feature requests, and improvements are welcome.

Make sure to follow semantic versioning for updates.


## 📜 License

MIT License. See the [LICENSE](LICENSE) file for details.


## 🔗 Links

Packagist: https://packagist.org/packages/wall-e/laravel-imageresize

GitHub Repository: https://github.com/Rahman-Shaikat/laravel-imageresize.git



## ✅ This README explains:

- Installation

- Configuration

- Multiple usage examples

- Handling aspect ratio

- Saving to database

---
