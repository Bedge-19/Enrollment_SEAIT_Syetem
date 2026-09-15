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

    $admissionId = $_POST['AdmissionID'] ?? 0;
    $action = $_POST['action'] ?? '';
    $remarks = $_POST['Remarks'] ?? '';
    $staffId = $_SESSION['StaffID'] ?? 1; // Default to 1 if not fully populated

    if (!$admissionId) {
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Invalid admission ID."));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $status = ($action === 'Approve') ? 'Approved' : 'Rejected';

        // Update admission status
        $stmt = $pdo->prepare("UPDATE admission SET Status = ?, ApprovalDate = CURDATE(), Remarks = ?, StaffID = ? WHERE AdmissionID = ?");
        $stmt->execute([$status, $remarks, $staffId, $admissionId]);

        // If approved, maybe create a user account? Wait, the FDD says staff creates it later, or maybe we just leave it for now.
        // Actually, if it's approved, let's just create a login so the student can access the portal.
        if ($status === 'Approved') {
            // Get student info
            $stmt2 = $pdo->prepare("SELECT s.StudentID, s.StudentNo, s.LastName FROM admission a JOIN student s ON a.StudentID = s.StudentID WHERE a.AdmissionID = ?");
            $stmt2->execute([$admissionId]);
            $student = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $username = $student['StudentNo'];
                // Default password is their last name lowercase + last 4 digits of Student No
                $lastName = strtolower(str_replace(' ', '', $student['LastName']));
                $last4 = substr($student['StudentNo'], -4);
                $password = $lastName . $last4;
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Check if login already exists
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM login WHERE StudentID = ?");
                $stmtCheck->execute([$student['StudentID']]);
                $exists = $stmtCheck->fetchColumn();

                if (!$exists) {
                    $stmt3 = $pdo->prepare("INSERT INTO login (Username, PasswordHash, UserType, Status, StudentID, StaffID, MustChangePassword) VALUES (?, ?, 'Student', 'Inactive', ?, ?, 1)");
                    $stmt3->execute([$username, $passwordHash, $student['StudentID'], $staffId]);

                    // Create pending ID validation record
                    $stmt4 = $pdo->prepare("INSERT INTO id_validation (ValidationDate, Status, StudentID, StaffID) VALUES (CURDATE(), 'Pending', ?, ?)");
                    $stmt4->execute([$student['StudentID'], $staffId]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../views/staff_dashboard.php?msg=Admission+$status");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/staff_dashboard.php?error=" . urlencode("Database action failed. Please try again."));
        exit;
    }
}
?>
