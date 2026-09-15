<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: ../views/staff_dashboard.php?error=" . urlencode("Unauthorized access."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $validationId = $_POST['ValidationID'] ?? 0;
    $studentId = $_POST['StudentID'] ?? 0;
    $action = $_POST['action'] ?? '';
    $staffId = $_SESSION['StaffID'] ?? 1;

    if (!$validationId || !$studentId) {
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Invalid parameters."));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $status = ($action === 'Completed') ? 'Completed' : 'Failed';

        // Update validation status
        $stmt = $pdo->prepare("UPDATE id_validation SET Status = ?, ValidationDate = CURDATE(), StaffID = ? WHERE ValidationID = ?");
        $stmt->execute([$status, $staffId, $validationId]);

        // If completed, activate the student's login account
        if ($status === 'Completed') {
            $stmt2 = $pdo->prepare("UPDATE login SET Status = 'Active' WHERE StudentID = ? AND UserType = 'Student'");
            $stmt2->execute([$studentId]);
            
            // Generate evaluation record
            $stmt3 = $pdo->prepare("INSERT INTO evaluation (EvaluationDate, Status, StudentID, StaffID) VALUES (CURDATE(), 'Pending', ?, ?)");
            $stmt3->execute([$studentId, $staffId]);
        } else {
            // If failed, make sure login stays inactive
            $stmt2 = $pdo->prepare("UPDATE login SET Status = 'Inactive' WHERE StudentID = ? AND UserType = 'Student'");
            $stmt2->execute([$studentId]);
        }

        $pdo->commit();
        header("Location: ../views/staff_dashboard.php?msg=Validation+$status");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Database action failed. Please try again."));
        exit;
    }
}
?>
