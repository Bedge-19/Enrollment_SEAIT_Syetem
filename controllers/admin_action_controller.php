<?php
// controllers/admin_action_controller.php
session_start();
require_once '../config/db.php';
require_once '../config/logger.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: ../views/login.php?error=" . urlencode("Unauthorized access."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: ../views/admin_dashboard.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            // 1. Toggle User Account Status
            case 'toggle_user_status':
                $loginId = (int)($_POST['login_id'] ?? 0);
                $newStatus = ($_POST['status'] ?? '') === 'Active' ? 'Active' : 'Inactive';
                $stmt = $pdo->prepare("UPDATE login SET Status = ? WHERE LoginID = ?");
                $stmt->execute([$newStatus, $loginId]);
                log_activity($pdo, 'Toggle User Status', 'User Login Management', "Set LoginID $loginId status to $newStatus");
                header("Location: ../views/admin_users.php?msg=" . urlencode("User status updated to $newStatus"));
                exit;

            // 2. Reset User Password
            case 'reset_password':
                $loginId = (int)($_POST['login_id'] ?? 0);
                $newPassword = $_POST['new_password'] ?? 'P@ssword123';
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $mustChange = isset($_POST['must_change']) ? 1 : 0;
                $stmt = $pdo->prepare("UPDATE login SET PasswordHash = ?, MustChangePassword = ? WHERE LoginID = ?");
                $stmt->execute([$passwordHash, $mustChange, $loginId]);
                log_activity($pdo, 'Reset Password', 'User Login Management', "Reset password for LoginID $loginId");
                header("Location: ../views/admin_users.php?msg=" . urlencode("Password reset successfully."));
                exit;

            // 3. Add New Staff & Provision Login
            case 'add_staff':
                $firstName = trim($_POST['first_name'] ?? '');
                $lastName = trim($_POST['last_name'] ?? '');
                $middleName = trim($_POST['middle_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $contactNo = trim($_POST['contact_no'] ?? '');
                $departmentId = (int)($_POST['department_id'] ?? 1);
                $roleId = $_POST['role_id'] ?? 'Registrar';
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? 'password123';

                if (empty($firstName) || empty($lastName) || empty($email) || empty($username)) {
                    throw new Exception("Please provide required staff details.");
                }

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO staff (FirstName, LastName, MiddleName, Email, ContactNo, DepartmentID, RoleID) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$firstName, $lastName, $middleName, $email, $contactNo, $departmentId, $roleId]);
                $staffId = $pdo->lastInsertId();

                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $stmtLogin = $pdo->prepare("INSERT INTO login (Username, PasswordHash, UserType, Status, StaffID) VALUES (?, ?, 'Staff', 'Active', ?)");
                $stmtLogin->execute([$username, $passHash, $staffId]);

                $pdo->commit();
                log_activity($pdo, 'Create Staff Account', 'Staff & Roles Config', "Created $roleId staff: $firstName $lastName (User: $username)");
                header("Location: ../views/admin_staff.php?msg=" . urlencode("New staff member $firstName $lastName added successfully."));
                exit;

            // 4. Update Staff Details / Role
            case 'update_staff':
                $staffId = (int)($_POST['staff_id'] ?? 0);
                $departmentId = (int)($_POST['department_id'] ?? 1);
                $roleId = $_POST['role_id'] ?? 'Registrar';
                $email = trim($_POST['email'] ?? '');
                $contactNo = trim($_POST['contact_no'] ?? '');

                $stmt = $pdo->prepare("UPDATE staff SET DepartmentID = ?, RoleID = ?, Email = ?, ContactNo = ? WHERE StaffID = ?");
                $stmt->execute([$departmentId, $roleId, $email, $contactNo, $staffId]);
                log_activity($pdo, 'Update Staff Role', 'Staff & Roles Config', "Updated StaffID $staffId to Role $roleId");
                header("Location: ../views/admin_staff.php?msg=" . urlencode("Staff configuration updated."));
                exit;

            // 5. Add Department
            case 'add_department':
                $deptName = trim($_POST['department_name'] ?? '');
                if (empty($deptName)) throw new Exception("Department name is required.");
                $stmt = $pdo->prepare("INSERT INTO department (DepartmentName) VALUES (?)");
                $stmt->execute([$deptName]);
                log_activity($pdo, 'Add Department', 'Department Management', "Created department: $deptName");
                header("Location: ../views/admin_departments.php?msg=" . urlencode("Department added successfully."));
                exit;

            // 6. Delete Department
            case 'delete_department':
                $deptId = (int)($_POST['department_id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM department WHERE departmentID = ?");
                $stmt->execute([$deptId]);
                log_activity($pdo, 'Delete Department', 'Department Management', "Deleted DepartmentID: $deptId");
                header("Location: ../views/admin_departments.php?msg=" . urlencode("Department removed."));
                exit;

            // 7. Add Program
            case 'add_program':
                $progName = trim($_POST['program_name'] ?? '');
                $deptId = (int)($_POST['department_id'] ?? 0);
                if (empty($progName) || !$deptId) throw new Exception("Program name and department are required.");
                $stmt = $pdo->prepare("INSERT INTO program (ProgramName, DepartmentID) VALUES (?, ?)");
                $stmt->execute([$progName, $deptId]);
                log_activity($pdo, 'Add Program', 'Program Master Data', "Created program: $progName");
                header("Location: ../views/admin_programs.php?msg=" . urlencode("Program added successfully."));
                exit;

            // 8. Delete Program
            case 'delete_program':
                $progId = (int)($_POST['program_id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM program WHERE ProgramID = ?");
                $stmt->execute([$progId]);
                log_activity($pdo, 'Delete Program', 'Program Master Data', "Deleted ProgramID: $progId");
                header("Location: ../views/admin_programs.php?msg=" . urlencode("Program removed."));
                exit;

            // 9. Add Student Type
            case 'add_student_type':
                $typeName = trim($_POST['type_name'] ?? '');
                if (empty($typeName)) throw new Exception("Student type name is required.");
                $stmt = $pdo->prepare("INSERT INTO student_type (TypeName) VALUES (?)");
                $stmt->execute([$typeName]);
                log_activity($pdo, 'Add Student Type', 'Student Types Master Data', "Created type: $typeName");
                header("Location: ../views/admin_student_types.php?msg=" . urlencode("Student type created."));
                exit;

            // 10. Delete Student Type
            case 'delete_student_type':
                $typeId = (int)($_POST['student_type_id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM student_type WHERE StudentTypeID = ?");
                $stmt->execute([$typeId]);
                log_activity($pdo, 'Delete Student Type', 'Student Types Master Data', "Deleted StudentTypeID: $typeId");
                header("Location: ../views/admin_student_types.php?msg=" . urlencode("Student type deleted."));
                exit;

            default:
                header("Location: ../views/admin_dashboard.php?error=" . urlencode("Invalid operation."));
                exit;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../views/admin_dashboard.php') . "?error=" . urlencode($e->getMessage()));
        exit;
    }
}
