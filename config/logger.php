<?php
// config/logger.php

if (!function_exists('log_activity')) {
    function log_activity($pdo, $action, $module, $details = '') {
        try {
            $user = $_SESSION['Username'] ?? ($_SESSION['UserType'] ?? 'System');
            $role = $_SESSION['Role'] ?? ($_SESSION['UserType'] ?? 'System');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt = $pdo->prepare("INSERT INTO system_log (User, Role, Action, Module, Details, IPAddress) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user, $role, $action, $module, $details, $ip]);
        } catch (Exception $e) {
            // Silently fail logging so business logic does not halt
            error_log("Logging failed: " . $e->getMessage());
        }
    }
}
