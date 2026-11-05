<?php

declare(strict_types=1);

class ImageWatermarker
{
    private string $sourceDir;
    private string $destinationDir;

    private array $watermarks = [];

    private int $jpegQuality = 95;
    private int $webpQuality = 95;
    private int $pngCompression = 6;

    private bool $overwrite = true;
    private bool $recursive = false;

    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    private const POSITIONS = [
        'top-left',
        'top-center',
        'top-right',
        'middle-left',
        'center',
        'middle-right',
        'bottom-left',
        'bottom-center',
        'bottom-right'
    ];

    /**
     * Initialize class.
     */
    public function __construct(array $config)
    {
        $this->sourceDir = $config['source'] ?? '';
        $this->destinationDir = $config['destination'] ?? '';

        $this->jpegQuality = $config['jpeg_quality'] ?? 95;
        $this->webpQuality = $config['webp_quality'] ?? 95;
        $this->pngCompression = $config['png_compression'] ?? 6;

        $this->overwrite = $config['overwrite'] ?? true;
        $this->recursive = $config['recursive'] ?? false;

        $this->watermarks = $config['watermarks'] ?? [];

        $this->validateConfig();
    }

    /**
     * Validate configuration.
     */
    private function validateConfig(): void
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('GD extension is not enabled.');
        }

        if ($this->sourceDir === '' || !is_dir($this->sourceDir)) {
            throw new InvalidArgumentException('Invalid source directory.');
        }

        if ($this->destinationDir === '') {
            throw new InvalidArgumentException('Destination directory is required.');
        }

        if ($this->jpegQuality < 0 || $this->jpegQuality > 100) {
            throw new InvalidArgumentException('Invalid JPEG quality.');
        }

        if ($this->webpQuality < 0 || $this->webpQuality > 100) {
            throw new InvalidArgumentException('Invalid WebP quality.');
        }

        if ($this->pngCompression < 0 || $this->pngCompression > 9) {
            throw new InvalidArgumentException('Invalid PNG compression.');
        }

        if (empty($this->watermarks)) {
            throw new InvalidArgumentException('At least one watermark is required.');
        }

        foreach ($this->watermarks as $watermark) {
            $this->validateWatermark($watermark);
        }

        if (!is_dir($this->destinationDir)) {
            if (!mkdir($this->destinationDir, 0755, true) && !is_dir($this->destinationDir)) {
                throw new RuntimeException('Unable to create destination directory.');
            }
        }

        if (!is_writable($this->destinationDir)) {
            throw new RuntimeException('Destination directory is not writable.');
        }
    }

    /**
     * Validate individual watermark.
     */
    private function validateWatermark(array $watermark): void
    {
        $type = $watermark['type'] ?? '';

        if (!in_array($type, ['image', 'text'], true)) {
            throw new InvalidArgumentException('Watermark type must be image or text.');
        }

        $position = $watermark['position'] ?? 'center';

        if (!in_array($position, self::POSITIONS, true)) {
            throw new InvalidArgumentException('Invalid watermark position.');
        }

        $opacity = $watermark['opacity'] ?? 100;

        if ($opacity < 0 || $opacity > 100) {
            throw new InvalidArgumentException('Opacity must be between 0 and 100.');
        }

        $margin = $watermark['margin'] ?? 20;

        if ($margin < 0) {
            throw new InvalidArgumentException('Margin cannot be negative.');
        }

        if ($type === 'image') {
            $path = $watermark['path'] ?? '';

            if (!is_file($path) || !is_readable($path)) {
                throw new InvalidArgumentException('Watermark image not found: ' . $path);
            }

            $info = @getimagesize($path);

            if ($info === false || ($info[2] ?? null) !== IMAGETYPE_PNG) {
                throw new InvalidArgumentException('Image watermark must be a valid PNG.');
            }

            $widthPercent = $watermark['width_percent'] ?? 25;

            if ($widthPercent < 1 || $widthPercent > 100) {
                throw new InvalidArgumentException('Watermark width must be between 1 and 100.');
            }
        }

        if ($type === 'text') {
            if (trim($watermark['text'] ?? '') === '') {
                throw new InvalidArgumentException('Text watermark cannot be empty.');
            }

            $font = $watermark['font'] ?? '';

            if (!is_file($font) || !is_readable($font)) {
                throw new InvalidArgumentException('TTF font file not found.');
            }

            $fontSize = $watermark['font_size'] ?? 24;

            if ($fontSize < 1) {
                throw new InvalidArgumentException('Font size must be positive.');
            }
        }
    }

    /**
     * Sanitize filename.
     */
    private function sanitizeFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);

        $name = strtolower(trim($name));

        // Replace every consecutive special-character/space sequence
        // with one hyphen.
        $name = preg_replace('/[^a-z0-9]+/', '-', $name) ?? '';

        $name = trim($name, '-');

        return $name !== '' ? $name : 'image';
    }

    /**
     * Load source image.
     */
    private function loadImage(string $path, string $extension): GdImage|false
    {
        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),

            'webp' => function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($path)
                : false,

            default => false
        };
    }

    /**
     * Calculate position.
     */
    private function getPosition(
        int $imageWidth,
        int $imageHeight,
        int $watermarkWidth,
        int $watermarkHeight,
        string $position,
        int $margin
    ): array {

        $positions = [
            'top-left' => [$margin, $margin],

            'top-center' => [
                (int) (($imageWidth - $watermarkWidth) / 2),
                $margin
            ],

            'top-right' => [
                $imageWidth - $watermarkWidth - $margin,
                $margin
            ],

            'middle-left' => [
                $margin,
                (int) (($imageHeight - $watermarkHeight) / 2)
            ],

            'center' => [
                (int) (($imageWidth - $watermarkWidth) / 2),
                (int) (($imageHeight - $watermarkHeight) / 2)
            ],

            'middle-right' => [
                $imageWidth - $watermarkWidth - $margin,
                (int) (($imageHeight - $watermarkHeight) / 2)
            ],

            'bottom-left' => [
                $margin,
                $imageHeight - $watermarkHeight - $margin
            ],

            'bottom-center' => [
                (int) (($imageWidth - $watermarkWidth) / 2),
                $imageHeight - $watermarkHeight - $margin
            ],

            'bottom-right' => [
                $imageWidth - $watermarkWidth - $margin,
                $imageHeight - $watermarkHeight - $margin
            ]
        ];

        [$x, $y] = $positions[$position];

        return [
            max(0, $x),
            max(0, $y)
        ];
    }

    /**
     * Apply opacity to a truecolor watermark.
     *
     * GD alpha: 0 = opaque, 127 = transparent.
     */
    private function applyOpacity(GdImage $image, int $opacity): void
    {
        if ($opacity >= 100) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {

                $rgba = imagecolorsforindex(
                    $image,
                    imagecolorat($image, $x, $y)
                );

                $originalAlpha = $rgba['alpha'];

                $newAlpha = 127 - (int) round(
                    (127 - $originalAlpha) * ($opacity / 100)
                );

                $color = imagecolorallocatealpha(
                    $image,
                    $rgba['red'],
                    $rgba['green'],
                    $rgba['blue'],
                    $newAlpha
                );

                imagesetpixel($image, $x, $y, $color);
            }
        }
    }

    /**
     * Apply image watermark.
     */
    private function applyImageWatermark(
        GdImage $canvas,
        array $config
    ): void {

        // @ suppresses harmless libpng warnings (e.g. "iCCP: known incorrect sRGB profile").
        $watermark = @imagecreatefrompng($config['path']);

        if (!$watermark instanceof GdImage) {
            throw new RuntimeException('Unable to load image watermark.');
        }

        $originalWidth = imagesx($watermark);
        $originalHeight = imagesy($watermark);

        $imageWidth = imagesx($canvas);
        $imageHeight = imagesy($canvas);

        $widthPercent = $config['width_percent'] ?? 25;
        $margin = $config['margin'] ?? 20;

        $maxWidth = (int) floor($imageWidth * ($widthPercent / 100));

        $scale = min(
            1,
            $maxWidth / $originalWidth,
            max(0, $imageWidth - 2 * $margin) / $originalWidth,
            max(0, $imageHeight - 2 * $margin) / $originalHeight
        );

        if ($scale <= 0) {
            imagedestroy($watermark);
            throw new RuntimeException('Image is too small for watermark.');
        }

        $width = max(1, (int) round($originalWidth * $scale));
        $height = max(1, (int) round($originalHeight * $scale));

        $resized = imagecreatetruecolor($width, $height);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);

        imagecopyresampled(
            $resized,
            $watermark,
            0, 0, 0, 0,
            $width, $height,
            $originalWidth, $originalHeight
        );

        $opacity = $config['opacity'] ?? 100;

        $this->applyOpacity($resized, $opacity);

        [$x, $y] = $this->getPosition(
            $imageWidth,
            $imageHeight,
            $width,
            $height,
            $config['position'] ?? 'center',
            $margin
        );

        imagealphablending($canvas, true);

        imagecopy(
            $canvas,
            $resized,
            $x,
            $y,
            0,
            0,
            $width,
            $height
        );

        imagedestroy($watermark);
        imagedestroy($resized);
    }

    /**
     * Apply text watermark.
     */
    private function applyTextWatermark(
        GdImage $canvas,
        array $config
    ): void {

        $text = $config['text'];
        $font = $config['font'];

        $fontSize = $config['font_size'] ?? 24;
        $opacity = $config['opacity'] ?? 100;
        $margin = $config['margin'] ?? 20;

        $color = $config['color'] ?? '#FFFFFF';
        $position = $config['position'] ?? 'center';

        if (!preg_match('/^#([0-9a-fA-F]{6})$/', $color, $matches)) {
            throw new InvalidArgumentException('Text color must be a hexadecimal color.');
        }

        $hex = $matches[1];

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        $alpha = 127 - (int) round(127 * ($opacity / 100));

        $textColor = imagecolorallocatealpha(
            $canvas,
            $red,
            $green,
            $blue,
            $alpha
        );

        $box = imagettfbbox($fontSize, 0, $font, $text);

        if ($box === false) {
            throw new RuntimeException('Unable to calculate text dimensions.');
        }

        $textWidth = abs($box[2] - $box[0]);
        $textHeight = abs($box[7] - $box[1]);

        $imageWidth = imagesx($canvas);
        $imageHeight = imagesy($canvas);

        $positions = [
            'top-left' => [$margin - $box[6], $margin + $textHeight],
            'top-center' => [(int) (($imageWidth - $textWidth) / 2) - $box[6], $margin + $textHeight],
            'top-right' => [$imageWidth - $textWidth - $margin - $box[6], $margin + $textHeight],

            'middle-left' => [$margin - $box[6], (int) (($imageHeight + $textHeight) / 2)],
            'center' => [(int) (($imageWidth - $textWidth) / 2) - $box[6], (int) (($imageHeight + $textHeight) / 2)],
            'middle-right' => [$imageWidth - $textWidth - $margin - $box[6], (int) (($imageHeight + $textHeight) / 2)],

            'bottom-left' => [$margin - $box[6], $imageHeight - $margin],
            'bottom-center' => [(int) (($imageWidth - $textWidth) / 2) - $box[6], $imageHeight - $margin],
            'bottom-right' => [$imageWidth - $textWidth - $margin - $box[6], $imageHeight - $margin]
        ];

        [$x, $y] = $positions[$position];

        imagealphablending($canvas, true);

        if (!imagettftext(
            $canvas,
            $fontSize,
            0,
            $x,
            $y,
            $textColor,
            $font,
            $text
        )) {
            throw new RuntimeException('Unable to render text watermark.');
        }
    }

    /**
     * Save output image.
     */
    private function saveImage(
        GdImage $image,
        string $path,
        string $extension
    ): bool {

        return match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $path, $this->jpegQuality),

            'png' => imagepng($image, $path, $this->pngCompression),

            'webp' => function_exists('imagewebp')
                ? imagewebp($image, $path, $this->webpQuality)
                : false,

            default => false
        };
    }

    /**
     * Process one image.
     */
    public function watermarkImage(string $filename): array
    {
        $sourcePath = $this->sourceDir . DIRECTORY_SEPARATOR . $filename;

        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            return ['success' => false, 'message' => 'Source image not found.'];
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->allowedExtensions, true)) {
            return ['success' => false, 'message' => 'Unsupported image format.'];
        }

        $info = @getimagesize($sourcePath);

        $expectedTypes = [
            'jpg' => IMAGETYPE_JPEG,
            'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
            'webp' => defined('IMAGETYPE_WEBP') ? IMAGETYPE_WEBP : -1
        ];

        if ($info === false || ($info[2] ?? null) !== $expectedTypes[$extension]) {
            return ['success' => false, 'message' => 'Invalid image format.'];
        }

        $source = $this->loadImage($sourcePath, $extension);

        if (!$source instanceof GdImage) {
            return ['success' => false, 'message' => 'Unable to load source image.'];
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);

            // Create working canvas.
            $canvas = imagecreatetruecolor($width, $height);

            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);

            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparent);

            imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

            // Apply all configured watermarks.
            foreach ($this->watermarks as $config) {

                if (($config['type'] ?? '') === 'image') {
                    $this->applyImageWatermark($canvas, $config);
                }

                if (($config['type'] ?? '') === 'text') {
                    $this->applyTextWatermark($canvas, $config);
                }
            }

            // Preserve PNG transparency.
            imagesavealpha($canvas, true);

            $newFilename = $this->sanitizeFilename($filename) . '.' . $extension;

            $destinationPath = $this->destinationDir
                . DIRECTORY_SEPARATOR
                . $newFilename;

            if (!$this->overwrite && file_exists($destinationPath)) {
                throw new RuntimeException('Destination image already exists.');
            }

            // Save directly; existing destination is overwritten.
            if (!$this->saveImage($canvas, $destinationPath, $extension)) {
                throw new RuntimeException('Unable to save destination image.');
            }

            imagedestroy($canvas);

            return [
                'success' => true,
                'filename' => $newFilename,
                'path' => $destinationPath,
                'message' => 'Watermark applied successfully.'
            ];

        } catch (Throwable $e) {

            if (isset($canvas) && $canvas instanceof GdImage) {
                imagedestroy($canvas);
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

        } finally {
            imagedestroy($source);
        }
    }

    /**
     * Process all images.
     */
    public function watermarkAll(): array
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'files' => []
        ];

        if ($this->recursive) {

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->sourceDir,
                    FilesystemIterator::SKIP_DOTS
                )
            );

            $files = [];

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = $file->getPathname();
                }
            }

        } else {

            $entries = scandir($this->sourceDir);

            if ($entries === false) {
                throw new RuntimeException('Unable to read source directory.');
            }

            $files = [];

            foreach ($entries as $entry) {
                $path = $this->sourceDir . DIRECTORY_SEPARATOR . $entry;

                if (is_file($path)) {
                    $files[] = $path;
                }
            }
        }

        foreach ($files as $sourcePath) {

            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

            if (!in_array($extension, $this->allowedExtensions, true)) {
                $results['skipped']++;
                continue;
            }

            $relativePath = substr(
                $sourcePath,
                strlen(rtrim($this->sourceDir, DIRECTORY_SEPARATOR)) + 1
            );

            $relativeDir = dirname($relativePath);

            $filename = basename($relativePath);

            // Recreate nested directory structure in destination.
            $originalDestinationDir = $this->destinationDir;

            if ($this->recursive && $relativeDir !== '.') {

                $this->destinationDir = $originalDestinationDir
                    . DIRECTORY_SEPARATOR
                    . $relativeDir;

                if (!is_dir($this->destinationDir)) {
                    mkdir($this->destinationDir, 0755, true);
                }
            }

            // watermarkImage() expects a path relative to the source directory.
            $result = $this->watermarkImage($relativePath);

            // Restore destination directory.
            $this->destinationDir = $originalDestinationDir;

            $results['files'][$relativePath] = $result;

            if ($result['success']) {
                $results['processed']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }
}