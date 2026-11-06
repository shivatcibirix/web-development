<?php

require_once '../ImageWatermarker.php';

$watermarker = new ImageWatermarker([
    'source'      => __DIR__ . '/source',
    'destination' => __DIR__ . '/destination',

    'watermarks' => [
        [
            'type'      => 'text',
            'text'      => '© My Website',
            'font'      => __DIR__ . '/fonts/arial.ttf',
            'font_size' => 28,
            'color'     => '#FFFFFF',
            'opacity'   => 60,
            'position'  => 'center',
            'margin'    => 25
        ]
    ]
]);

$result = $watermarker->watermarkAll();

print_r($result);