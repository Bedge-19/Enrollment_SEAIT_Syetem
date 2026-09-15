<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Clinic', 'Admin'])) {
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

    $studentId = $_POST['StudentID'] ?? 0;
    $clinicId = $_POST['ClinicID'] ?? 0;
    $clearanceId = $_POST['ClearanceID'] ?? 0;
    $enrollmentId = $_POST['EnrollmentID'] ?? 0;
    
    $clinicStatus = $_POST['ClinicStatus'] ?? 'Pending';
    $clearanceStatus = $_POST['ClearanceStatus'] ?? 'Pending';
    $staffId = $_SESSION['StaffID'] ?? 1;

    if (!$studentId) {
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Invalid parameters."));
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($clinicId) {
            $stmtClinic = $pdo->prepare("UPDATE clinic SET Status = ?, CompletionDate = CURDATE(), StaffID = ? WHERE ClinicID = ?");
            $stmtClinic->execute([$clinicStatus, $staffId, $clinicId]);
        }

        if ($clearanceId) {
            $stmtClearance = $pdo->prepare("UPDATE clearance SET Status = ?, ClearanceDate = CURDATE() WHERE ClearanceID = ?");
            $stmtClearance->execute([$clearanceStatus, $clearanceId]);
        }

        if ($enrollmentId) {
            // Check payment status
            $stmtPay = $pdo->prepare("SELECT PaymentStatus FROM payment WHERE StudentID = ? ORDER BY PaymentID DESC LIMIT 1");
            $stmtPay->execute([$studentId]);
            $paymentStatus = $stmtPay->fetchColumn();

            if ($clinicStatus === 'Completed' && $clearanceStatus === 'Cleared' && $paymentStatus === 'Paid') {
                $stmtEnroll = $pdo->prepare("UPDATE enrollment SET Status = 'Enrolled' WHERE EnrollmentID = ?");
                $stmtEnroll->execute([$enrollmentId]);
            }
        }

        $pdo->commit();
        header("Location: ../views/staff_dashboard.php?msg=Clearance+Updated");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Database action failed. Please try again."));
        exit;
    }
}
?>
