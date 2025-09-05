<?php

namespace WallE\LaravelImageresize;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class ImageFunctions
{
    /**
     * Upload and resize an image
     *
     * @param \Illuminate\Http\UploadedFile|\GdImage $image
     * @param string $path
     * @param array $sizes
     * @return array
     * @throws Exception
     */
    public static function upload($image, string $path, array $sizes = []): array
    {
        // Handle GdImage object
        if ($image instanceof \GdImage) {
            $tempDir = storage_path('app/temp-images');
            File::ensureDirectoryExists($tempDir);
            $tempPath = $tempDir . '/temp.jpg';
            imagejpeg($image, $tempPath, config('imageresizer.quality', 90));
            $image = new UploadedFile($tempPath, 'temp.jpg', File::mimeType($tempPath), null, true);
        }

        $originalExt = strtolower($image->getClientOriginalExtension());
        $outputExt = ($originalExt === 'gif') ? 'gif' : 'webp';
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $baseFilename = Str::slug($originalName) . '-' . time();

        $sourceImage = self::createImageResource($image->getPathname(), $originalExt);
        if (!$sourceImage) {
            throw new Exception('Failed to create image resource from uploaded file');
        }

        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        if (!isset($sizes['original'])) {
            $sizes['original'] = ['width' => $originalWidth, 'height' => $originalHeight];
        }

        $results = [];

        foreach ($sizes as $type => $dimensions) {
            $filename = $baseFilename . '-' . $type . '.' . $outputExt;
            $tempPath = storage_path('app/temp-images/' . $filename);
            File::ensureDirectoryExists(dirname($tempPath));

            $targetWidth = $dimensions['width'] ?? $originalWidth;
            $targetHeight = $dimensions['height'] ?? (int)($originalHeight * ($targetWidth / $originalWidth));

            if ($type === 'original') {
                self::saveImageResource($sourceImage, $tempPath, $outputExt);
            } else {
                $resizedImage = self::resizeImage($sourceImage, $originalWidth, $originalHeight, $targetWidth, $targetHeight);
                self::saveImageResource($resizedImage, $tempPath, $outputExt);
                imagedestroy($resizedImage);
            }

            $results[$type] = $tempPath;
        }

        imagedestroy($sourceImage);
        return $results;
    }

    private static function createImageResource($imagePath, $extension)
    {
        try {
            switch ($extension) {
                case 'jpg':
                case 'jpeg': return @imagecreatefromjpeg($imagePath) ?: @imagecreatefromwebp($imagePath);
                case 'png':  return @imagecreatefrompng($imagePath);
                case 'gif':  return @imagecreatefromgif($imagePath);
                case 'webp': return @imagecreatefromwebp($imagePath);
                default:
                    return @imagecreatefromjpeg($imagePath)
                        ?: @imagecreatefrompng($imagePath)
                        ?: @imagecreatefromwebp($imagePath)
                        ?: @imagecreatefromgif($imagePath);
            }
        } catch (Exception $e) {
            Log::error('Image creation failed: ' . $e->getMessage(), ['path' => $imagePath, 'extension' => $extension]);
            return false;
        }
    }

    private static function saveImageResource($imageResource, $path, $format)
    {
        $quality = config('imageresizer.quality', 90);

        if ($format !== 'gif') {
            imagealphablending($imageResource, false);
            imagesavealpha($imageResource, true);
        }

        switch ($format) {
            case 'webp': return imagewebp($imageResource, $path, $quality);
            case 'gif':  return imagegif($imageResource, $path);
            default:     return imagewebp($imageResource, $path, $quality);
        }
    }

    private static function resizeImage($sourceImage, $originalWidth, $originalHeight, $newWidth, $newHeight)
    {
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
        imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled(
            $resizedImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );

        return $resizedImage;
    }
}