<?php
// config/setup_tables.php
require_once __DIR__ . '/db.php';

try {
    // 1. Create system_log table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `system_log` (
            `LogID` int(11) NOT NULL AUTO_INCREMENT,
            `Timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `User` varchar(100) NOT NULL,
            `Role` varchar(50) NOT NULL,
            `Action` varchar(100) NOT NULL,
            `Module` varchar(100) NOT NULL,
            `Details` text DEFAULT NULL,
            `IPAddress` varchar(45) DEFAULT NULL,
            PRIMARY KEY (`LogID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 2. Add sample system logs if empty
    $countLogs = $pdo->query("SELECT COUNT(*) FROM system_log")->fetchColumn();
    if ($countLogs == 0) {
        $sampleLogs = [
            ['admin', 'Admin', 'System Initialization', 'System Overview', 'Enrollment system tables initialized successfully', '127.0.0.1'],
            ['staff1', 'Registrar', 'Admissions Processing', 'Admissions Queue', 'Processed student application review', '127.0.0.1'],
            ['staff4', 'Clinic', 'Health Verification', 'Health Clearance', 'Completed student medical checkup record', '127.0.0.1'],
            ['staff3', 'Accounting', 'POS Settlement', 'Payment Processing', 'Collected matriculation fees and issued receipt', '127.0.0.1']
        ];
        $stmtLog = $pdo->prepare("INSERT INTO system_log (User, Role, Action, Module, Details, IPAddress) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($sampleLogs as $log) {
            $stmtLog->execute($log);
        }
    }

    echo "Database setup completed successfully.\n";
} catch (PDOException $e) {
    echo "Database setup error: " . $e->getMessage() . "\n";
}
