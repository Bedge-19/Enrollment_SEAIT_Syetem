<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Registrar privileges required."));
    exit;
}

// Handle Walk-in Student Registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register_student') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: registrar_students.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $fname = trim($_POST['first_name'] ?? '');
    $lname = trim($_POST['last_name'] ?? '');
    $mname = trim($_POST['middle_name'] ?? '');
    $birthDate = $_POST['birth_date'] ?? '2005-01-01';
    $sex = $_POST['sex'] ?? 'Male';
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact_no'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $progId = (int)($_POST['program_id'] ?? 1);
    $typeId = (int)($_POST['student_type_id'] ?? 1);
    $staffId = $_SESSION['StaffID'] ?? 1;

    // Generate unique student number
    do {
        $studentNo = date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $check = $pdo->prepare("SELECT COUNT(*) FROM student WHERE StudentNo = ?");
        $check->execute([$studentNo]);
    } while ($check->fetchColumn() > 0);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO student (StudentNo, FirstName, LastName, MiddleName, BirthDate, Sex, Address, ContactNo, Email, StudentTypeID, ProgramID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$studentNo, $fname, $lname, $mname, $birthDate, $sex, $address, $contact, $email, $typeId, $progId]);
        $studentId = $pdo->lastInsertId();

        $stmtProf = $pdo->prepare("INSERT INTO student_profile (Address, ContactNo, GuardianName, GuardianContactNo, PreviousSchoolName, PreviousProgram, LastYearLevelCompleted, GWA, StudentID) VALUES (?, ?, 'N/A', 'N/A', 'N/A', 'N/A', 'Grade 12', 90.0, ?)");
        $stmtProf->execute([$address, $contact, $studentId]);

        // Auto-approve admission for walk-in registration
        $stmtAdm = $pdo->prepare("INSERT INTO admission (SubmissionDate, ApprovalDate, Status, Remarks, StudentID, StaffID) VALUES (CURDATE(), CURDATE(), 'Approved', 'Registered on-site by Registrar', ?, ?)");
        $stmtAdm->execute([$studentId, $staffId]);

        // Create initial ID validation record
        $stmtIdVal = $pdo->prepare("INSERT INTO id_validation (ValidationDate, Status, StudentID, StaffID) VALUES (CURDATE(), 'Pending', ?, ?)");
        $stmtIdVal->execute([$studentId, $staffId]);

        // Provision student login
        $passHash = password_hash('student123', PASSWORD_DEFAULT);
        $stmtLog = $pdo->prepare("INSERT INTO login (Username, PasswordHash, UserType, Status, StudentID, StaffID, MustChangePassword) VALUES (?, ?, 'Student', 'Inactive', ?, ?, 1)");
        $stmtLog->execute([$studentNo, $passHash, $studentId, $staffId]);

        $pdo->commit();

        require_once '../config/logger.php';
        log_activity($pdo, 'Register Student', 'Student Directory', "Registered student $studentNo ($fname $lname)");

        header("Location: registrar_students.php?msg=" . urlencode("Student $studentNo ($fname $lname) registered successfully!"));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: registrar_students.php?error=" . urlencode("Registration failed: " . $e->getMessage()));
        exit;
    }
}

$search = trim($_GET['search'] ?? '');
$programFilter = (int)($_GET['program_id'] ?? 0);

$sql = "
    SELECT s.*, p.ProgramName, st.TypeName,
           e.Status as EnrollmentStatus
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN student_type st ON s.StudentTypeID = st.StudentTypeID
    LEFT JOIN enrollment e ON s.StudentID = e.StudentID
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.StudentNo LIKE ? OR s.LastName LIKE ? OR s.FirstName LIKE ? OR s.Email LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term, $term];
}

if ($programFilter > 0) {
    $sql .= " AND s.ProgramID = ?";
    $params[] = $programFilter;
}

$sql .= " ORDER BY s.StudentID DESC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$programs = $pdo->query("SELECT * FROM program ORDER BY ProgramName ASC")->fetchAll();
$studentTypes = $pdo->query("SELECT * FROM student_type ORDER BY TypeName ASC")->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg">

        <!-- Header -->
        <section class="flex flex-col gap-space-md">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-sm bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
                <div class="flex flex-col gap-space-xs">
                    <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-wider font-semibold">
                        <i class="fa-solid fa-address-book text-[18px]"></i>
                        <span>Registrar Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Student Directory & Registration</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Master student body directory, identity credentials, and walk-in on-site registration.
                    </p>
                </div>
                <button onclick="document.getElementById('registerStudentModal').classList.remove('hidden')" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-user-plus text-[18px]"></i>
                    <span>Register Walk-in Student</span>
                </button>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['msg']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($_GET['error'])): ?>
            <div class="p-3 bg-error-container border border-error/30 text-on-error-container rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-error text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
            <?php endif; ?>
        </section>

        <!-- Search & Filters -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex flex-wrap items-center gap-space-sm flex-1">
                    <div class="relative min-w-[280px] flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-tertiary text-[18px]"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search student number, name, email..." 
                               class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                    <select name="program_id" class="h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm max-w-xs">
                        <option value="">All Academic Programs</option>
                        <?php foreach($programs as $p): ?>
                            <option value="<?= $p['ProgramID'] ?>" <?= $programFilter === (int)$p['ProgramID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['ProgramName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-space-xs">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
                        Filter Roster
                    </button>
                    <a href="registrar_students.php" class="px-3 py-2 bg-surface-container hover:bg-surface-container-high rounded-lg text-sm text-tertiary">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- Roster Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Student No</th>
                            <th class="px-space-md py-space-sm">Full Name</th>
                            <th class="px-space-md py-space-sm">Program & Type</th>
                            <th class="px-space-md py-space-sm">Contact Information</th>
                            <th class="px-space-md py-space-sm">Enrollment Status</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($students as $s): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-mono font-bold text-primary">
                                <?= htmlspecialchars($s['StudentNo']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-medium text-on-surface">
                                <?= htmlspecialchars($s['LastName'] . ', ' . $s['FirstName'] . ' ' . ($s['MiddleName'] ?? '')) ?>
                                <span class="block text-xs text-tertiary font-normal"><?= htmlspecialchars($s['Sex']) ?> • <?= date('M d, Y', strtotime($s['BirthDate'])) ?></span>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <div class="font-medium text-on-surface"><?= htmlspecialchars($s['ProgramName'] ?? 'Unassigned') ?></div>
                                <span class="text-[11px] px-2 py-0.5 rounded bg-surface-container font-medium"><?= htmlspecialchars($s['TypeName'] ?? 'Regular') ?></span>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <div><?= htmlspecialchars($s['Email'] ?? 'N/A') ?></div>
                                <div class="font-mono text-[11px]"><?= htmlspecialchars($s['ContactNo'] ?? '') ?></div>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php if (($s['EnrollmentStatus'] ?? '') === 'Enrolled'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Enrolled</span>
                                <?php elseif (($s['EnrollmentStatus'] ?? '') === 'Pending'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">In Process</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-container text-tertiary">Applicant</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <a href="evaluation.php?id=<?= $s['StudentID'] ?>" class="px-2.5 py-1 bg-surface-container hover:bg-surface-container-high text-xs rounded text-on-surface inline-flex items-center gap-1">
                                    <i class="fa-solid fa-clipboard-check text-[15px]"></i>
                                    <span>Evaluate</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6" class="px-space-md py-8 text-center text-tertiary">No student records found matching query.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<!-- Register Walk-in Modal -->
<div id="registerStudentModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-xl w-full p-6 border border-outline-variant/40 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Register Walk-in Student</h3>
            <button onclick="document.getElementById('registerStudentModal').classList.add('hidden')" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="registrar_students.php" class="flex flex-col gap-3 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="register_student">

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">First Name</label>
                    <input type="text" name="first_name" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Middle Name</label>
                    <input type="text" name="middle_name" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Last Name</label>
                    <input type="text" name="last_name" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Birth Date</label>
                    <input type="date" name="birth_date" value="2005-01-01" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Sex</label>
                    <select name="sex" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="student@example.com" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Contact No</label>
                    <input type="text" name="contact_no" required placeholder="09xxxxxxxxx" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Residential Address</label>
                <input type="text" name="address" required placeholder="Barangay, City, Province" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Student Type</label>
                    <select name="student_type_id" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                        <?php foreach($studentTypes as $st): ?>
                            <option value="<?= $st['StudentTypeID'] ?>"><?= htmlspecialchars($st['TypeName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Intended Degree Program</label>
                    <select name="program_id" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                        <?php foreach($programs as $pr): ?>
                            <option value="<?= $pr['ProgramID'] ?>"><?= htmlspecialchars($pr['ProgramName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-surface-container">
                <button type="button" onclick="document.getElementById('registerStudentModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container shadow-sm">Register & Issue Student No</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>