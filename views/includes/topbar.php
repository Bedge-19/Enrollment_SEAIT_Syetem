<header class="fixed top-0 left-72 right-0 h-16 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-40 flex items-center justify-between px-gutter">
    <div class="flex items-center gap-gutter-sm flex-1">
        <div class="flex items-center gap-space-xs bg-surface-container px-space-md py-space-xs rounded-full text-on-surface font-label-md text-label-md font-semibold">
            <i class="fa-solid fa-calendar-days text-[18px] text-primary"></i>
            <span class="">A.Y. 2026-2027 • 1st Semester</span>
            <i class="fa-solid fa-chevron-down text-[16px] text-tertiary cursor-pointer"></i>
        </div>
        <div class="relative max-w-md w-full">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-tertiary text-[20px]"></i>
            <input onkeyup="filterQueue(this.value)" class="w-full h-10 pl-10 pr-space-md bg-surface-container-low text-on-surface placeholder:text-tertiary font-body-sm text-body-sm rounded-lg focus:outline-none focus:bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)] transition-all" placeholder="Search Student No, Student Name, Applicant ID..." type="search"/>
        </div>
    </div>
    <div class="flex items-center gap-space-md">
        <a href="apply.php" target="_blank" class="flex items-center gap-space-xs bg-primary text-on-primary px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-medium shadow-[0_1px_8px_rgba(0,0,0,0.04)] hover:bg-primary-container transition-all">
            <i class="fa-solid fa-plus text-[18px]"></i>
            <span class="">New Enrollment</span>
        </a>
        <!-- Dark Mode Toggle -->
        <button id="themeToggleBtn" onclick="toggleDarkMode()" class="p-space-sm rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors flex items-center justify-center" type="button" title="Toggle Dark / Light Mode">
            <i id="themeToggleIcon" class="fa-solid fa-moon text-[22px]"></i>
        </button>

        <button onclick="alert('No new notifications at this time.')" class="relative p-space-sm rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors" type="button">
            <i class="fa-solid fa-bell text-[22px]"></i>
            <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
        </button>
        <div class="flex items-center gap-space-sm pl-space-sm">
            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold">
                <?php echo strtoupper(substr($_SESSION['Username'] ?? 'S', 0, 1)); ?>
            </div>
            <div class="flex flex-col">
                <span class="font-label-md text-label-md text-on-surface font-semibold leading-tight"><?php echo htmlspecialchars($_SESSION['Username'] ?? 'User'); ?></span>
                <?php
                $roleLabel = 'Student';
                if (($_SESSION['UserType'] ?? '') === 'Staff') {
                    $r = $_SESSION['Role'] ?? '';
                    $roleMap = [
                        'Admin' => 'System Administrator',
                        'Registrar' => 'Registrar Staff',
                        'Clinic' => 'Campus Health Staff',
                        'Accounting' => 'Accounting Cashier'
                    ];
                    $roleLabel = $roleMap[$r] ?? 'Registrar Staff';
                }
                ?>
                <span class="font-label-sm text-label-sm text-tertiary leading-tight"><?= $roleLabel ?></span>
            </div>
            <a href="logout.php" class="ml-2 p-1 text-tertiary hover:text-primary transition-colors bg-surface-container-low rounded-full flex items-center" title="Logout">
                <i class="fa-solid fa-right-from-bracket text-[18px]"></i>
            </a>
        </div>
    </div>
</header>
<script>
function filterQueue(query) {
    const q = query.toLowerCase();
    const items = document.querySelectorAll('aside .overflow-y-auto a');
    items.forEach(item => {
        if(item.innerText.toLowerCase().includes(q)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function updateThemeIcon() {
    const icon = document.getElementById('themeToggleIcon');
    if (!icon) return;
    const isDark = document.documentElement.classList.contains('dark');
    icon.className = isDark ? 'fa-solid fa-sun text-[20px] text-amber-400' : 'fa-solid fa-moon text-[20px]';
    icon.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
}

function toggleDarkMode() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeIcon();
}

document.addEventListener('DOMContentLoaded', updateThemeIcon);
updateThemeIcon();
</script>
