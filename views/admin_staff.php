<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Administrator privileges required."));
    exit;
}

// Fetch all staff with department and login account status
$stmt = $pdo->query("
    SELECT s.*, d.DepartmentName, l.Username, l.Status as LoginStatus 
    FROM staff s 
    LEFT JOIN department d ON s.DepartmentID = d.departmentID 
    LEFT JOIN login l ON s.StaffID = l.StaffID 
    ORDER BY s.StaffID ASC
");
$staffList = $stmt->fetchAll();

// Fetch departments for dropdown
$departments = $pdo->query("SELECT * FROM department ORDER BY DepartmentName ASC")->fetchAll();
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
                        <i class="fa-solid fa-user-shield text-[18px]"></i>
                        <span>Admin Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Staff & Roles Configuration</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Configure institutional staff assignments, functional roles, and system permission tiers.
                    </p>
                </div>
                <button onclick="openAddStaffModal()" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-user-plus text-[18px]"></i>
                    <span>Add New Staff</span>
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

        <!-- Staff Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Staff Member</th>
                            <th class="px-space-md py-space-sm">Username</th>
                            <th class="px-space-md py-space-sm">Assigned Role</th>
                            <th class="px-space-md py-space-sm">Department</th>
                            <th class="px-space-md py-space-sm">Contact Info</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach ($staffList as $staff): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-semibold text-on-surface">
                                <?= htmlspecialchars($staff['FirstName'] . ' ' . $staff['LastName']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-mono text-xs text-primary font-semibold">
                                <?= htmlspecialchars($staff['Username'] ?? 'No Login') ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php
                                $badgeColor = [
                                    'Admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'Registrar' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'Accounting' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'Clinic' => 'bg-teal-100 text-teal-800 border-teal-200'
                                ][$staff['RoleID']] ?? 'bg-surface-container text-tertiary border-surface-container';
                                ?>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $badgeColor ?>">
                                    <?= htmlspecialchars($staff['RoleID']) ?>
                                </span>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($staff['DepartmentName'] ?? 'Unassigned') ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <div><?= htmlspecialchars($staff['Email'] ?? '') ?></div>
                                <div class="font-mono text-[11px]"><?= htmlspecialchars($staff['ContactNo'] ?? '') ?></div>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <button onclick='openEditStaffModal(<?= json_encode($staff) ?>)' class="px-3 py-1 bg-surface-container hover:bg-surface-container-high rounded text-xs font-medium text-on-surface transition-colors">
                                    Edit Role / Dept
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<!-- Add Staff Modal -->
<div id="addStaffModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-lg w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Add New Staff Member</h3>
            <button onclick="closeAddStaffModal()" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="../controllers/admin_action_controller.php" class="flex flex-col gap-3 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_staff">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">First Name</label>
                    <input type="text" name="first_name" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Last Name</label>
                    <input type="text" name="last_name" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Email</label>
                    <input type="email" name="email" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Contact No</label>
                    <input type="text" name="contact_no" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Functional Role</label>
                    <select name="role_id" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                        <option value="Registrar">Registrar</option>
                        <option value="Clinic">Clinic</option>
                        <option value="Accounting">Accounting</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Department</label>
                    <select name="department_id" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                        <?php foreach($departments as $d): ?>
                            <option value="<?= $d['departmentID'] ?>"><?= htmlspecialchars($d['DepartmentName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container mt-2">
                <div class="text-xs font-semibold text-tertiary uppercase mb-2">Login Credentials</div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-tertiary mb-1">Username</label>
                        <input type="text" name="username" required placeholder="e.g. staff_jane" class="w-full h-9 px-3 rounded-lg bg-surface-container-lowest border border-surface-container text-sm font-mono focus:outline-none focus:border-primary"/>
                    </div>
                    <div>
                        <label class="block text-[11px] text-tertiary mb-1">Default Password</label>
                        <input type="text" name="password" value="password123" required class="w-full h-9 px-3 rounded-lg bg-surface-container-lowest border border-surface-container text-sm font-mono focus:outline-none focus:border-primary"/>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-surface-container">
                <button type="button" onclick="closeAddStaffModal()" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container">Save & Provision Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Edit Staff Assignment</h3>
            <button onclick="closeEditStaffModal()" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="../controllers/admin_action_controller.php" class="flex flex-col gap-3 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="update_staff">
            <input type="hidden" id="edit_staff_id" name="staff_id" value="">

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Staff Member</label>
                <input type="text" id="edit_staff_name" disabled class="w-full h-9 px-3 rounded-lg bg-surface-container text-sm font-semibold text-on-surface"/>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Role Assignment</label>
                <select id="edit_role_id" name="role_id" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm">
                    <option value="Registrar">Registrar</option>
                    <option value="Clinic">Clinic</option>
                    <option value="Accounting">Accounting</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Department</label>
                <select id="edit_dept_id" name="department_id" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm">
                    <?php foreach($departments as $d): ?>
                        <option value="<?= $d['departmentID'] ?>"><?= htmlspecialchars($d['DepartmentName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Email</label>
                <input type="email" id="edit_email" name="email" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm"/>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Contact No</label>
                <input type="text" id="edit_contact" name="contact_no" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm"/>
            </div>

            <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-surface-container">
                <button type="button" onclick="closeEditStaffModal()" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddStaffModal() {
    document.getElementById('addStaffModal').classList.remove('hidden');
}
function closeAddStaffModal() {
    document.getElementById('addStaffModal').classList.add('hidden');
}
function openEditStaffModal(staff) {
    document.getElementById('edit_staff_id').value = staff.StaffID;
    document.getElementById('edit_staff_name').value = staff.FirstName + ' ' + staff.LastName;
    document.getElementById('edit_role_id').value = staff.RoleID;
    document.getElementById('edit_dept_id').value = staff.DepartmentID;
    document.getElementById('edit_email').value = staff.Email;
    document.getElementById('edit_contact').value = staff.ContactNo;
    document.getElementById('editStaffModal').classList.remove('hidden');
}
function closeEditStaffModal() {
    document.getElementById('editStaffModal').classList.add('hidden');
}
</script>

<?php include 'includes/footer.php'; ?>