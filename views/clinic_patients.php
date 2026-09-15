<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Clinic', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Clinic staff privileges required."));
    exit;
}

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$sql = "
    SELECT s.StudentID, s.StudentNo, s.LastName, s.FirstName, s.BirthDate, s.Sex, s.ContactNo, s.Email,
           p.ProgramName,
           sp.GuardianName, sp.GuardianContactNo,
           cl.Status as ClinicStatus, cl.Remarks as ClinicRemarks, cl.CompletionDate,
           c.Status as ClearanceStatus
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN student_profile sp ON s.StudentID = sp.StudentID
    LEFT JOIN clinic cl ON s.StudentID = cl.StudentID
    LEFT JOIN clearance c ON s.StudentID = c.StudentID
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.StudentNo LIKE ? OR s.LastName LIKE ? OR s.FirstName LIKE ? OR s.Email LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term, $term];
}

if (!empty($statusFilter)) {
    if ($statusFilter === 'Cleared') {
        $sql .= " AND (cl.Status = 'Completed' AND c.Status = 'Cleared')";
    } elseif ($statusFilter === 'Pending') {
        $sql .= " AND (cl.Status = 'Pending' OR c.Status = 'Pending' OR cl.Status IS NULL)";
    }
}

$sql .= " ORDER BY s.LastName ASC, s.FirstName ASC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();
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
                        <i class="fa-solid fa-bed-pulse text-[18px]"></i>
                        <span>Clinic Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Patient Directory</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Browse student medical history, emergency guardian contacts, and health status records.
                    </p>
                </div>
            </div>
        </section>

        <!-- Search & Filter -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex flex-wrap items-center gap-space-sm flex-1">
                    <div class="relative min-w-[280px] flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-tertiary text-[18px]"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search student name, student number..." 
                               class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                    <select name="status" class="h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm">
                        <option value="">All Health Statuses</option>
                        <option value="Cleared" <?= $statusFilter === 'Cleared' ? 'selected' : '' ?>>Medically Cleared</option>
                        <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending Clearance</option>
                    </select>
                </div>
                <div class="flex items-center gap-space-xs">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
                        Search Roster
                    </button>
                    <a href="clinic_patients.php" class="px-3 py-2 bg-surface-container hover:bg-surface-container-high rounded-lg text-sm text-tertiary">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- Patients Roster Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Student</th>
                            <th class="px-space-md py-space-sm">Program</th>
                            <th class="px-space-md py-space-sm">Health Status</th>
                            <th class="px-space-md py-space-sm">Emergency / Guardian</th>
                            <th class="px-space-md py-space-sm">Medical Notes</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach ($patients as $p): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm">
                                <div class="font-semibold text-on-surface"><?= htmlspecialchars($p['LastName'] . ', ' . $p['FirstName']) ?></div>
                                <div class="text-xs font-mono text-primary font-medium"><?= htmlspecialchars($p['StudentNo']) ?></div>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($p['ProgramName'] ?? 'Unassigned') ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php if ($p['ClinicStatus'] === 'Completed' || $p['ClearanceStatus'] === 'Cleared'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Cleared
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Pending Checkup
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <div><?= htmlspecialchars($p['GuardianName'] ?? 'Not specified') ?></div>
                                <div class="font-mono text-[11px] text-on-surface"><?= htmlspecialchars($p['GuardianContactNo'] ?? 'No phone') ?></div>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary max-w-xs truncate" title="<?= htmlspecialchars($p['ClinicRemarks'] ?? '') ?>">
                                <?= htmlspecialchars($p['ClinicRemarks'] ?? 'No checkup recorded') ?>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <a href="clinic_consultations.php?student_id=<?= $p['StudentID'] ?>" class="px-3 py-1 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-xs rounded transition-colors inline-flex items-center gap-1">
                                    <i class="fa-solid fa-stethoscope text-[16px]"></i>
                                    <span>Checkup</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="px-space-md py-8 text-center text-tertiary">No student patients found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>