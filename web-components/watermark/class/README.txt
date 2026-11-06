IMAGE WATERMARKER

A reusable PHP class for adding image and/or text watermarks to JPG,
JPEG, PNG and WebP images using PHP GD.

This README explains how to install the class, configure it, use each
feature, and understand the returned results.

  -------------
  1. FEATURES
  -------------

-   Supports JPG, JPEG, PNG and WebP source images.
-   Supports PNG image watermarks with transparent backgrounds.
-   Supports text watermarks using TrueType (.ttf) fonts.
-   Supports multiple watermarks on the same image.
-   Supports opacity control for image and text watermarks.
-   Supports nine watermark positions.
-   Supports watermark margins.
-   Resizes image watermarks proportionally according to a percentage of
    the source image width.
-   Preserves the source image’s original dimensions.
-   Can process one image or all supported images in a folder.
-   Can process nested folders when recursive mode is enabled.
-   Recreates the nested folder structure in the destination directory
    when using recursive batch processing.
-   Can overwrite existing destination images or refuse to overwrite
    them.
-   Allows JPEG, WebP and PNG output quality/compression settings.
-   Sanitizes output filenames: lowercase letters, numbers and single
    hyphens.
-   Never writes the watermarked result over the source image when
    source and destination directories are configured separately.

IMPORTANT: The class saves each result using the same file extension as
its source. It does not convert JPG to PNG, PNG to WebP, or change
output formats.

  -----------------
  2. REQUIREMENTS
  -----------------

-   PHP 8.0 or later is recommended. The class uses PHP union types and
    match expressions.
-   PHP GD extension must be enabled.
-   The GD build must support the image formats you intend to use: JPEG,
    PNG and/or WebP.
-   Text watermarks require FreeType support in GD.
-   Text watermark fonts must be TrueType .ttf files.
-   Image watermarks must be valid PNG files.

To check whether GD is enabled, run:

    <?php
    var_dump(extension_loaded('gd'));
    ?>

For WebP support, check:

    <?php
    var_dump(function_exists('imagecreatefromwebp'));
    var_dump(function_exists('imagewebp'));
    ?>

For text watermark support, check:

    <?php
    var_dump(function_exists('imagettftext'));
    ?>

If a required GD feature is unavailable, enable/install the appropriate
PHP GD support in your PHP environment.

  --------------------------
  3. FILE AND FOLDER SETUP
  --------------------------

Example project structure:

    project/
    |
    |-- ImageWatermarker.php
    |-- watermark.php
    |
    |-- images/
    |   |-- photo-1.jpg
    |   |-- photo-2.png
    |   |
    |   |-- products/
    |       |-- product-1.jpg
    |       |-- product-2.webp
    |
    |-- watermarks/
    |   |-- logo.png
    |   |-- logo-small.png
    |   |-- Roboto-Bold.ttf
    |
    |-- watermarked/

The source directory contains the original images. The destination
directory receives the processed images. The watermarks directory
contains watermark assets and fonts.

Keep the source and destination directories separate to avoid
accidentally processing generated images as source images.

  ----------------
  4. BASIC SETUP
  ----------------

Include the class file:

    require_once __DIR__ . '/ImageWatermarker.php';

Create a configuration array and initialize the class:

    $config = [
        'source'      => __DIR__ . '/images',
        'destination' => __DIR__ . '/watermarked',

        'watermarks' => [
            [
                'type'     => 'image',
                'path'     => __DIR__ . '/watermarks/logo.png',
                'position' => 'center',
            ],
        ],
    ];

    $watermarker = new ImageWatermarker($config);

The destination directory is created automatically if it does not exist.
At least one watermark must be configured.

  -----------------------------
  5. CONFIGURATION PARAMETERS
  -----------------------------

The constructor accepts one associative configuration array.

MAIN PARAMETERS

source Required. Absolute or relative path to the folder containing
original images. The folder must already exist.

    Example:
        'source' => __DIR__ . '/images'

destination Required. Path to the folder where watermarked images will
be saved. The folder is created automatically if it does not exist. It
must be writable by PHP.

    Example:
        'destination' => __DIR__ . '/watermarked'

watermarks Required. An array containing one or more watermark
configurations. Each watermark must have type “image” or “text”.

    Example:
        'watermarks' => [
            [ /* image watermark configuration */ ],
            [ /* text watermark configuration */ ],
        ]

overwrite Optional. Default: true. Controls what happens if the
destination file already exists.

    true:
        Replace the existing destination image.

    false:
        Do not replace it. The image operation returns a failure with the
        message "Destination image already exists."

    Example:
        'overwrite' => false

recursive Optional. Default: false. Controls whether watermarkAll()
searches inside subdirectories.

    false:
        Process supported image files directly inside the source folder only.

    true:
        Process supported image files in the source folder and all nested
        folders. The relative folder structure is recreated in the destination.

    Example:
        'recursive' => true

IMAGE QUALITY PARAMETERS

jpeg_quality Optional. Default: 95. JPEG output quality from 0 to 100.
Higher values generally provide better visual quality and larger files.

    Example:
        'jpeg_quality' => 95

webp_quality Optional. Default: 95. WebP output quality from 0 to 100.

    Example:
        'webp_quality' => 90

png_compression Optional. Default: 6. PNG compression level from 0 to 9.
This controls compression/file size, not a JPEG-style visual quality
percentage. Higher compression can take longer.

    Example:
        'png_compression' => 6

  -------------------------
  6. WATERMARK PARAMETERS
  -------------------------

Each item in the watermarks array represents one watermark.

The supported watermark types are:

    'type' => 'image'

or:

    'type' => 'text'

A. IMAGE WATERMARK PARAMETERS

type Required. Must be “image”.

path Required. Full path to a valid PNG watermark image. PNG is required
for image watermark assets so transparency can be used.

width_percent Optional. Default: 25. Maximum watermark width as a
percentage of the source image width. Allowed range: 1 to 100. The
watermark is resized proportionally; its aspect ratio is retained. The
class also limits its size so it can fit within the image margins.

    Examples:
        'width_percent' => 20
        'width_percent' => 35

opacity Optional. Default: 100. Watermark opacity from 0 to 100.

    100 = fully opaque, retaining the PNG's original transparency.
    75  = partially transparent.
    50  = half opacity.
    0   = fully transparent/invisible.

    Example:
        'opacity' => 70

position Optional. Default: center. Determines where the watermark is
placed.

    Supported values:
        top-left
        top-center
        top-right
        middle-left
        center
        middle-right
        bottom-left
        bottom-center
        bottom-right

    Example:
        'position' => 'bottom-right'

margin Optional. Default: 20. Distance in pixels from the relevant image
edges for corner and edge-aligned positions. It must be zero or a
positive number. Centered positions are centered; the margin does not
shift them.

    Example:
        'margin' => 25

IMAGE WATERMARK EXAMPLE:

    [
        'type'          => 'image',
        'path'          => __DIR__ . '/watermarks/logo.png',
        'width_percent' => 25,
        'opacity'       => 70,
        'position'      => 'bottom-right',
        'margin'        => 20,
    ]

B. TEXT WATERMARK PARAMETERS

type Required. Must be “text”.

text Required. The text to display on the image. It cannot be empty.

    Example:
        'text' => '© My Website'

font Required. Full path to a readable TrueType .ttf font file.

    Example:
        'font' => __DIR__ . '/watermarks/Roboto-Bold.ttf'

font_size Optional. Default: 24. Text font size. Must be a positive
number. The class uses this value with GD’s TrueType text functions.

    Example:
        'font_size' => 28

color Optional. Default: #FFFFFF. Text color in six-digit hexadecimal
format: #RRGGBB.

    Valid examples:
        '#FFFFFF'  White
        '#000000'  Black
        '#FF0000'  Red
        '#FFFF00'  Yellow

    Example:
        'color' => '#FFFFFF'

opacity Optional. Default: 100. Text opacity from 0 to 100. 100 is fully
opaque; 0 is invisible.

position Optional. Default: center. Uses the same nine position values
listed in the image watermark section.

margin Optional. Default: 20. Pixel spacing from the relevant edges for
edge-aligned positions.

TEXT WATERMARK EXAMPLE:

    [
        'type'      => 'text',
        'text'      => '© My Website',
        'font'      => __DIR__ . '/watermarks/Roboto-Bold.ttf',
        'font_size' => 28,
        'color'     => '#FFFFFF',
        'opacity'   => 75,
        'position'  => 'bottom-center',
        'margin'    => 25,
    ]

  ---------------------------------------
  7. EXAMPLE 1 - NORMAL IMAGE WATERMARK
  ---------------------------------------

This example adds one PNG logo watermark to every image directly inside
the source folder.

    <?php

    require_once __DIR__ . '/ImageWatermarker.php';

    $config = [
        'source'      => __DIR__ . '/images',
        'destination' => __DIR__ . '/watermarked',

        'watermarks' => [
            [
                'type'          => 'image',
                'path'          => __DIR__ . '/watermarks/logo.png',
                'width_percent' => 25,
                'opacity'       => 100,
                'position'      => 'center',
                'margin'        => 20,
            ],
        ],
    ];

    $watermarker = new ImageWatermarker($config);

    $result = $watermarker->watermarkAll();

    print_r($result);

  ------------------------------------------
  8. EXAMPLE 2 - CONTROL WATERMARK OPACITY
  ------------------------------------------

Set opacity to a value between 0 and 100.

    'opacity' => 50

Example watermark configuration:

    [
        'type'          => 'image',
        'path'          => __DIR__ . '/watermarks/logo.png',
        'width_percent' => 30,
        'opacity'       => 50,
        'position'      => 'bottom-right',
        'margin'        => 20,
    ]

The same opacity parameter is available for text watermarks.

  -------------------------------
  9. EXAMPLE 3 - TEXT WATERMARK
  -------------------------------

This example adds copyright text to each image.

    <?php

    require_once __DIR__ . '/ImageWatermarker.php';

    $config = [
        'source'      => __DIR__ . '/images',
        'destination' => __DIR__ . '/watermarked',

        'watermarks' => [
            [
                'type'      => 'text',
                'text'      => '© My Website',
                'font'      => __DIR__ . '/watermarks/Roboto-Bold.ttf',
                'font_size' => 28,
                'color'     => '#FFFFFF',
                'opacity'   => 75,
                'position'  => 'bottom-center',
                'margin'    => 25,
            ],
        ],
    ];

    $watermarker = new ImageWatermarker($config);

    $result = $watermarker->watermarkAll();

    print_r($result);

Use a font file that exists on your server. The class does not
automatically search for or download fonts.

  -------------------------------------
  10. EXAMPLE 4 - MULTIPLE WATERMARKS
  -------------------------------------

You can apply more than one watermark to the same source image.

Watermarks are applied in the order in which they appear in the array. A
later watermark may overlap an earlier watermark.

This example applies a semi-transparent logo in the center and copyright
text near the bottom.

    <?php

    require_once __DIR__ . '/ImageWatermarker.php';

    $config = [
        'source'      => __DIR__ . '/images',
        'destination' => __DIR__ . '/watermarked',

        'watermarks' => [

            // First watermark: image logo.
            [
                'type'          => 'image',
                'path'          => __DIR__ . '/watermarks/logo.png',
                'width_percent' => 30,
                'opacity'       => 45,
                'position'      => 'center',
                'margin'        => 20,
            ],

            // Second watermark: copyright text.
            [
                'type'      => 'text',
                'text'      => '© My Website',
                'font'      => __DIR__ . '/watermarks/Roboto-Bold.ttf',
                'font_size' => 24,
                'color'     => '#FFFFFF',
                'opacity'   => 80,
                'position'  => 'bottom-center',
                'margin'    => 25,
            ],

        ],
    ];

    $watermarker = new ImageWatermarker($config);

    $result = $watermarker->watermarkAll();

    print_r($result);

You can add more image and text watermark entries to the same array.

  ----------------------------------------
  11. EXAMPLE 5 - PROCESS NESTED FOLDERS
  ----------------------------------------

Set recursive to true to process images inside subdirectories.

Example source structure:

    images/
    |-- photo-1.jpg
    |-- photo-2.png
    |
    |-- products/
    |   |-- product-1.jpg
    |   |-- product-2.webp
    |
    |-- banners/
        |-- banner-1.jpg

Configuration:

    <?php

    require_once __DIR__ . '/ImageWatermarker.php';

    $config = [
        'source'      => __DIR__ . '/images',
        'destination' => __DIR__ . '/watermarked',

        'recursive' => true,

        'watermarks' => [
            [
                'type'          => 'image',
                'path'          => __DIR__ . '/watermarks/logo.png',
                'width_percent' => 25,
                'opacity'       => 80,
                'position'      => 'bottom-right',
                'margin'        => 20,
            ],
        ],
    ];

    $watermarker = new ImageWatermarker($config);

    $result = $watermarker->watermarkAll();

    print_r($result);

Expected destination structure:

    watermarked/
    |-- photo-1.jpg
    |-- photo-2.png
    |
    |-- products/
    |   |-- product-1.jpg
    |   |-- product-2.webp
    |
    |-- banners/
        |-- banner-1.jpg

The class recreates the relative folder structure during watermarkAll()
when recursive mode is enabled.

IMPORTANT: Use watermarkAll() for recursive batch processing. The public
watermarkImage($filename) method processes the filename/path supplied by
the caller and writes the output directly into the currently configured
destination directory. It does not independently recreate nested output
directories.

  -----------------------------------
  12. EXAMPLE 6 - PROCESS ONE IMAGE
  -----------------------------------

Use watermarkImage() when you want to process only one image instead of
processing the entire folder.

    <?php

    require_once __DIR__ . '/ImageWatermarker.php';

    $config = [
        'source'      => __DIR__ . '/images',
        'destination' => __DIR__ . '/watermarked',

        'watermarks' => [
            [
                'type'          => 'image',
                'path'          => __DIR__ . '/watermarks/logo.png',
                'width_percent' => 25,
                'opacity'       => 90,
                'position'      => 'center',
                'margin'        => 20,
            ],
        ],
    ];

    $watermarker = new ImageWatermarker($config);

    $result = $watermarker->watermarkImage('photo-1.jpg');

    print_r($result);

The filename is relative to the configured source directory.

For example, if source is /project/images, the call above reads:

    /project/images/photo-1.jpg

The output is saved in the configured destination directory.

  -------------------------------------------------
  13. EXAMPLE 7 - DO NOT OVERWRITE EXISTING FILES
  -------------------------------------------------

By default, overwrite is true. If you want to protect existing output
files, set it to false.

    'overwrite' => false

Complete configuration example:

    $config = [
        'source'      => __DIR__ . '/images',
        'destination' => __DIR__ . '/watermarked',
        'overwrite'   => false,

        'watermarks' => [
            [
                'type' => 'image',
                'path' => __DIR__ . '/watermarks/logo.png',
            ],
        ],
    ];

When the destination filename already exists, that image operation
returns success=false and the message:

    Destination image already exists.

When overwrite is true, the existing destination image is replaced.

  --------------------------------------
  14. EXAMPLE 8 - CUSTOM IMAGE QUALITY
  --------------------------------------

You can configure output quality/compression globally:

    $config = [
        'source'         => __DIR__ . '/images',
        'destination'    => __DIR__ . '/watermarked',

        'jpeg_quality'   => 90,
        'webp_quality'   => 90,
        'png_compression'=> 6,

        'watermarks' => [
            [
                'type' => 'image',
                'path' => __DIR__ . '/watermarks/logo.png',
            ],
        ],
    ];

JPEG quality: Range: 0-100. Default: 95.

WebP quality: Range: 0-100. Default: 95.

PNG compression: Range: 0-9. Default: 6.

These settings affect saving of the corresponding source format. The
class does not change an image’s format.

  --------------------------
  15. RUNNING ALL EXAMPLES
  --------------------------

A typical script follows these steps:

1.  Include ImageWatermarker.php.
2.  Set the source and destination directory paths.
3.  Add one or more watermark configurations.
4.  Set optional quality, overwrite and recursive parameters.
5.  Create the ImageWatermarker object.
6.  Call watermarkImage() for one image, or watermarkAll() for a folder.
7.  Check the returned result.

Example:

    $watermarker = new ImageWatermarker($config);

    // Process one file:
    $result = $watermarker->watermarkImage('photo-1.jpg');

    // OR process all eligible files:
    $result = $watermarker->watermarkAll();

Choose the method you need; you normally call one or the other for a
given task.

  ------------------------------------
  16. UNDERSTANDING THE RETURN VALUE
  ------------------------------------

A. watermarkImage()

Returns an associative array.

On success, the structure is:

    [
        'success'  => true,
        'filename' => 'photo-1.jpg',
        'path'     => '/path/to/watermarked/photo-1.jpg',
        'message'  => 'Watermark applied successfully.',
    ]

On failure, the structure is:

    [
        'success' => false,
        'message' => 'Reason for failure.',
    ]

Example:

    $result = $watermarker->watermarkImage('photo-1.jpg');

    if ($result['success']) {
        echo $result['message'];
        echo $result['path'];
    } else {
        echo $result['message'];
    }

B. watermarkAll()

Returns a summary array containing:

    [
        'processed' => 3,
        'failed'    => 1,
        'skipped'   => 2,
        'files'     => [
            'photo-1.jpg' => [ /* per-file result */ ],
        ],
    ]

processed Number of images successfully watermarked.

failed Number of supported image files that could not be processed.

skipped Number of files ignored because their extensions are not
supported.

files Per-file result, keyed by the relative source path.

Example:

    $result = $watermarker->watermarkAll();

    echo 'Processed: ' . $result['processed'];
    echo 'Failed: ' . $result['failed'];
    echo 'Skipped: ' . $result['skipped'];

    foreach ($result['files'] as $filename => $fileResult) {
        echo $filename . ': ' . $fileResult['message'] . PHP_EOL;
    }

  ---------------------------
  17. FILENAME SANITIZATION
  ---------------------------

The class sanitizes output filenames.

Rules: - Converts letters to lowercase. - Keeps English letters a-z and
digits 0-9. - Replaces each consecutive sequence of spaces or special
characters with one hyphen. - Removes hyphens from the beginning and
end. - Uses “image” if no valid characters remain. - Keeps the original
file extension.

Examples:

    My Photo.jpg       -> my-photo.jpg
    A & B.png          -> a-b.png
    Product!!! 2026.jpg -> product-2026.jpg
    Hello---World.webp -> hello-world.webp
    @@@.jpg            -> image.jpg

Because the sanitized filename is used for the destination, two
different source filenames can produce the same output filename. If
overwrite is true, the later processed file can replace the earlier
output. Review filenames before batch processing if collisions are
possible.

  -----------------------
  18. SUPPORTED FORMATS
  -----------------------

Source formats: .jpg .jpeg .png .webp

Image watermark format: .png only

Text watermark font: .ttf

Output format: Same extension as the source image.

WebP processing requires WebP support in the installed GD library. Text
watermarking requires FreeType support.

  ----------------------------------
  19. VALIDATION AND COMMON ERRORS
  ----------------------------------

The class validates the configuration when the object is created.

Common exceptions include:

    GD extension is not enabled.
        Enable PHP GD.

    Invalid source directory.
        Check that the source folder exists and its path is correct.

    Destination directory is required.
        Provide a destination path.

    Destination directory is not writable.
        Give the PHP process permission to write to the destination.

    At least one watermark is required.
        Add at least one entry to the watermarks array.

    Watermark type must be image or text.
        Use exactly "image" or "text".

    Image watermark must be a valid PNG.
        Use a readable, valid PNG file.

    TTF font file not found.
        Check the font path and file permissions.

    Text color must be a hexadecimal color.
        Use six-digit format such as #FFFFFF.

    Invalid JPEG quality / Invalid WebP quality.
        Use a number from 0 to 100.

    Invalid PNG compression.
        Use a number from 0 to 9.

Individual image-processing failures are returned by watermarkImage() or
included in the files array returned by watermarkAll().

  ---------------------
  20. IMPORTANT NOTES
  ---------------------

-   Always test with copies of images before running a large batch.
-   Keep source and destination directories separate.
-   The class does not resize the original canvas or source image
    dimensions.
-   Image watermarks must be PNG files.
-   Text watermarks need a valid TTF font and GD FreeType support.
-   Multiple watermarks are applied in array order.
-   The class preserves the source extension; it does not convert
    formats.
-   Quality settings are applied when saving the corresponding image
    format.
-   With overwrite=true, existing destination files are replaced.
-   With recursive=true, use watermarkAll() to process nested folders
    and reproduce their directory structure.
-   The class does not delete or modify source images.

  -----------------------------------
  21. QUICK CONFIGURATION REFERENCE
  -----------------------------------

MAIN CONFIGURATION:

    [
        'source'          => '/path/to/source',
        'destination'     => '/path/to/destination',
        'jpeg_quality'    => 95,
        'webp_quality'    => 95,
        'png_compression' => 6,
        'overwrite'       => true,
        'recursive'       => false,

        'watermarks' => [
            // One or more watermark configurations.
        ],
    ]

IMAGE WATERMARK:

    [
        'type'          => 'image',
        'path'          => '/path/to/logo.png',
        'width_percent' => 25,
        'opacity'       => 100,
        'position'      => 'center',
        'margin'        => 20,
    ]

TEXT WATERMARK:

    [
        'type'      => 'text',
        'text'      => '© My Website',
        'font'      => '/path/to/font.ttf',
        'font_size' => 24,
        'color'     => '#FFFFFF',
        'opacity'   => 100,
        'position'  => 'center',
        'margin'    => 20,
    ]

PUBLIC METHODS:

    watermarkImage(string $filename): array
        Process one image.

    watermarkAll(): array
        Process all supported images in the source directory. If recursive
        is true, process nested directories too.

============================================================ END OF
README ============================================================
