<?php
// c:/introllment/seed.php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'enrollment';

try {
    // 1. Connect and create database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `$dbname`");
    
    echo "<h1>Database Seeder</h1>";
    echo "<p>Database '$dbname' created/selected.</p>";

    // 2. Import schema
    $sqlFile = 'c:/introllment/enrollment_database.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Remove comments and empty lines
        $lines = explode("\n", $sql);
        $cleanSql = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strpos($line, '--') !== 0 && strpos($line, '/*') !== 0) {
                $cleanSql .= $line . "\n";
            }
        }
        
        // Execute queries
        $queries = explode(';', $cleanSql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                try {
                    $pdo->exec($query);
                } catch(PDOException $e) {
                    echo "Query Failed: " . substr($query, 0, 50) . "... Error: " . $e->getMessage() . "<br>";
                }
            }
        }
        echo "<p>Schema imported successfully.</p>";
    } else {
        die("Could not find enrollment_database.sql");
    }

    // Fix login table nullable fields so Staff don't need a StudentID and vice versa
    $pdo->exec("ALTER TABLE login MODIFY StudentID int(11) NULL, MODIFY StaffID int(11) NULL;");

    // 3. Clear existing data
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $tables = ['login', 'staff', 'student', 'student_profile', 'program', 'subject', 'section', 'admission', 'id_validation', 'evaluation', 'evaluation_subject', 'enrollment', 'payment', 'blocking', 'enrollment_subject', 'clinic', 'clearance', 'department', 'student_type'];
    foreach ($tables as $t) {
        $pdo->exec("TRUNCATE TABLE `$t`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "<p>Tables truncated.</p>";

    // 4. Seed Departments
    $departments = [
        'K–12 Basic Education',
        'College of Agriculture and Fisheries',
        'College of Business and Good Governance',
        'College of Information and Communication Technology',
        'College of Teacher Education',
        'College of Criminal Justice Education',
        'Department of Civil Engineering',
        'TESDA / Technical Vocational Programs'
    ];
    $stmtDept = $pdo->prepare("INSERT INTO department (DepartmentName) VALUES (?)");
    foreach ($departments as $d) {
        $stmtDept->execute([$d]);
    }
    echo "<p>Departments seeded.</p>";

    // Seed Student Types
    $stmtType = $pdo->prepare("INSERT INTO student_type (TypeName) VALUES (?)");
    $stmtType->execute(['New']);
    $stmtType->execute(['Transferee']);
    $stmtType->execute(['Returnee']);

    // 4. Seed Programs
    $programs = [
        // K-12 Basic Education (ID 1)
        ['Kindergarten', 1],
        ['Elementary', 1],
        ['Junior High School', 1],
        ['Senior High School - ABM', 1],
        ['Senior High School - GAS', 1],
        ['Senior High School - HUMSS', 1],
        ['Senior High School - STEM', 1],
        
        // College of Agriculture and Fisheries (ID 2)
        ['BS Agriculture - Animal Science', 2],
        ['BS Agriculture - Crop Science', 2],
        ['BS Agriculture - Horticulture', 2],
        ['BS Agriculture - Plant Breeding and Genetics', 2],
        ['BS Fisheries', 2],
        
        // College of Business and Good Governance (ID 3)
        ['Bachelor of Public Administration', 3],
        ['BS Accounting Information Systems', 3],
        ['BS Business Administration - Marketing Management', 3],
        ['BS Hospitality Management', 3],
        ['BS Social Work', 3],
        ['BS Tourism Management', 3],
        ['BS Accountancy', 3],
        
        // College of Information and Communication Technology (ID 4)
        ['BS Information Technology', 4],
        ['BS Information Technology - Business Analytics', 4],
        ['BS Computer Science', 4],
        
        // College of Teacher Education (ID 5)
        ['Bachelor of Early Childhood Education', 5],
        ['Bachelor of Elementary Education', 5],
        ['Bachelor of Secondary Education - English', 5],
        ['Bachelor of Secondary Education - Filipino', 5],
        ['Bachelor of Secondary Education - Mathematics', 5],
        ['Bachelor of Secondary Education - Science', 5],
        ['Bachelor of Secondary Education - Social Studies', 5],
        ['Bachelor of Technology and Livelihood Education - ICT', 5],
        
        // College of Criminal Justice Education (ID 6)
        ['BS Criminology', 6],
        
        // Department of Civil Engineering (ID 7)
        ['BS Civil Engineering - Structural Engineering', 7],
        
        // TESDA / Technical Vocational Programs (ID 8)
        ['2-Year Diploma in Computer Programming NC IV', 8],
        ['Computer Hardware Servicing NC II', 8],
        ['Cookery NC II', 8],
        ['Driving NC II', 8],
        ['Electrical Installation and Maintenance NC II', 8],
        ['Heavy Equipment Operation - Hydraulic Excavator NC II', 8],
        ['Heavy Equipment Operation - Wheel Loader NC II', 8],
        ['Shielded Metal Arc Welding NC II', 8]
    ];
    $stmtProg = $pdo->prepare("INSERT INTO program (ProgramName, DepartmentID) VALUES (?, ?)");
    foreach ($programs as $p) {
        $stmtProg->execute($p);
    }
    echo "<p>Programs seeded.</p>";

    // 5. Seed Subjects
    $stmtSub = $pdo->prepare("INSERT INTO subject (SubjectCode, SubjectTitle, Units, ProgramID) VALUES (?, ?, ?, ?)");
    $numPrograms = count($programs);
    for ($pId = 1; $pId <= $numPrograms; $pId++) {
        for ($i = 1; $i <= 5; $i++) {
            $stmtSub->execute(["SUBJ{$pId}0{$i}", "Subject $i for Program $pId", 3, $pId]);
        }
    }
    echo "<p>Subjects seeded.</p>";

    // 6. Seed Sections
    $stmtSec = $pdo->prepare("INSERT INTO section (SectionCode, YearLevel, ProgramID) VALUES (?, ?, ?)");
    for ($pId = 1; $pId <= $numPrograms; $pId++) {
        $stmtSec->execute(["SEC-{$pId}A", 1, $pId]);
    }
    echo "<p>Sections seeded.</p>";

    // 7. Seed Staff & Staff Logins
    $staffRoles = ['Registrar', 'Dean', 'Accounting', 'Clinic', 'Admin'];
    $stmtStaff = $pdo->prepare("INSERT INTO staff (FirstName, LastName, MiddleName, Email, ContactNo, DepartmentID, RoleID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtLogin = $pdo->prepare("INSERT INTO login (Username, PasswordHash, UserType, Status, StaffID) VALUES (?, ?, 'Staff', 'Active', ?)");
    
    foreach ($staffRoles as $index => $role) {
        $num = $index + 1;
        $fname = "Staff$num";
        $lname = $role;
        $email = "staff$num@seait.edu";
        $phone = "0900000000$num";
        
        $stmtStaff->execute([$fname, $lname, '', $email, $phone, 1, $role]);
        $staffId = $pdo->lastInsertId();
        
        // Password is 'password' hashed with md5 for simplicity if system uses md5, or password_hash.
        // Wait, auth_controller.php uses: password_verify($password, $user['Password']) or md5?
        // Let's use password_hash since it's standard.
        $passHash = password_hash('password123', PASSWORD_DEFAULT);
        $stmtLogin->execute(["staff$num", $passHash, $staffId]);
    }
    echo "<p>Staff seeded.</p>";

    // 8. Seed Students & their process
    $stmtStud = $pdo->prepare("INSERT INTO student (StudentNo, FirstName, LastName, MiddleName, BirthDate, Sex, Email, ContactNo, Address, StudentTypeID, ProgramID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtProf = $pdo->prepare("INSERT INTO student_profile (GuardianName, GuardianContactNo, PreviousSchoolName, PreviousProgram, LastYearLevelCompleted, GWA, StudentID, Address, ContactNo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtLogStud = $pdo->prepare("INSERT INTO login (Username, PasswordHash, UserType, Status, StudentID) VALUES (?, ?, 'Student', ?, ?)");
    
    // Admission
    $stmtAdm = $pdo->prepare("INSERT INTO admission (SubmissionDate, ApprovalDate, Status, Remarks, StudentID, StaffID) VALUES (CURDATE(), CURDATE(), ?, 'N/A', ?, 1)");
    // ID Val
    $stmtIdVal = $pdo->prepare("INSERT INTO id_validation (ValidationDate, Status, StudentID, StaffID) VALUES (CURDATE(), ?, ?, 1)");
    // Eval
    $stmtEval = $pdo->prepare("INSERT INTO evaluation (EvaluationDate, Status, Remarks, StudentID, StaffID) VALUES (CURDATE(), ?, '', ?, 1)");
    // Eval Subject
    $stmtEvalSub = $pdo->prepare("INSERT INTO evaluation_subject (EvaluationID, SubjectID, SubjectStatus) VALUES (?, ?, 'Approved')");
    // Payment
    $stmtPay = $pdo->prepare("INSERT INTO payment (PaymentDate, Amount, ReceiptNo, PaymentStatus, StudentID, StaffID) VALUES (CURDATE(), 5000.00, ?, ?, ?, 3)");
    // Enrollment
    $stmtEnroll = $pdo->prepare("INSERT INTO enrollment (EnrollmentDate, Status, SchoolYear, Semester, StudentID, StaffID, EvaluationID, PaymentID) VALUES (CURDATE(), ?, '2026-2027', '1st', ?, 1, ?, ?)");
    
    // We will distribute 15 students in various stages:
    // 1-3: Pending Admission
    // 4-6: Pending ID Validation (Admission Approved)
    // 7-9: Pending Evaluation (ID Val Completed, Login Active)
    // 10-12: Pending Sectioning/Payment (Evaluation Approved)
    // 13-15: Fully Enrolled (Payment Paid, Clearance Cleared)


    for ($i = 1; $i <= 15; $i++) {
        $studentNo = "2026-000" . str_pad($i, 2, '0', STR_PAD_LEFT);
        $fname = "Student$i";
        $lname = "Test";
        $email = "student$i@test.com";
        $progId = ($i % $numPrograms) + 1;
        $passHash = password_hash('student123', PASSWORD_DEFAULT);
        
        $stmtStud->execute([$studentNo, $fname, $lname, '', '2005-01-01', 'Male', $email, '09990000000', 'GenSan City', 1, $progId]);
        $studId = $pdo->lastInsertId();
        
        $stmtProf->execute(["Guardian $i", '09880000000', 'High School', 'N/A', 'Grade 12', 90.0, $studId, 'GenSan City', '09990000000']);
        
        if ($i <= 3) {
            // Stage 1: Pending Admission
            $stmtAdm->execute(['Pending', $studId]);
            // No login yet
        }
        else if ($i <= 6) {
            // Stage 2: ID Validation
            $stmtAdm->execute(['Approved', $studId]);
            $stmtIdVal->execute(['Pending', $studId]);
            $stmtLogStud->execute([$studentNo, $passHash, 'Inactive', $studId]);
        }
        else if ($i <= 9) {
            // Stage 3: Evaluation
            $stmtAdm->execute(['Approved', $studId]);
            $stmtIdVal->execute(['Completed', $studId]);
            $stmtLogStud->execute([$studentNo, $passHash, 'Active', $studId]);
            $stmtEval->execute(['Pending', $studId]);
        }
        else if ($i <= 12) {
            // Stage 4: Pending Blocking / Payment
            $stmtAdm->execute(['Approved', $studId]);
            $stmtIdVal->execute(['Completed', $studId]);
            $stmtLogStud->execute([$studentNo, $passHash, 'Active', $studId]);
            $stmtEval->execute(['Approved', $studId]);
            $evalId = $pdo->lastInsertId();
            
            for ($s = 1; $s <= 5; $s++) {
                $subId = (($progId - 1) * 5) + $s;
                $stmtEvalSub->execute([$evalId, $subId]);
            }
            
            $stmtPay->execute(['', 'Pending', $studId]);
            $payId = $pdo->lastInsertId();
            $stmtEnroll->execute(['Pending', $studId, $evalId, $payId]);
        }
        else {
            // Stage 5: Fully Enrolled
            $stmtAdm->execute(['Approved', $studId]);
            $stmtIdVal->execute(['Completed', $studId]);
            $stmtLogStud->execute([$studentNo, $passHash, 'Active', $studId]);
            $stmtEval->execute(['Approved', $studId]);
            $evalId = $pdo->lastInsertId();
            
            for ($s = 1; $s <= 5; $s++) {
                $subId = (($progId - 1) * 5) + $s;
                $stmtEvalSub->execute([$evalId, $subId]);
            }
            
            $stmtPay->execute(['OR-TEST-'.$i, 'Paid', $studId]);
            $payId = $pdo->lastInsertId();
            $stmtEnroll->execute(['Enrolled', $studId, $evalId, $payId]);
            $enrollId = $pdo->lastInsertId();
            
            // Blocking
            $pdo->exec("INSERT INTO blocking (BlockingDate, EnrollmentID, SectionID) VALUES (CURDATE(), $enrollId, $progId)");
            for ($s = 1; $s <= 5; $s++) {
                $subId = (($progId - 1) * 5) + $s;
                $pdo->exec("INSERT INTO enrollment_subject (EnrollmentID, SubjectID, SectionID) VALUES ($enrollId, $subId, $progId)");
            }
            
            // Clinic & Clearance
            $pdo->exec("INSERT INTO clinic (CompletionDate, Status, StudentID, StaffID) VALUES (CURDATE(), 'Completed', $studId, 1)");
            $pdo->exec("INSERT INTO clearance (ClearanceDate, Status, StudentID) VALUES (CURDATE(), 'Cleared', $studId)");
        }
    }
    echo "<p>Students seeded successfully.</p>";
    echo "<h2>Seed Complete</h2>";
    echo "<p><strong>Staff Login:</strong> staff1, staff2, staff3, staff4, staff5 (Password: password123)</p>";
    echo "<p><strong>Student Login:</strong> 2026-00004 to 2026-00015 (Password: student123)</p>";
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
