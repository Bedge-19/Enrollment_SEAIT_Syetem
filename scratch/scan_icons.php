<?php
$files = array_merge(glob('views/*.php'), glob('views/includes/*.php'), ['index.php']);
$icons = [];
$totalOccurrences = 0;

foreach ($files as $f) {
    $c = file_get_contents($f);
    if (preg_match_all('/<span[^>]*class="[^"]*material-symbols-outlined[^"]*"[^>]*>([a-z0-9_]+)<\/span>/i', $c, $m)) {
        foreach ($m[1] as $icon) {
            $icons[$icon] = ($icons[$icon] ?? 0) + 1;
            $totalOccurrences++;
        }
    }
}

arsort($icons);
echo "Total Icon Occurrences: $totalOccurrences\n";
echo "Distinct Icons: " . count($icons) . "\n\n";
foreach ($icons as $icon => $count) {
    echo sprintf("%-30s %d\n", $icon, $count);
}
