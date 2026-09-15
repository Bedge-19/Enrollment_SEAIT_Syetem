<?php
$files = array_merge(glob('views/*.php'), glob('views/includes/*.php'));
$textClasses = [];
$bgClasses = [];
foreach ($files as $f) {
    $content = file_get_contents($f);
    if (preg_match_all('/text-([a-z]+-[0-9]{2,3})/', $content, $m)) {
        $textClasses = array_merge($textClasses, $m[0]);
    }
    if (preg_match_all('/bg-([a-z]+-[0-9]{2,3})/', $content, $m)) {
        $bgClasses = array_merge($bgClasses, $m[0]);
    }
}
$textClasses = array_unique($textClasses);
sort($textClasses);
$bgClasses = array_unique($bgClasses);
sort($bgClasses);

echo "--- TEXT CLASSES ---\n";
echo implode("\n", $textClasses) . "\n";
echo "--- BG CLASSES ---\n";
echo implode("\n", $bgClasses) . "\n";
