<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Clinic', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Clinic staff privileges required."));
    exit;
}

$selectedStudentId = (int)($_GET['student_id'] ?? 0);

// Fetch all students for the dropdown
$students = $pdo->query("SELECT StudentID, StudentNo, LastName, FirstName FROM student ORDER BY LastName ASC, FirstName ASC")->fetchAll();

// Fetch consultations history
$stmt = $pdo->query("
    SELECT cl.*, s.StudentNo, s.LastName, s.FirstName, p.ProgramName, st.LastName as StaffLastName
    FROM clinic cl
    JOIN student s ON cl.StudentID = s.StudentID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN staff st ON cl.StaffID = st.StaffID
    ORDER BY cl.ClinicID DESC
    LIMIT 30
");
$consultations = $stmt->fetchAll();
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
                        <i class="fa-solid fa-briefcase-medical text-[18px]"></i>
                        <span>Clinic Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Medical Consultations & Checkups</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Record vital signs, physical examination assessments, and health fitness certificates.
                    </p>
                </div>
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

        <!-- New Consultation Card -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 p-space-lg">
            <div class="border-b border-surface-container pb-space-sm mb-space-md flex items-center gap-2">
                <i class="fa-solid fa-heart-pulse text-primary text-[22px]"></i>
                <h2 class="font-headline-sm text-headline-sm text-on-surface">Record Student Checkup & Vitals</h2>
            </div>
            <form method="POST" action="../controllers/clinic_action_controller.php" class="flex flex-col gap-4 text-sm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="record_consultation">

                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Select Student Candidate</label>
                    <select name="student_id" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                        <option value="">-- Choose Student from Directory --</option>
                        <?php foreach($students as $s): ?>
                            <option value="<?= $s['StudentID'] ?>" <?= $selectedStudentId === (int)$s['StudentID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['StudentNo'] . ' - ' . $s['LastName'] . ', ' . $s['FirstName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Blood Pressure</label>
                        <input type="text" name="blood_pressure" value="120/80" placeholder="e.g. 120/80" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Pulse Rate</label>
                        <input type="text" name="pulse_rate" value="72 bpm" placeholder="e.g. 75 bpm" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Height (cm)</label>
                        <input type="text" name="height" value="165 cm" placeholder="e.g. 165 cm" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Weight (kg)</label>
                        <input type="text" name="weight" value="58 kg" placeholder="e.g. 60 kg" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Clinical Findings & Diagnosis</label>
                        <input type="text" name="findings" value="Physically fit, normal vision, clear breath sounds" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Medical Assessment Status</label>
                        <select name="status" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm font-semibold">
                            <option value="Completed" selected class="text-emerald-700">Completed (Physically Fit)</option>
                            <option value="Pending" class="text-amber-700">Pending (Further Testing Needed)</option>
                            <option value="Failed" class="text-error">Failed / Flagged Condition</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-container text-on-primary font-semibold text-sm rounded-lg transition-colors shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-[18px]"></i>
                        <span>Record Consultation & Update Clearance</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- Consultation Logs History -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="p-space-md border-b border-surface-container">
                <h3 class="font-title-md text-title-md text-on-surface">Recent Medical Consultation Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Date</th>
                            <th class="px-space-md py-space-sm">Student</th>
                            <th class="px-space-md py-space-sm">Program</th>
                            <th class="px-space-md py-space-sm">Vitals & Clinical Remarks</th>
                            <th class="px-space-md py-space-sm">Status</th>
                            <th class="px-space-md py-space-sm">Examiner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($consultations as $c): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm text-xs text-tertiary whitespace-nowrap">
                                <?= date('M d, Y', strtotime($c['CompletionDate'])) ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="font-semibold text-on-surface"><?= htmlspecialchars($c['LastName'] . ', ' . $c['FirstName']) ?></span>
                                <span class="block text-xs font-mono text-primary"><?= htmlspecialchars($c['StudentNo']) ?></span>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($c['ProgramName'] ?? 'Unassigned') ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-on-surface max-w-md">
                                <?= htmlspecialchars($c['Remarks'] ?? 'N/A') ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php if ($c['Status'] === 'Completed'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Fit to Enroll</span>
                                <?php elseif ($c['Status'] === 'Pending'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-container text-error">Flagged</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($c['StaffLastName'] ? 'Dr./Nurse ' . $c['StaffLastName'] : 'Clinic Staff') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($consultations)): ?>
                        <tr>
                            <td colspan="6" class="px-space-md py-8 text-center text-tertiary">No medical consultations recorded yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>