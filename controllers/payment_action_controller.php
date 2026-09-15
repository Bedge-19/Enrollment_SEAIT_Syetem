<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Accounting', 'Admin'])) {
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

    $paymentId = (int)($_POST['PaymentID'] ?? 0);
    $studentId = (int)($_POST['StudentID'] ?? 0);
    $enrollmentId = (int)($_POST['EnrollmentID'] ?? 0);
    $amount = (float)($_POST['Amount'] ?? 0);
    $receiptNo = trim($_POST['ReceiptNo'] ?? '');
    $staffId = $_SESSION['StaffID'] ?? 1;

    if (!$studentId) {
        header("Location: ../views/accounting_pos.php?error=" . urlencode("Student must be selected."));
        exit;
    }

    if (empty($receiptNo)) {
        $receiptNo = 'OR-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    try {
        $pdo->beginTransaction();

        // 1. Locate or create Payment
        if (!$paymentId) {
            $stmtFind = $pdo->prepare("SELECT PaymentID FROM payment WHERE StudentID = ? AND PaymentStatus = 'Pending' ORDER BY PaymentID DESC LIMIT 1");
            $stmtFind->execute([$studentId]);
            $paymentId = $stmtFind->fetchColumn();
        }

        if ($paymentId) {
            $stmtPay = $pdo->prepare("UPDATE payment SET Amount = ?, ReceiptNo = ?, PaymentDate = CURDATE(), PaymentStatus = 'Paid', StaffID = ? WHERE PaymentID = ?");
            $stmtPay->execute([$amount, $receiptNo, $staffId, $paymentId]);
        } else {
            $stmtPay = $pdo->prepare("INSERT INTO payment (Amount, ReceiptNo, PaymentDate, PaymentStatus, StudentID, StaffID) VALUES (?, ?, CURDATE(), 'Paid', ?, ?)");
            $stmtPay->execute([$amount, $receiptNo, $studentId, $staffId]);
            $paymentId = $pdo->lastInsertId();
        }

        // 2. Link payment to enrollment if not linked
        if (!$enrollmentId) {
            $stmtEnc = $pdo->prepare("SELECT EnrollmentID FROM enrollment WHERE StudentID = ? ORDER BY EnrollmentID DESC LIMIT 1");
            $stmtEnc->execute([$studentId]);
            $enrollmentId = $stmtEnc->fetchColumn();
        }

        if ($enrollmentId) {
            $stmtLink = $pdo->prepare("UPDATE enrollment SET PaymentID = ? WHERE EnrollmentID = ?");
            $stmtLink->execute([$paymentId, $enrollmentId]);
        }

        // 3. Check clearances
        $stmtCheck = $pdo->prepare("
            SELECT 
                (SELECT Status FROM clinic WHERE StudentID = ? ORDER BY ClinicID DESC LIMIT 1) as ClinicStatus,
                (SELECT Status FROM clearance WHERE StudentID = ? ORDER BY ClearanceID DESC LIMIT 1) as ClearanceStatus
        ");
        $stmtCheck->execute([$studentId, $studentId]);
        $check = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $clinicCleared = (($check['ClinicStatus'] ?? '') === 'Completed');
        $clearanceCleared = (($check['ClearanceStatus'] ?? '') === 'Cleared');

        if ($clinicCleared && $clearanceCleared && $enrollmentId) {
            $stmtEnroll = $pdo->prepare("UPDATE enrollment SET Status = 'Enrolled' WHERE EnrollmentID = ?");
            $stmtEnroll->execute([$enrollmentId]);
        }

        $pdo->commit();

        require_once '../config/logger.php';
        log_activity($pdo, 'Process Payment (POS)', 'Payment Processing', "Issued OR $receiptNo for StudentID $studentId. Amount: ₱$amount");

        header("Location: ../views/accounting_pos.php?msg=" . urlencode("Payment processed successfully! Receipt: $receiptNo") . "&or=" . urlencode($receiptNo) . "&amount=" . urlencode($amount));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/accounting_pos.php?error=" . urlencode("Database action failed: " . $e->getMessage()));
        exit;
    }
}
?>
