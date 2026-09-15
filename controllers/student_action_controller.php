<?php
// controllers/student_action_controller.php
session_start();
require_once '../config/db.php';
require_once '../config/logger.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: ../views/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

$studentId = $_SESSION['StudentID'] ?? null;
if (!$studentId) {
    header("Location: ../views/login.php?error=" . urlencode("Session expired."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: ../views/student_profile.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'update_profile':
                $contactNo = trim($_POST['ContactNo'] ?? '');
                $address = trim($_POST['Address'] ?? '');
                $guardianName = trim($_POST['GuardianName'] ?? '');
                $guardianContactNo = trim($_POST['GuardianContactNo'] ?? '');
                $email = trim($_POST['Email'] ?? '');

                // Validate contact
                if (!empty($contactNo) && !preg_match('/^[0-9]+$/', $contactNo)) {
                    throw new Exception("Contact number must contain only numbers.");
                }

                $pdo->beginTransaction();

                // 1. Update student table
                $stmt = $pdo->prepare("UPDATE student SET Address = ?, ContactNo = ?, Email = ? WHERE StudentID = ?");
                $stmt->execute([$address, $contactNo, $email, $studentId]);

                // 2. Update student_profile table
                $stmtCheck = $pdo->prepare("SELECT ProfileID FROM student_profile WHERE StudentID = ?");
                $stmtCheck->execute([$studentId]);
                $profId = $stmtCheck->fetchColumn();

                if ($profId) {
                    $stmtProf = $pdo->prepare("UPDATE student_profile SET Address = ?, ContactNo = ?, GuardianName = ?, GuardianContactNo = ? WHERE ProfileID = ?");
                    $stmtProf->execute([$address, $contactNo, $guardianName, $guardianContactNo, $profId]);
                } else {
                    $stmtProf = $pdo->prepare("INSERT INTO student_profile (Address, ContactNo, GuardianName, GuardianContactNo, PreviousSchoolName, PreviousProgram, LastYearLevelCompleted, GWA, StudentID) VALUES (?, ?, ?, ?, 'N/A', 'N/A', 'N/A', 0, ?)");
                    $stmtProf->execute([$address, $contactNo, $guardianName, $guardianContactNo, $studentId]);
                }

                $pdo->commit();
                log_activity($pdo, 'Update Profile', 'Student Profile', "StudentID $studentId updated contact/guardian information");
                header("Location: ../views/student_profile.php?msg=" . urlencode("Profile information updated successfully."));
                exit;

            default:
                header("Location: ../views/student_dashboard.php?error=" . urlencode("Invalid operation."));
                exit;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header("Location: ../views/student_profile.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}
