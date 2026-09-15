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

    $enrollmentId = $_POST['EnrollmentID'] ?? 0;
    $sectionId = $_POST['SectionID'] ?? 0;
    $subjects = $_POST['subjects'] ?? [];

    if (!$enrollmentId || !$sectionId) {
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Invalid parameters. Make sure a section is selected."));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert into blocking
        $stmtBlock = $pdo->prepare("INSERT INTO blocking (BlockingDate, EnrollmentID, SectionID) VALUES (CURDATE(), ?, ?)");
        $stmtBlock->execute([$enrollmentId, $sectionId]);

        // 2. Insert into enrollment_subject
        if (!empty($subjects)) {
            $stmtSub = $pdo->prepare("INSERT INTO enrollment_subject (EnrollmentID, SubjectID, SectionID) VALUES (?, ?, ?)");
            foreach ($subjects as $subjectId) {
                $stmtSub->execute([$enrollmentId, $subjectId, $sectionId]);
            }
        }

        // Generate clinic and clearance records for the next stage (if they don't exist)
        // We need the studentID from enrollment
        $stmtEnc = $pdo->prepare("SELECT StudentID FROM enrollment WHERE EnrollmentID = ?");
        $stmtEnc->execute([$enrollmentId]);
        $studentId = $stmtEnc->fetchColumn();

        if ($studentId) {
            $staffId = $_SESSION['StaffID'] ?? 1;

            $stmtCheckClearance = $pdo->prepare("SELECT COUNT(*) FROM clearance WHERE StudentID = ?");
            $stmtCheckClearance->execute([$studentId]);
            if ($stmtCheckClearance->fetchColumn() == 0) {
                $stmtClearance = $pdo->prepare("INSERT INTO clearance (ClearanceDate, Status, StudentID) VALUES (CURDATE(), 'Pending', ?)");
                $stmtClearance->execute([$studentId]);
            }

            $stmtCheckClinic = $pdo->prepare("SELECT COUNT(*) FROM clinic WHERE StudentID = ?");
            $stmtCheckClinic->execute([$studentId]);
            if ($stmtCheckClinic->fetchColumn() == 0) {
                $stmtClinic = $pdo->prepare("INSERT INTO clinic (CompletionDate, Status, StudentID, StaffID) VALUES (CURDATE(), 'Pending', ?, ?)");
                $stmtClinic->execute([$studentId, $staffId]);
            }
        }

        $pdo->commit();
        header("Location: ../views/staff_dashboard.php?msg=Sectioning+Completed");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Database action failed. Please try again."));
        exit;
    }
}
?>
