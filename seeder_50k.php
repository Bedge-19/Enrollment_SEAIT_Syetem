<?php
require 'config/db.php';

// Adjust memory limit and max execution time for 50k inserts
ini_set('memory_limit', '512M');
set_time_limit(0);

echo "Starting seeding...\n";

// Seed 15 Staff
echo "Seeding 15 staff...\n";
$roles = ['Registrar', 'Dean', 'Accounting', 'Clinic', 'Admin'];
$staffPassword = password_hash('password123', PASSWORD_DEFAULT);

$pdo->beginTransaction();
for ($i = 1; $i <= 15; $i++) {
    $role = $roles[array_rand($roles)];
    $stmt = $pdo->prepare("INSERT INTO staff (LastName, FirstName, MiddleName, Email, ContactNo, DepartmentID, RoleID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        "StaffLast$i", 
        "StaffFirst$i", 
        "M", 
        "staff$i@example.com", 
        "0999000000$i", 
        1, 
        $role
    ]);
    $staffId = $pdo->lastInsertId();

    $stmtLogin = $pdo->prepare("INSERT INTO login (Username, PasswordHash, UserType, Status, LastLogin, StaffID) VALUES (?, ?, 'Staff', 'Active', NOW(), ?)");
    $stmtLogin->execute(["newstaff$i", $staffPassword, $staffId]);
}
$pdo->commit();
echo "15 staff seeded.\n";

// Seed 50,000 Students
echo "Seeding 50,000 students...\n";

$totalStudents = 50000;
$batchSize = 1000;
$batches = ceil($totalStudents / $batchSize);

$programs = [1, 2, 3]; // Assuming some valid program IDs exist
$sexes = ['Male', 'Female'];
$types = [1, 2];

for ($b = 0; $b < $batches; $b++) {
    $pdo->beginTransaction();

    $studentValues = [];
    $profileValues = [];
    $studentParams = [];
    $profileParams = [];
    
    // For inserting student records
    for ($i = 0; $i < $batchSize; $i++) {
        $idx = ($b * $batchSize) + $i + 1;
        $studentNo = "ST-" . date('Y') . "-" . str_pad($idx, 6, "0", STR_PAD_LEFT);
        
        $lastName = "StudentLast$idx";
        $firstName = "StudentFirst$idx";
        $middleName = "M$idx";
        $sex = $sexes[array_rand($sexes)];
        $programId = $programs[array_rand($programs)];
        $typeId = $types[array_rand($types)];
        
        // Student query parts
        $studentValues[] = "(?, ?, ?, ?, '2005-01-01', ?, 'Address $idx', '09990000000', 'student$idx@example.com', ?, ?)";
        array_push($studentParams, $studentNo, $lastName, $firstName, $middleName, $sex, $typeId, $programId);
    }

    // Insert batch of students
    $sqlStudent = "INSERT INTO student (StudentNo, LastName, FirstName, MiddleName, BirthDate, Sex, Address, ContactNo, Email, StudentTypeID, ProgramID) VALUES " . implode(", ", $studentValues);
    $stmtStudent = $pdo->prepare($sqlStudent);
    $stmtStudent->execute($studentParams);
    
    // We need to get the last inserted IDs for student_profile. 
    // In MySQL, lastInsertId() returns the ID of the FIRST row inserted in a bulk insert.
    $firstInsertId = $pdo->lastInsertId();
    
    for ($i = 0; $i < $batchSize; $i++) {
        $studentId = $firstInsertId + $i;
        $idx = ($b * $batchSize) + $i + 1;
        
        $profileValues[] = "('Profile Address $idx', '09990000000', 'Guardian $idx', '09991110000', 'Prev School', 'Prev Prog', 'Grade 10', 90.00, ?)";
        $profileParams[] = $studentId;
    }
    
    $sqlProfile = "INSERT INTO student_profile (Address, ContactNo, GuardianName, GuardianContactNo, PreviousSchoolName, PreviousProgram, LastYearLevelCompleted, GWA, StudentID) VALUES " . implode(", ", $profileValues);
    $stmtProfile = $pdo->prepare($sqlProfile);
    $stmtProfile->execute($profileParams);

    $pdo->commit();
    echo "Seeded batch " . ($b + 1) . " of $batches (Total: " . (($b + 1) * $batchSize) . ")\n";
}

echo "Seeding completed successfully.\n";
