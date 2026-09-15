<?php
$map = [
    'check_circle' => 'fa-circle-check',
    'error' => 'fa-circle-exclamation',
    'warning' => 'fa-triangle-exclamation',
    'cancel' => 'fa-circle-xmark',
    'school' => 'fa-graduation-cap',
    'close' => 'fa-xmark',
    'fact_check' => 'fa-clipboard-check',
    'search' => 'fa-magnifying-glass',
    'point_of_sale' => 'fa-cash-register',
    'payments' => 'fa-money-bill-wave',
    'receipt_long' => 'fa-receipt',
    'receipt' => 'fa-receipt',
    'print' => 'fa-print',
    'how_to_reg' => 'fa-user-check',
    'verified' => 'fa-circle-check',
    'verified_user' => 'fa-user-shield',
    'delete' => 'fa-trash-can',
    'person' => 'fa-user',
    'person_add' => 'fa-user-plus',
    'medical_services' => 'fa-briefcase-medical',
    'shield_person' => 'fa-user-shield',
    'badge' => 'fa-id-badge',
    'info' => 'fa-circle-info',
    'calendar_month' => 'fa-calendar-days',
    'calendar_view_week' => 'fa-calendar-week',
    'manage_accounts' => 'fa-users-gear',
    'class' => 'fa-book-bookmark',
    'domain' => 'fa-building',
    'add' => 'fa-plus',
    'add_circle' => 'fa-circle-plus',
    'monitor_heart' => 'fa-heart-pulse',
    'personal_injury' => 'fa-bed-pulse',
    'dark_mode' => 'fa-moon',
    'light_mode' => 'fa-sun',
    'schedule' => 'fa-clock',
    'account_balance' => 'fa-landmark',
    'account_balance_wallet' => 'fa-wallet',
    'admin_panel_settings' => 'fa-shield-halved',
    'health_and_safety' => 'fa-shield-heart',
    'apartment' => 'fa-building-columns',
    'category' => 'fa-shapes',
    'family_restroom' => 'fa-people-roof',
    'home' => 'fa-house',
    'save' => 'fa-floppy-disk',
    'stethoscope' => 'fa-stethoscope',
    'menu_book' => 'fa-book-open',
    'account_tree' => 'fa-diagram-project',
    'done_all' => 'fa-check-double',
    'recent_patient' => 'fa-address-book',
    'dashboard_customize' => 'fa-table-cells-large',
    'assignment' => 'fa-clipboard-list',
    'playlist_add_check' => 'fa-list-check',
    'local_library' => 'fa-book-atlas',
    'medical_information' => 'fa-notes-medical',
    'dashboard' => 'fa-gauge-high',
    'app_registration' => 'fa-id-card',
    'today' => 'fa-calendar-day',
    'groups' => 'fa-users',
    'history' => 'fa-clock-rotate-left',
    'inbox' => 'fa-inbox',
    'hourglass_empty' => 'fa-hourglass-half',
    'history_edu' => 'fa-scroll',
    'bookmark_added' => 'fa-bookmark',
    'arrow_back' => 'fa-arrow-left',
    'send' => 'fa-paper-plane',
    'local_hospital' => 'fa-hospital',
    'checklist' => 'fa-list-check',
    'vital_signs' => 'fa-heart-pulse',
    'pending_actions' => 'fa-clock',
    'task' => 'fa-check-to-slot',
    'playlist_add' => 'fa-folder-plus',
    'more_time' => 'fa-calendar-plus',
    'file_download' => 'fa-file-arrow-down',
    'chevron_right' => 'fa-chevron-right',
    'storefront' => 'fa-shop',
    'sync_saved_locally' => 'fa-arrows-rotate',
    'library_books' => 'fa-book',
    'contact_support' => 'fa-circle-question',
    'route' => 'fa-route',
    'smart_card_reader' => 'fa-id-card-clip',
    'qr_code_2' => 'fa-qrcode',
    'help_outline' => 'fa-circle-question',
    'list_alt' => 'fa-rectangle-list',
    'rule_folder' => 'fa-folder-check',
    'library_add_check' => 'fa-list-check',
    'event_note' => 'fa-calendar-check',
    'id_card' => 'fa-id-card',
    'expand_more' => 'fa-chevron-down',
    'notifications' => 'fa-bell',
    'logout' => 'fa-right-from-bracket',
    'login' => 'fa-right-to-bracket',
    'edit_document' => 'fa-file-pen',
    'arrow_downward' => 'fa-arrow-down',
    'assignment_turned_in' => 'fa-clipboard-check',
    'assignment_ind' => 'fa-clipboard-user',
    'edit_note' => 'fa-pen-to-square',
    'lock_open' => 'fa-lock-open',
    'location_on' => 'fa-location-dot',
    'mail' => 'fa-envelope'
];

$files = array_merge(glob('views/*.php'), glob('views/includes/*.php'), ['index.php']);
$modifiedFiles = 0;
$totalReplaced = 0;

foreach ($files as $f) {
    $c = file_get_contents($f);
    $orig = $c;
    
    // Pattern to match <span ... class="...material-symbols-outlined...">icon_name</span>
    $pattern = '/<span([^>]*class="[^"]*)material-symbols-outlined([^"]*"[^>]*)>([a-z0-9_]+)<\/span>/i';
    
    $c = preg_replace_callback($pattern, function($m) use ($map, &$totalReplaced) {
        $before = $m[1];
        $after = $m[2];
        $iconName = trim($m[3]);
        $faIcon = $map[$iconName] ?? ('fa-' . str_replace('_', '-', $iconName));
        
        // Extract extra attributes if any (like id="themeToggleIcon")
        $attrs = '';
        if (preg_match('/\b(id="[^"]*")/', $before . $after, $idMatch)) {
            $attrs .= ' ' . $idMatch[1];
        }
        if (preg_match('/\b(title="[^"]*")/', $before . $after, $titleMatch)) {
            $attrs .= ' ' . $titleMatch[1];
        }
        
        // Clean up class attribute
        $fullClasses = trim($before . ' ' . $after);
        $fullClasses = preg_replace('/\bid="[^"]*"/', '', $fullClasses);
        $fullClasses = preg_replace('/\btitle="[^"]*"/', '', $fullClasses);
        $fullClasses = preg_replace('/class\s*=\s*"/', '', $fullClasses);
        $fullClasses = preg_replace('/"/', '', $fullClasses);
        $fullClasses = preg_replace('/\s+/', ' ', $fullClasses);
        $fullClasses = trim($fullClasses);
        
        $totalReplaced++;
        return '<i' . $attrs . ' class="fa-solid ' . $faIcon . ($fullClasses ? ' ' . $fullClasses : '') . '"></i>';
    }, $c);
    
    if ($c !== $orig) {
        file_put_contents($f, $c);
        $modifiedFiles++;
        echo "Updated: $f\n";
    }
}

echo "\nSummary: $totalReplaced icons replaced across $modifiedFiles files.\n";
