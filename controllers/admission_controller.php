<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['FirstName'] ?? '';
    $middleName = $_POST['MiddleName'] ?? '';
    $lastName = $_POST['LastName'] ?? '';
    $birthDate = $_POST['BirthDate'] ?? '';
    $sex = $_POST['Sex'] ?? '';
    $address = $_POST['Address'] ?? '';
    $contactNo = $_POST['ContactNo'] ?? '';
    $email = $_POST['Email'] ?? '';
    
    $previousSchoolName = $_POST['PreviousSchoolName'] ?? '';
    $previousProgram = $_POST['PreviousProgram'] ?? '';
    $lastYearLevelCompleted = $_POST['LastYearLevelCompleted'] ?? '';
    $gwa = $_POST['GWA'] ?? 0;
    
    $studentTypeID = $_POST['StudentTypeID'] ?? '';
    $programID = $_POST['ProgramID'] ?? '';
    
    $guardianName = trim($_POST['GuardianName'] ?? '');
    $guardianContactNo = trim($_POST['GuardianContactNo'] ?? '');

    // CSRF check
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: ../views/apply.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    // Server-side validations
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../views/apply.php?error=" . urlencode("Invalid email format."));
        exit;
    }
    if (!preg_match('/^[0-9]+$/', $contactNo) || !preg_match('/^[0-9]+$/', $guardianContactNo)) {
        header("Location: ../views/apply.php?error=" . urlencode("Contact numbers must contain only digits."));
        exit;
    }
    if (!is_numeric($gwa) || $gwa < 0 || $gwa > 100) {
        header("Location: ../views/apply.php?error=" . urlencode("Invalid GWA. Must be a number between 0 and 100."));
        exit;
    }

    // Generate a temporary student number with collision check
    do {
        $studentNo = 'APP-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM student WHERE StudentNo = ?");
        $stmtCheck->execute([$studentNo]);
        $exists = $stmtCheck->fetchColumn();
    } while ($exists > 0);

    try {
        $pdo->beginTransaction();

        // 1. Insert into student
        $stmt = $pdo->prepare("INSERT INTO student (StudentNo, LastName, FirstName, MiddleName, BirthDate, Sex, Address, ContactNo, Email, StudentTypeID, ProgramID) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$studentNo, $lastName, $firstName, $middleName, $birthDate, $sex, $address, $contactNo, $email, $studentTypeID, $programID]);
        
        $studentId = $pdo->lastInsertId();

        // 2. Insert into student_profile
        $stmt2 = $pdo->prepare("INSERT INTO student_profile (Address, ContactNo, GuardianName, GuardianContactNo, PreviousSchoolName, PreviousProgram, LastYearLevelCompleted, GWA, StudentID) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->execute([$address, $contactNo, $guardianName, $guardianContactNo, $previousSchoolName, $previousProgram, $lastYearLevelCompleted, $gwa, $studentId]);

        // 3. Insert into admission
        $stmt3 = $pdo->prepare("INSERT INTO admission (SubmissionDate, ApprovalDate, Status, Remarks, StudentID, StaffID) 
                                VALUES (CURDATE(), '1000-01-01', 'Pending', 'New Online Application', ?, 0)");
        $stmt3->execute([$studentId]);

        $pdo->commit();

        // Redirect to success page
        header("Location: ../views/apply_success.php?ref=" . urlencode($studentNo));
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/apply.php?error=" . urlencode("Application failed due to a system error. Please try again later."));
        exit;
    }
}
?>
