<?php
$fonts = [
    'fa-solid-900.woff2',
    'fa-solid-900.ttf',
    'fa-regular-400.woff2',
    'fa-regular-400.ttf',
    'fa-brands-400.woff2',
    'fa-brands-400.ttf'
];

foreach ($fonts as $f) {
    $url = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/' . $f;
    $target = __DIR__ . '/../assets/fontawesome/webfonts/' . $f;
    $content = @file_get_contents($url);
    if ($content) {
        file_put_contents($target, $content);
        echo "Downloaded: $f (" . strlen($content) . " bytes)\n";
    } else {
        echo "Failed to download: $f\n";
    }
}
