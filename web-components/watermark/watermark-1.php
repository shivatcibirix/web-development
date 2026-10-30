<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/

$sourceDir      = __DIR__ . '/source';
$destinationDir = __DIR__ . '/destination';
$watermarkPath  = __DIR__ . '/watermark.png';

$jpegQuality = 95; // Range: 0-100
$webpQuality = 95; // Range: 0-100
$pngCompression = 6; // Range: 0-9

$watermarkWidthPercent = 25; // Maximum width as % of original image
$watermarkMargin = 20;       // Margin in pixels

/*
|--------------------------------------------------------------------------
| Check GD support
|--------------------------------------------------------------------------
*/

if (!extension_loaded('gd')) {
    exit("Error: PHP GD extension is not enabled.\n");
}

if (!is_dir($sourceDir)) {
    exit("Error: Source folder does not exist.\n");
}

if (!is_file($watermarkPath)) {
    exit("Error: Watermark PNG file does not exist.\n");
}

if (!is_dir($destinationDir)) {
    if (!mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
        exit("Error: Unable to create destination folder.\n");
    }
}

if (!is_readable($watermarkPath)) {
    exit("Error: Watermark file is not readable.\n");
}

if (!is_writable($destinationDir)) {
    exit("Error: Destination folder is not writable.\n");
}

/*
|--------------------------------------------------------------------------
| Generate SEO-friendly filename
|--------------------------------------------------------------------------
|
| Example:
| "My  Summer Photo @2026!.jpg"
| becomes:
| "my-summer-photo-2026.jpg"
|
*/

function sanitizeFilename(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);

    $name = strtolower($name);

    // Convert multiple spaces into one space.
    $name = preg_replace('/\s+/', ' ', trim($name)) ?? '';

    // Replace spaces with hyphens.
    $name = str_replace(' ', '-', $name);

    // Remove special characters.
    $name = preg_replace('/[^a-z0-9-]/', '', $name) ?? '';

    // Avoid consecutive or leading/trailing hyphens.
    $name = preg_replace('/-+/', '-', $name) ?? '';
    $name = trim($name, '-');

    return $name !== '' ? $name : 'image';
}

/*
|--------------------------------------------------------------------------
| Load source image
|--------------------------------------------------------------------------
*/

function loadImage(string $path, string $extension): GdImage|false
{
    return match ($extension) {
        'jpg', 'jpeg' => @imagecreatefromjpeg($path),
        'png'         => @imagecreatefrompng($path),
        'webp'        => function_exists('imagecreatefromwebp')
            ? @imagecreatefromwebp($path)
            : false,
        default       => false,
    };
}

/*
|--------------------------------------------------------------------------
| Save watermarked image
|--------------------------------------------------------------------------
*/

function saveImage(
    GdImage $image,
    string $path,
    string $extension,
    int $jpegQuality,
    int $webpQuality,
    int $pngCompression
): bool {
    return match ($extension) {
        'jpg', 'jpeg' => imagejpeg($image, $path, $jpegQuality),
        'png'         => imagepng($image, $path, $pngCompression),
        'webp'        => function_exists('imagewebp')
            ? imagewebp($image, $path, $webpQuality)
            : false,
        default       => false,
    };
}

/*
|--------------------------------------------------------------------------
| Load watermark
|--------------------------------------------------------------------------
*/

$watermark = @imagecreatefrompng($watermarkPath);

if ($watermark === false) {
    exit("Error: Unable to load watermark PNG.\n");
}

// Preserve the watermark's transparent background.
imagealphablending($watermark, false);
imagesavealpha($watermark, true);

$watermarkOriginalWidth = imagesx($watermark);
$watermarkOriginalHeight = imagesy($watermark);

if ($watermarkOriginalWidth <= 0 || $watermarkOriginalHeight <= 0) {
    imagedestroy($watermark);
    exit("Error: Invalid watermark dimensions.\n");
}

/*
|--------------------------------------------------------------------------
| Process source images
|--------------------------------------------------------------------------
*/

$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

$files = scandir($sourceDir);

if ($files === false) {
    imagedestroy($watermark);
    exit("Error: Unable to read source folder.\n");
}

$processed = 0;
$skipped = 0;
$failed = 0;

foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }

    $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $file;

    // Process files only, not subdirectories.
    if (!is_file($sourcePath) || !is_readable($sourcePath)) {
        continue;
    }

    $extension = strtolower(
        pathinfo($file, PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $allowedExtensions, true)) {
        continue;
    }

    // Check the actual image format, not just the extension.
    $imageInfo = @getimagesize($sourcePath);

    if ($imageInfo === false) {
        echo "Skipped: Invalid image - {$file}\n";
        $skipped++;
        continue;
    }

    $expectedTypes = [
        'jpg'  => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png'  => IMAGETYPE_PNG,
        'webp' => defined('IMAGETYPE_WEBP')
            ? IMAGETYPE_WEBP
            : -1,
    ];

    if (($imageInfo[2] ?? null) !== $expectedTypes[$extension]) {
        echo "Skipped: Extension/format mismatch - {$file}\n";
        $skipped++;
        continue;
    }

    $image = loadImage($sourcePath, $extension);

    if ($image === false) {
        echo "Skipped: Unable to load - {$file}\n";
        $skipped++;
        continue;
    }

    $imageWidth = imagesx($image);
    $imageHeight = imagesy($image);

    /*
    |--------------------------------------------------------------------------
    | Calculate watermark dimensions
    |--------------------------------------------------------------------------
    */

    $maxWatermarkWidth = (int) floor(
        $imageWidth * ($watermarkWidthPercent / 100)
    );

    $scale = min(
        1,
        $maxWatermarkWidth / $watermarkOriginalWidth,
        max(0, $imageWidth - 2 * $watermarkMargin)
            / $watermarkOriginalWidth,
        max(0, $imageHeight - 2 * $watermarkMargin)
            / $watermarkOriginalHeight
    );

    if ($scale <= 0) {
        imagedestroy($image);
        echo "Skipped: Image is too small - {$file}\n";
        $skipped++;
        continue;
    }

    $watermarkWidth = max(
        1,
        (int) round($watermarkOriginalWidth * $scale)
    );

    $watermarkHeight = max(
        1,
        (int) round($watermarkOriginalHeight * $scale)
    );

    // Create a resized watermark with alpha transparency.
    $resizedWatermark = imagecreatetruecolor(
        $watermarkWidth,
        $watermarkHeight
    );

    imagealphablending($resizedWatermark, false);
    imagesavealpha($resizedWatermark, true);

    $transparent = imagecolorallocatealpha(
        $resizedWatermark,
        0,
        0,
        0,
        127
    );

    imagefill($resizedWatermark, 0, 0, $transparent);

    imagecopyresampled(
        $resizedWatermark,
        $watermark,
        0,
        0,
        0,
        0,
        $watermarkWidth,
        $watermarkHeight,
        $watermarkOriginalWidth,
        $watermarkOriginalHeight
    );

    /*
    |--------------------------------------------------------------------------
    | Position watermark at bottom-right
    |--------------------------------------------------------------------------
    */

$x = (int) (($imageWidth - $watermarkWidth) / 2);
$y = (int) (($imageHeight - $watermarkHeight) / 2);

    // Preserve transparency in PNG output.
    if ($extension === 'png') {
        imagealphablending($image, true);
    } else {
        imagealphablending($image, true);
    }

    imagecopy(
        $image,
        $resizedWatermark,
        $x,
        $y,
        0,
        0,
        $watermarkWidth,
        $watermarkHeight
    );

    if ($extension === 'png') {
        imagesavealpha($image, true);
    }

    /*
    |--------------------------------------------------------------------------
    | Create destination filename
    |--------------------------------------------------------------------------
    */

    $newFilename = sanitizeFilename($file) . '.' . $extension;

    $destinationPath = $destinationDir
        . DIRECTORY_SEPARATOR
        . $newFilename;


    /*
    |--------------------------------------------------------------------------
    | Save the new image
    |--------------------------------------------------------------------------
    */

    $saved = saveImage(
        $image,
        $destinationPath,
        $extension,
        $jpegQuality,
        $webpQuality,
        $pngCompression
    );

    if ($saved) {
        echo "Success: {$file} -> {$newFilename}\n";
        $processed++;
    } else {
        echo "Failed: Unable to save - {$newFilename}\n";
        $failed++;
    }

    // Release memory.
    imagedestroy($image);
    imagedestroy($resizedWatermark);
}

// Release watermark memory.
imagedestroy($watermark);

echo "\nProcessing completed.\n";
echo "Processed: {$processed}\n";
echo "Skipped: {$skipped}\n";
echo "Failed: {$failed}\n";
