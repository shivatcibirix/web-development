<?php
/**
 * Batch watermark script (JPG / PNG / WebP) using PHP Imagick.
 *
 * - Originals in source_dir are never modified.
 * - Watermarked copies are written to destination_dir.
 * - Output file names are cleaned: lowercase, spaces -> "-", special
 *   characters removed (e.g. "My Photo (1) #Final!.JPG" -> "my-photo-1-final.jpg").
 * - Keeps original dimensions, transparency, ICC profile, EXIF data,
 *   and animated WebP frames.
 *
 * Usage:
 *   CLI:      php watermark.php
 *   Browser:  http://your-site/watermark.php
 *
 * Folder layout:
 *   watermark.php
 *   source/         <- put originals here
 *   destination/    <- watermarked copies appear here (auto-created)
 *   watermark.png   <- your watermark (transparent PNG recommended)
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// CONFIG
// ---------------------------------------------------------------------------
$config = [
    'source_dir'      => __DIR__ . '/source',
    'destination_dir' => __DIR__ . '/destination',
    'watermark_path'  => __DIR__ . '/watermark.png',

    // top-left | top-center | top-right
    // center-left | center | center-right
    // bottom-left | bottom-center | bottom-right
    'position'        => 'bottom-right',

    'scale'           => 0.20, // watermark width as a fraction of the image width
    'margin'          => 0.02, // distance from the edges, as a fraction of the image width
    'opacity'         => 70,   // 0 (invisible) - 100 (fully opaque)

    // Quality settings
    'jpeg_quality'    => 95,    // 1-100 (saved with 4:4:4 chroma, no subsampling)
    'png_compression' => 9,     // 0-9, always lossless (only affects size/speed)
    'webp_quality'    => 95,    // 1-100
    'webp_lossless'   => false, // true = truly lossless WebP (larger files)

    'overwrite'       => false, // overwrite existing files in destination?
];

// ---------------------------------------------------------------------------
// HELPERS
// ---------------------------------------------------------------------------

function out(string $msg): void
{
    echo $msg . (PHP_SAPI === 'cli' ? PHP_EOL : "<br>\n");
}

/**
 * Clean a file name (without extension):
 * lowercase, accents -> plain letters, spaces -> "-", special characters removed.
 * Allowed characters: a-z, 0-9, "-" and "_".
 */
function sanitize_base_name(string $name): string
{
    $name = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);

    // Accents -> ASCII (é -> e, ü -> u, ß -> ss ...)
    if (class_exists('Transliterator')) {
        $t = Transliterator::create('Any-Latin; Latin-ASCII');
        if ($t !== null) {
            $name = $t->transliterate($name) ?: $name;
        }
    } elseif (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        if ($converted !== false) {
            $name = $converted;
        }
    }

    $name = strtolower($name);                          // 1. lowercase
    $name = trim($name);
    $name = preg_replace('/\s+/', ' ', $name);          // 2. multiple spaces -> one space
    $name = str_replace(' ', '-', $name);               // 3. space -> dash
    $name = preg_replace('/[^a-z0-9_-]/', '', $name);   // 4. remove special characters
    $name = preg_replace('/-{2,}/', '-', $name);        // 5. tidy: "a & b" -> "a-b", not "a--b"
    $name = trim($name, '-_');

    return $name !== '' ? $name : 'image';
}

/** Build a clean, unique output file name for this run. */
function clean_file_name(string $base, string $ext, array &$used): string
{
    $base = sanitize_base_name($base);
    $candidate = "$base.$ext";
    $i = 1;
    while (isset($used[$candidate])) {   // two sources cleaned to the same name
        $candidate = "$base-$i.$ext";
        $i++;
    }
    $used[$candidate] = true;
    return $candidate;
}

/** Keep the original extension if it is valid for the detected format. */
function output_extension(string $origExt, string $format): string
{
    $valid = ['JPEG' => ['jpg', 'jpeg'], 'PNG' => ['png'], 'WEBP' => ['webp']];
    return in_array($origExt, $valid[$format], true) ? $origExt : $valid[$format][0];
}

/** Bake the EXIF orientation into the pixels (phone photos). */
function fix_orientation(Imagick $img): void
{
    if (method_exists($img, 'autoOrient')) {
        $img->autoOrient();
        return;
    }
    $bg = new ImagickPixel('none');
    switch ($img->getImageOrientation()) {
        case Imagick::ORIENTATION_BOTTOMRIGHT: $img->rotateImage($bg, 180); break;
        case Imagick::ORIENTATION_RIGHTTOP:    $img->rotateImage($bg, 90);  break;
        case Imagick::ORIENTATION_LEFTBOTTOM:  $img->rotateImage($bg, -90); break;
    }
    $img->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
}

/** Resize the watermark and apply opacity to its alpha channel. */
function prepare_watermark(Imagick $source, int $targetW, int $imgH, int $opacity): Imagick
{
    $wm = clone $source;
    $ratio = $wm->getImageHeight() / $wm->getImageWidth();
    $targetH = max(1, (int) round($targetW * $ratio));

    // Never let the watermark be taller than the image.
    if ($targetH > $imgH) {
        $targetH = $imgH;
        $targetW = max(1, (int) round($targetH / $ratio));
    }

    $wm->resizeImage($targetW, $targetH, Imagick::FILTER_LANCZOS, 1);

    if ($opacity < 100) {
        $wm->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $wm->evaluateImage(Imagick::EVALUATE_MULTIPLY, max(0, $opacity) / 100, Imagick::CHANNEL_ALPHA);
    }

    return $wm;
}

/** Top-left coordinates of the watermark for the chosen position. */
function watermark_position(string $pos, int $imgW, int $imgH, int $wmW, int $wmH, int $margin): array
{
    $vertical   = str_contains($pos, 'top') ? 'top' : (str_contains($pos, 'bottom') ? 'bottom' : 'middle');
    $horizontal = str_contains($pos, 'left') ? 'left' : (str_contains($pos, 'right') ? 'right' : 'center');

    $x = match ($horizontal) {
        'left'  => $margin,
        'right' => $imgW - $wmW - $margin,
        default => (int) (($imgW - $wmW) / 2),
    };
    $y = match ($vertical) {
        'top'    => $margin,
        'bottom' => $imgH - $wmH - $margin,
        default  => (int) (($imgH - $wmH) / 2),
    };

    return [max(0, $x), max(0, $y)];
}

/** Save the image in its original format using high-quality settings. */
function save_image(Imagick $img, string $format, string $path, array $cfg): bool
{
    switch ($format) {
        case 'JPEG':
            $img->setImageFormat('jpeg');
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality((int) $cfg['jpeg_quality']);
            $img->setSamplingFactors(['1x1', '1x1', '1x1']); // 4:4:4, no chroma loss
            $img->setInterlaceScheme(Imagick::INTERLACE_JPEG); // progressive
            break;

        case 'PNG':
            $img->setImageFormat('png');
            $img->setOption('png:compression-level', (string) $cfg['png_compression']);
            break;

        case 'WEBP':
            $img->setImageFormat('webp');
            $img->setOption('webp:lossless', $cfg['webp_lossless'] ? 'true' : 'false');
            $img->setOption('webp:method', '6'); // best compression
            $img->setImageCompressionQuality((int) $cfg['webp_quality']);
            break;

        default:
            return false;
    }

    return $img->getNumberImages() > 1
        ? $img->writeImages($path, true)   // animated
        : $img->writeImage($path);
}

// ---------------------------------------------------------------------------
// MAIN
// ---------------------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    echo '<pre style="font-family:monospace">';
}

@set_time_limit(0);

if (!extension_loaded('imagick')) {
    exit('The Imagick extension is required.');
}

$sourceDir = realpath($config['source_dir']);
if ($sourceDir === false || !is_dir($sourceDir)) {
    exit('Source folder not found: ' . $config['source_dir']);
}

if (!is_dir($config['destination_dir']) && !mkdir($config['destination_dir'], 0755, true)) {
    exit('Could not create destination folder.');
}
$destDir = realpath($config['destination_dir']);

// Safety: never write into the source folder.
if ($destDir === $sourceDir) {
    exit('Destination folder must be different from the source folder.');
}

try {
    $watermarkSource = new Imagick($config['watermark_path']);
} catch (ImagickException $e) {
    exit('Watermark image not found or unsupported: ' . $config['watermark_path']);
}

$allowed = ['jpg', 'jpeg', 'png', 'webp'];
$files = [];
foreach (scandir($sourceDir) ?: [] as $entry) {
    $full = $sourceDir . '/' . $entry;
    if (is_file($full) && in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $allowed, true)) {
        $files[] = $entry;
    }
}
if (!$files) {
    exit('No images found in source folder.');
}

$done = $skipped = $failed = 0;
$usedNames = [];

foreach ($files as $entry) {
    $file = $sourceDir . '/' . $entry;
    $origExt = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
    $origBase = pathinfo($entry, PATHINFO_FILENAME);

    try {
        $img = new Imagick($file);
        $format = strtoupper($img->getImageFormat());

        if (!in_array($format, ['JPEG', 'PNG', 'WEBP'], true)) {
            throw new RuntimeException("unsupported format $format");
        }

        $newName = clean_file_name($origBase, output_extension($origExt, $format), $usedNames);
        $target = $destDir . '/' . $newName;

        if (file_exists($target) && !$config['overwrite']) {
            out("SKIP  $entry -> $newName (already exists)");
            $skipped++;
            $img->clear();
            continue;
        }

        fix_orientation($img);

        // Animated images: expand every frame to full size first.
        if ($img->getNumberImages() > 1) {
            $img = $img->coalesceImages();
        }

        $imgW = $img->getImageWidth();
        $imgH = $img->getImageHeight();

        $wm = prepare_watermark(
            $watermarkSource,
            max(1, (int) round($imgW * $config['scale'])),
            $imgH,
            (int) $config['opacity']
        );
        $wmW = $wm->getImageWidth();
        $wmH = $wm->getImageHeight();

        [$x, $y] = watermark_position(
            $config['position'],
            $imgW,
            $imgH,
            $wmW,
            $wmH,
            (int) round($imgW * $config['margin'])
        );

        foreach ($img as $frame) {
            $frame->compositeImage($wm, Imagick::COMPOSITE_OVER, $x, $y);
        }

        if (!save_image($img, $format, $target, $config)) {
            throw new RuntimeException('cannot save');
        }

        out("OK    $entry -> $newName");
        $done++;

        $wm->clear();
        $img->clear();
    } catch (Throwable $e) {
        out("FAIL  $entry (" . $e->getMessage() . ')');
        $failed++;
    }
}

out('');
out("Done: $done | Skipped: $skipped | Failed: $failed");

if (PHP_SAPI !== 'cli') {
    echo '</pre>';
}