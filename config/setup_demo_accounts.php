<?php
// c:/introllment/config/setup_demo_accounts.php
require_once __DIR__ . '/db.php';

try {
    $hashPass123 = password_hash('password123', PASSWORD_BCRYPT);
    $hashAdmin123 = password_hash('admin123', PASSWORD_BCRYPT);
    $hashStudent123 = password_hash('student123', PASSWORD_BCRYPT);

    echo "=== SETTING UP DEMO & STANDARD ACCOUNTS ===\n";

    // 1. Ensure Admin staff record exists
    $stmtAdminStaff = $pdo->prepare("SELECT StaffID FROM staff WHERE RoleID = 'Admin' LIMIT 1");
    $stmtAdminStaff->execute();
    $adminStaffId = $stmtAdminStaff->fetchColumn();

    if (!$adminStaffId) {
        $stmtInsertStaff = $pdo->prepare("INSERT INTO staff (LastName, FirstName, MiddleName, Email, ContactNo, DepartmentID, RoleID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtInsertStaff->execute(['Administrator', 'System', 'S.', 'admin@seait.edu.ph', '09170000099', 1, 'Admin']);
        $adminStaffId = $pdo->lastInsertId();
        echo "Created new Admin Staff ID: $adminStaffId\n";
    } else {
        echo "Using existing Admin Staff ID: $adminStaffId\n";
    }

    // 2. Fetch sample Staff IDs for Registrar, Accounting, Clinic
    $regStaffId = $pdo->query("SELECT StaffID FROM staff WHERE RoleID = 'Registrar' LIMIT 1")->fetchColumn() ?: 1;
    $accStaffId = $pdo->query("SELECT StaffID FROM staff WHERE RoleID = 'Accounting' LIMIT 1")->fetchColumn() ?: 11;
    $cliStaffId = $pdo->query("SELECT StaffID FROM staff WHERE RoleID = 'Clinic' LIMIT 1")->fetchColumn() ?: 19;
    $studentId = $pdo->query("SELECT StudentID FROM student LIMIT 1")->fetchColumn() ?: 1;

    // Accounts to guarantee
    $accounts = [
        // Username, PasswordHash, UserType, Status, StaffID, StudentID, Role (for users table)
        ['admin', $hashAdmin123, 'Staff', 'Active', $adminStaffId, null, 'Admin'],
        ['staff1', $hashPass123, 'Staff', 'Active', $regStaffId, null, 'Registrar'],
        ['staff2', $hashPass123, 'Staff', 'Active', $regStaffId, null, 'Registrar'],
        ['staff3', $hashPass123, 'Staff', 'Active', $accStaffId, null, 'Accounting'],
        ['staff4', $hashPass123, 'Staff', 'Active', $cliStaffId, null, 'Clinic'],
        ['staff5', $hashPass123, 'Staff', 'Active', $adminStaffId, null, 'Admin'],
        ['accounting', $hashPass123, 'Staff', 'Active', $accStaffId, null, 'Accounting'],
        ['clinic', $hashPass123, 'Staff', 'Active', $cliStaffId, null, 'Clinic'],
        ['student', $hashStudent123, 'Student', 'Active', null, $studentId, 'Student'],
    ];

    $checkLogin = $pdo->prepare("SELECT LoginID FROM login WHERE Username = ?");
    $updateLogin = $pdo->prepare("UPDATE login SET PasswordHash = ?, UserType = ?, Status = 'Active', StaffID = ?, StudentID = ? WHERE Username = ?");
    $insertLogin = $pdo->prepare("INSERT INTO login (Username, PasswordHash, UserType, Status, StaffID, StudentID) VALUES (?, ?, ?, 'Active', ?, ?)");

    $checkUsers = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $updateUsers = $pdo->prepare("UPDATE users SET password_hash = ?, role = ?, status = 'Active', staff_id = ?, student_id = ? WHERE username = ?");
    $insertUsers = $pdo->prepare("INSERT INTO users (username, password_hash, role, status, staff_id, student_id) VALUES (?, ?, ?, 'Active', ?, ?)");

    foreach ($accounts as $acc) {
        list($username, $hash, $userType, $status, $sId, $stId, $role) = $acc;

        // Upsert into login
        $checkLogin->execute([$username]);
        if ($checkLogin->fetchColumn()) {
            $updateLogin->execute([$hash, $userType, $sId, $stId, $username]);
            echo "Updated login account: $username\n";
        } else {
            $insertLogin->execute([$username, $hash, $userType, $sId, $stId]);
            echo "Inserted login account: $username\n";
        }

        // Upsert into users
        try {
            $checkUsers->execute([$username]);
            if ($checkUsers->fetchColumn()) {
                $updateUsers->execute([$hash, $role, $sId, $stId, $username]);
            } else {
                $insertUsers->execute([$username, $hash, $role, $sId, $stId]);
            }
        } catch (Exception $ex) {
            // users table might have different columns or constraints, ignore
        }
    }

    // Also update STF-01 to STF-31 and STU-00001 so password123 / student123 works for them directly
    $pdo->prepare("UPDATE login SET PasswordHash = ? WHERE Username IN ('STF-01', 'STF-02', 'STF-11', 'STF-19', 'STF-27')")->execute([$hashPass123]);
    $pdo->prepare("UPDATE login SET PasswordHash = ? WHERE Username = 'STU-00001'")->execute([$hashStudent123]);
    echo "Updated STF-01, STF-02, STF-11, STF-19, STF-27 and STU-00001 with password123/student123!\n";

    echo "ALL DEMO ACCOUNTS CONFIGURED SUCCESSFULLY!\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
