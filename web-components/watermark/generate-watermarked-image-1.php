<?php

require_once 'ImageWatermarker.php';

$watermarker = new ImageWatermarker([
    'source'      => __DIR__ . '/source',
    'destination' => __DIR__ . '/destination',

    'watermarks' => [
        [
            'type'          => 'image',
            'path'          => __DIR__ . '/watermarks/watermark.png',
            'position'      => 'center',
            'width_percent' => 25,
            'opacity'       => 50,
            'margin'        => 20
        ]
    ],

    'jpeg_quality' => 95,
    'webp_quality' => 95,
    'png_compression' => 6,
    'overwrite' => true
]);

$result = $watermarker->watermarkAll();

print_r($result);