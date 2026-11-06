<?php

/**
 * Watermark every image in ./source, including images inside nested
 * sub-folders, and recreate the same folder structure in ./destination.
 *
 * After processing, the script verifies that every source image has a
 * matching watermarked file at the same relative location in destination.
 */

require_once '../ImageWatermarker.php';

$sourceDir      = __DIR__ . DIRECTORY_SEPARATOR . 'source';
$destinationDir = __DIR__ . DIRECTORY_SEPARATOR . 'destination';

$watermarker = new ImageWatermarker([
    'source'      => $sourceDir,
    'destination' => $destinationDir,

    // REQUIRED for nested folders. Default is false (top-level files only).
    'recursive'   => true,

    'watermarks' => [

        // Main logo in the center.
        [
            'type'          => 'image',
            'path'          => __DIR__ . '/watermarks/watermark.png',
            'position'      => 'center',
            'width_percent' => 30,
            'opacity'       => 40,
            'margin'        => 20
        ],

        // Copyright text at the bottom.
        [
            'type'      => 'text',
            'text'      => '© My Website',
            'font'      => __DIR__ . '/fonts/arial.ttf',
            'font_size' => 20,
            'color'     => '#FFFFFF',
            'opacity'   => 70,
            'position'  => 'bottom-right',
            'margin'    => 20
        ],

        // Secondary logo at the top.
        [
            'type'          => 'image',
            'path'          => __DIR__ . '/watermarks/logo-secondary.png',
            'position'      => 'top-left',
            'width_percent' => 15,
            'opacity'       => 80,
            'margin'        => 20
        ]

    ],

    'overwrite' => true
]);

$result = $watermarker->watermarkAll();