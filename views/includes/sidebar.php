<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['Role'] ?? '';
$userType = $_SESSION['UserType'] ?? '';
$isAdmin = $role === 'Admin';
$isStudent = $userType === 'Student';

function is_active($page, $current) {
    return $page === $current ? 'bg-primary text-on-primary shadow-sm font-semibold rounded-lg' : 'rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors';
}
?>
<aside class="fixed left-0 top-0 h-full w-72 bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-50 flex flex-col justify-between overflow-y-auto">
    <div class="flex flex-col">
        <div class="h-16 px-gutter flex items-center gap-space-sm bg-surface-container-lowest">
            <img src="../assets/seait.png" alt="SEAIT Logo" class="w-10 h-10 object-contain shadow-sm" />
            <div class="flex flex-col">
                <span class="font-headline-sm text-headline-sm text-primary leading-tight tracking-tight">SEAIT</span>
                <span class="font-label-sm text-label-sm text-tertiary tracking-wider uppercase font-semibold">Enrolment Platform</span>
            </div>
        </div>
        
        <nav class="flex flex-col px-space-md gap-space-xs mt-space-md">
            
            <?php if ($isAdmin): ?>
            <!-- Admin Modules -->
            <div class="px-space-sm py-space-xs mt-space-sm font-label-sm text-label-sm text-tertiary uppercase tracking-wider font-semibold">Admin Modules</div>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admin_dashboard.php', $currentPage); ?>" href="admin_dashboard.php">
                <i class="fa-solid fa-gauge-high text-[20px]"></i>
                <span class="font-label-lg text-label-lg">System Overview</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admin_users.php', $currentPage); ?>" href="admin_users.php">
                <i class="fa-solid fa-users-gear text-[20px]"></i>
                <span class="font-label-lg text-label-lg">User Login Management</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admin_staff.php', $currentPage); ?>" href="admin_staff.php">
                <i class="fa-solid fa-user-shield text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Staff & Roles Configuration</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admin_departments.php', $currentPage); ?>" href="admin_departments.php">
                <i class="fa-solid fa-building text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Department Management</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admin_programs.php', $currentPage); ?>" href="admin_programs.php">
                <i class="fa-solid fa-graduation-cap text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Program Master Data</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admin_student_types.php', $currentPage); ?>" href="admin_student_types.php">
                <i class="fa-solid fa-shapes text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Student Types Master Data</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admin_logs.php', $currentPage); ?>" href="admin_logs.php">
                <i class="fa-solid fa-receipt text-[20px]"></i>
                <span class="font-label-lg text-label-lg">System Logs & Auditing</span>
            </a>
            <?php endif; ?>

            <?php if ($isAdmin || $role === 'Clinic'): ?>
            <!-- Clinic Modules -->
            <div class="px-space-sm py-space-xs mt-space-sm font-label-sm text-label-sm text-tertiary uppercase tracking-wider font-semibold">Clinic Modules</div>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('clinic_dashboard.php', $currentPage); ?>" href="clinic_dashboard.php">
                <i class="fa-solid fa-heart-pulse text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Clinic Dashboard</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('clinic_consultations.php', $currentPage); ?>" href="clinic_consultations.php">
                <i class="fa-solid fa-briefcase-medical text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Medical Consultations / Checkups</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('clearance.php', $currentPage); ?>" href="clearance.php">
                <i class="fa-solid fa-shield-heart text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Health Clearance Queue</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('clinic_patients.php', $currentPage); ?>" href="clinic_patients.php">
                <i class="fa-solid fa-bed-pulse text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Patient Directory</span>
            </a>
            <?php endif; ?>

            <?php if ($isAdmin || $role === 'Accounting'): ?>
            <!-- Accounting Modules -->
            <div class="px-space-sm py-space-xs mt-space-sm font-label-sm text-label-sm text-tertiary uppercase tracking-wider font-semibold">Accounting Modules</div>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('accounting_dashboard.php', $currentPage); ?>" href="accounting_dashboard.php">
                <i class="fa-solid fa-landmark text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Cashier Dashboard</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('accounting_pos.php', $currentPage); ?>" href="accounting_pos.php">
                <i class="fa-solid fa-cash-register text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Payment Processing (POS)</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('payment.php', $currentPage); ?>" href="payment.php">
                <i class="fa-solid fa-money-bill-wave text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Record New Payment</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('accounting_billing_queue.php', $currentPage); ?>" href="accounting_billing_queue.php">
                <i class="fa-solid fa-receipt text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Enrollment Billing Queue</span>
            </a>
            <?php endif; ?>

            <?php if ($isAdmin || $role === 'Registrar'): ?>
            <!-- Registrar Modules -->
            <div class="px-space-sm py-space-xs mt-space-sm font-label-sm text-label-sm text-tertiary uppercase tracking-wider font-semibold">Registrar Modules</div>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('staff_dashboard.php', $currentPage); ?>" href="staff_dashboard.php">
                <i class="fa-solid fa-table-cells-large text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Operations Dashboard</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('admission.php', $currentPage); ?>" href="admission.php">
                <i class="fa-solid fa-user-check text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Admissions Queue</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('registrar_students.php', $currentPage); ?>" href="registrar_students.php">
                <i class="fa-solid fa-address-book text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Student Directory & Registration</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('evaluation.php', $currentPage); ?>" href="evaluation.php">
                <i class="fa-solid fa-folder-check text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Subject Evaluations</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('registrar_crediting.php', $currentPage); ?>" href="registrar_crediting.php">
                <i class="fa-solid fa-clipboard-check text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Subject Crediting Processing</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('registrar_enrollment_approvals.php', $currentPage); ?>" href="registrar_enrollment_approvals.php">
                <i class="fa-solid fa-check-double text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Enrollment Approvals</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('blocking.php', $currentPage); ?>" href="blocking.php">
                <i class="fa-solid fa-book-bookmark text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Sectioning & Blocking</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('registrar_schedule.php', $currentPage); ?>" href="registrar_schedule.php">
                <i class="fa-solid fa-calendar-days text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Class Schedule Plotter</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('registrar_catalog.php', $currentPage); ?>" href="registrar_catalog.php">
                <i class="fa-solid fa-book-open text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Academic Course Catalog</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('registrar_departments.php', $currentPage); ?>" href="registrar_departments.php">
                <i class="fa-solid fa-diagram-project text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Programs & Departments</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('id_validation.php', $currentPage); ?>" href="id_validation.php">
                <i class="fa-solid fa-id-badge text-[20px]"></i>
                <span class="font-label-lg text-label-lg">ID Validation Processor</span>
            </a>
            <?php endif; ?>

            <?php if ($isStudent): ?>
            <!-- Student Modules -->
            <div class="px-space-sm py-space-xs mt-space-sm font-label-sm text-label-sm text-tertiary uppercase tracking-wider font-semibold">Student Modules</div>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_dashboard.php', $currentPage); ?>" href="student_dashboard.php">
                <i class="fa-solid fa-house text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Dashboard</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_profile.php', $currentPage); ?>" href="student_profile.php">
                <i class="fa-solid fa-user text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Profile & Background</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_admission.php', $currentPage); ?>" href="student_admission.php">
                <i class="fa-solid fa-clipboard-list text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Admissions Application</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_clearances.php', $currentPage); ?>" href="student_clearances.php">
                <i class="fa-solid fa-list-check text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Clearances Overview</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_clinic.php', $currentPage); ?>" href="student_clinic.php">
                <i class="fa-solid fa-notes-medical text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Clinic / Medical Status</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_evaluation.php', $currentPage); ?>" href="student_evaluation.php">
                <i class="fa-solid fa-clipboard-check text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Curriculum Evaluation</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_credited.php', $currentPage); ?>" href="student_credited.php">
                <i class="fa-solid fa-list-check text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Credited Subjects</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_enrollment.php', $currentPage); ?>" href="student_enrollment.php">
                <i class="fa-solid fa-id-card text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Enrollment Registration</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_schedule.php', $currentPage); ?>" href="student_schedule.php">
                <i class="fa-solid fa-calendar-check text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Section & Class Schedule</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_billing.php', $currentPage); ?>" href="student_billing.php">
                <i class="fa-solid fa-receipt text-[20px]"></i>
                <span class="font-label-lg text-label-lg">Billing & Payments</span>
            </a>
            <a class="flex items-center gap-space-sm px-space-md py-space-sm <?php echo is_active('student_id.php', $currentPage); ?>" href="student_id.php">
                <i class="fa-solid fa-id-card text-[20px]"></i>
                <span class="font-label-lg text-label-lg">ID Validation Card</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>
    
    <div class="p-space-md bg-surface-container-low mx-space-md mt-space-lg mb-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="flex items-center gap-space-xs text-secondary font-label-md text-label-md font-semibold mb-space-xs">
            <i class="fa-solid fa-circle-check text-[16px]"></i>
            <span class="">Authorized Terminal</span>
        </div>
        <?php
        $roleNames = [
            'Registrar' => 'Office of the University Registrar',
            'Accounting' => 'Cashier / Accounting Office',
            'Clinic' => 'Campus Health Clinic',
            'Admin' => 'System Administrator'
        ];
        $displayRole = $isStudent ? 'Student Portal' : ($roleNames[$role] ?? 'Registrar Terminal');
        $tier = $isStudent ? 'Student Access Tier' : 'Official Record Access Tier 1';
        ?>
        <p class="font-body-sm text-body-sm text-tertiary"><?= $displayRole ?> • <?= $tier ?></p>
    </div>
</aside>
