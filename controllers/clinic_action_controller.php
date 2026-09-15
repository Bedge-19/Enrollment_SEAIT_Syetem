<?php
// controllers/clinic_action_controller.php
session_start();
require_once '../config/db.php';
require_once '../config/logger.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Clinic', 'Admin'])) {
    header("Location: ../views/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: ../views/clinic_dashboard.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $action = $_POST['action'] ?? '';
    $staffId = $_SESSION['StaffID'] ?? 1;

    try {
        switch ($action) {
            // 1. Record Consultation & Physical Exam
            case 'record_consultation':
                $studentId = (int)($_POST['student_id'] ?? 0);
                $bp = trim($_POST['blood_pressure'] ?? '120/80');
                $pulse = trim($_POST['pulse_rate'] ?? '75 bpm');
                $weight = trim($_POST['weight'] ?? 'N/A');
                $height = trim($_POST['height'] ?? 'N/A');
                $findings = trim($_POST['findings'] ?? 'Normal / No findings');
                $clinicStatus = $_POST['status'] ?? 'Completed';
                $remarks = "BP: $bp | Pulse: $pulse | Ht: $height | Wt: $weight | Findings: $findings";

                if (!$studentId) throw new Exception("Student selection is required.");

                // Check if existing record
                $stmtCheck = $pdo->prepare("SELECT ClinicID FROM clinic WHERE StudentID = ? ORDER BY ClinicID DESC LIMIT 1");
                $stmtCheck->execute([$studentId]);
                $clinicId = $stmtCheck->fetchColumn();

                if ($clinicId) {
                    $stmt = $pdo->prepare("UPDATE clinic SET CompletionDate = CURDATE(), Status = ?, Remarks = ?, StaffID = ? WHERE ClinicID = ?");
                    $stmt->execute([$clinicStatus, $remarks, $staffId, $clinicId]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO clinic (CompletionDate, Status, Remarks, StudentID, StaffID) VALUES (CURDATE(), ?, ?, ?, ?)");
                    $stmt->execute([$clinicStatus, $remarks, $studentId, $staffId]);
                }

                // If checkup completed and physically fit, also auto-update/create clearance record
                if ($clinicStatus === 'Completed') {
                    $stmtClrCheck = $pdo->prepare("SELECT ClearanceID FROM clearance WHERE StudentID = ? ORDER BY ClearanceID DESC LIMIT 1");
                    $stmtClrCheck->execute([$studentId]);
                    $clrId = $stmtClrCheck->fetchColumn();

                    if ($clrId) {
                        $stmtClr = $pdo->prepare("UPDATE clearance SET ClearanceDate = CURDATE(), Status = 'Cleared', Remarks = 'Medical checkup passed' WHERE ClearanceID = ?");
                        $stmtClr->execute([$clrId]);
                    } else {
                        $stmtClr = $pdo->prepare("INSERT INTO clearance (ClearanceDate, Status, Remarks, StudentID) VALUES (CURDATE(), 'Cleared', 'Medical checkup passed', ?)");
                        $stmtClr->execute([$studentId]);
                    }
                }

                log_activity($pdo, 'Record Consultation', 'Medical Consultations', "Recorded health checkup for StudentID: $studentId, Status: $clinicStatus");
                header("Location: ../views/clinic_consultations.php?msg=" . urlencode("Medical consultation and vitals saved successfully."));
                exit;

            // 2. Process Health Clearance Queue
            case 'process_clearance':
                $studentId = (int)($_POST['student_id'] ?? 0);
                $clearanceStatus = $_POST['clearance_status'] ?? 'Cleared';
                $remarks = trim($_POST['remarks'] ?? 'Cleared for enrollment');

                if (!$studentId) throw new Exception("Student ID required.");

                $stmt = $pdo->prepare("
                    INSERT INTO clearance (ClearanceDate, Status, Remarks, StudentID) 
                    VALUES (CURDATE(), ?, ?, ?)
                    ON DUPLICATE KEY UPDATE Status = VALUES(Status), Remarks = VALUES(Remarks), ClearanceDate = CURDATE()
                ");
                // Wait, clearance doesn't have unique key on StudentID, so do standard select or update
                $stmtCheck = $pdo->prepare("SELECT ClearanceID FROM clearance WHERE StudentID = ? ORDER BY ClearanceID DESC LIMIT 1");
                $stmtCheck->execute([$studentId]);
                $cId = $stmtCheck->fetchColumn();

                if ($cId) {
                    $stmtUp = $pdo->prepare("UPDATE clearance SET Status = ?, Remarks = ?, ClearanceDate = CURDATE() WHERE ClearanceID = ?");
                    $stmtUp->execute([$clearanceStatus, $remarks, $cId]);
                } else {
                    $stmtIn = $pdo->prepare("INSERT INTO clearance (ClearanceDate, Status, Remarks, StudentID) VALUES (CURDATE(), ?, ?, ?)");
                    $stmtIn->execute([$clearanceStatus, $remarks, $studentId]);
                }

                // Also update clinic table status if relevant
                $stmtCln = $pdo->prepare("SELECT ClinicID FROM clinic WHERE StudentID = ? ORDER BY ClinicID DESC LIMIT 1");
                $stmtCln->execute([$studentId]);
                $clnId = $stmtCln->fetchColumn();
                if ($clnId) {
                    $clinicStat = ($clearanceStatus === 'Cleared') ? 'Completed' : 'Failed';
                    $stmtClnUp = $pdo->prepare("UPDATE clinic SET Status = ?, CompletionDate = CURDATE(), StaffID = ? WHERE ClinicID = ?");
                    $stmtClnUp->execute([$clinicStat, $staffId, $clnId]);
                }

                log_activity($pdo, 'Update Clearance', 'Health Clearance Queue', "StudentID $studentId marked as $clearanceStatus");
                header("Location: ../views/clearance.php?msg=" . urlencode("Clearance status updated to $clearanceStatus."));
                exit;

            default:
                header("Location: ../views/clinic_dashboard.php?error=" . urlencode("Invalid action."));
                exit;
        }
    } catch (Exception $e) {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../views/clinic_dashboard.php') . "?error=" . urlencode($e->getMessage()));
        exit;
    }
}
