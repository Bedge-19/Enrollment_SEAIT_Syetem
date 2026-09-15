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

    $evaluationId = $_POST['EvaluationID'] ?? 0;
    $studentId = $_POST['StudentID'] ?? 0;
    $action = $_POST['action'] ?? '';
    $remarks = $_POST['Remarks'] ?? '';
    $subjects = $_POST['subjects'] ?? [];
    $staffId = $_SESSION['StaffID'] ?? 1;

    if (!$evaluationId || !$studentId) {
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Invalid parameters."));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $status = ($action === 'Approve') ? 'Approved' : 'Rejected';

        // Update evaluation status
        $stmt = $pdo->prepare("UPDATE evaluation SET Status = ?, EvaluationDate = CURDATE(), Remarks = ?, StaffID = ? WHERE EvaluationID = ?");
        $stmt->execute([$status, $remarks, $staffId, $evaluationId]);

        if ($status === 'Approved') {
            // Process subjects
            $stmtEvalSub = $pdo->prepare("INSERT INTO evaluation_subject (SubjectStatus, EvaluationID, SubjectID) VALUES (?, ?, ?)");
            $stmtCredited = $pdo->prepare("INSERT INTO credited_subject (Status, Remarks, StudentID, SubjectID) VALUES ('Approved', 'Credited upon evaluation', ?, ?)");

            $hasApprovedSubjects = false;

            foreach ($subjects as $subjectId => $subjectStatus) {
                if ($subjectStatus === 'Approved' || $subjectStatus === 'Credited') {
                    $stmtEvalSub->execute([$subjectStatus, $evaluationId, $subjectId]);
                    $hasApprovedSubjects = true;

                    if ($subjectStatus === 'Credited') {
                        $stmtCredited->execute([$studentId, $subjectId]);
                    }
                }
            }

            // Create Pending Enrollment record if they have approved subjects
            if ($hasApprovedSubjects) {
                // Determine current semester/school year (dummy for now)
                $schoolYear = '2024-2025';
                $semester = '1st';
                
                // Note: PaymentID isn't known yet, might need to insert a dummy payment record or allow PaymentID to be nullable.
                // Looking at schema: PaymentID int(11) NOT NULL. Let's create a Pending payment first?
                // `payment` schema: PaymentID, Amount, PaymentDate, ReceiptNo, PaymentStatus, StudentID, StaffID
                $stmtPayment = $pdo->prepare("INSERT INTO payment (Amount, PaymentDate, ReceiptNo, PaymentStatus, StudentID, StaffID) VALUES (0, CURDATE(), '', 'Pending', ?, ?)");
                $stmtPayment->execute([$studentId, $staffId]);
                $paymentId = $pdo->lastInsertId();

                $stmtEnroll = $pdo->prepare("INSERT INTO enrollment (EnrollmentDate, SchoolYear, Semester, Status, StudentID, StaffID, EvaluationID, PaymentID) VALUES (CURDATE(), ?, ?, 'Pending', ?, ?, ?, ?)");
                $stmtEnroll->execute([$schoolYear, $semester, $studentId, $staffId, $evaluationId, $paymentId]);
            }
        }

        $pdo->commit();
        header("Location: ../views/staff_dashboard.php?msg=Evaluation+$status");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Database action failed. Please try again."));
        exit;
    }
}
?>
