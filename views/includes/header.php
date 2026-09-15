<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SEAIT Enrolment Platform</title>
    <!-- Instant Dark Mode Initialization (Prevents FOUC) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <!-- Font Awesome 6 Icons (Local & CDN Fallback) -->
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@100..900&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
            main > :first-child { margin-top: 0 !important; }
            main > :last-child { margin-bottom: 0 !important; }
        }
        ::-webkit-scrollbar { display: none; }

        /* Dark Mode Global Overrides & Transitions */
        html.dark {
            color-scheme: dark;
        }
        html.dark body {
            background-color: #0b0f19 !important;
            color: #f8fafc !important;
        }
        html.dark aside {
            background-color: #111827 !important;
            border-right: 1px solid #1f293d !important;
        }
        html.dark header {
            background-color: rgba(17, 24, 39, 0.95) !important;
            border-bottom: 1px solid #1f293d !important;
        }
        html.dark .bg-surface-container-lowest,
        html.dark .bg-white {
            background-color: #111827 !important;
        }
        html.dark .bg-surface-container-low,
        html.dark .bg-slate-50,
        html.dark .bg-slate-100 {
            background-color: #172033 !important;
        }
        html.dark .bg-surface-container,
        html.dark .bg-surface-variant {
            background-color: #1e293b !important;
        }
        html.dark .bg-surface-container-high {
            background-color: #222f46 !important;
        }
        html.dark .bg-surface-container-highest {
            background-color: #2d3d59 !important;
        }
        html.dark .hover\:bg-surface-container-high:hover {
            background-color: #222f46 !important;
        }
        html.dark .hover\:bg-primary-container:hover {
            background-color: #ea580c !important;
        }

        /* Surface Texts, Labels & Headings */
        html.dark .text-on-surface,
        html.dark .text-slate-900,
        html.dark .text-slate-800,
        html.dark .text-gray-900,
        html.dark .text-gray-800 {
            color: #f8fafc !important;
        }
        html.dark .text-on-surface-variant,
        html.dark .text-slate-700,
        html.dark .text-slate-600,
        html.dark .text-gray-700,
        html.dark .text-gray-600,
        html.dark label {
            color: #e2e8f0 !important;
        }
        html.dark .text-tertiary,
        html.dark .text-slate-500,
        html.dark .text-slate-400,
        html.dark .text-gray-500,
        html.dark th {
            color: #cbd5e1 !important;
        }

        /* Borders & Dividers */
        html.dark .border-surface-container,
        html.dark .border-slate-200,
        html.dark .border-slate-100,
        html.dark .border-gray-200,
        html.dark .border-gray-100,
        html.dark .border-outline-variant\/30 {
            border-color: #1f293d !important;
        }
        html.dark .divide-surface-container > :not([hidden]) ~ :not([hidden]) {
            border-color: #1f293d !important;
        }

        /* Form Controls */
        html.dark input:not([type="checkbox"]):not([type="radio"]),
        html.dark select,
        html.dark textarea {
            background-color: #172033 !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
        html.dark select option {
            background-color: #172033 !important;
            color: #f8fafc !important;
        }
        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #94a3b8 !important;
        }
        html.dark .shadow-\[0_1px_8px_rgba\(0\,0\,0\,0\.04\)\] {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45) !important;
        }
        html.dark tr.hover\:bg-surface-container-low\/50:hover,
        html.dark tr.hover\:bg-surface-container-low\/30:hover,
        html.dark tr:hover {
            background-color: rgba(30, 41, 59, 0.5) !important;
        }

        /* High-Contrast Status Badges & Accents */

        /* Emerald & Green (Success, Approved, Paid, Fit, Cleared) */
        html.dark .bg-emerald-50,
        html.dark .bg-emerald-100,
        html.dark .bg-green-50,
        html.dark .bg-green-50\/50,
        html.dark .bg-green-100 {
            background-color: rgba(16, 185, 129, 0.2) !important;
            border-color: rgba(16, 185, 129, 0.4) !important;
        }
        html.dark .border-emerald-200,
        html.dark .border-green-200,
        html.dark .border-green-600 {
            border-color: rgba(16, 185, 129, 0.4) !important;
        }
        html.dark .text-emerald-500,
        html.dark .text-emerald-600,
        html.dark .text-emerald-700,
        html.dark .text-emerald-800,
        html.dark .text-emerald-900,
        html.dark .text-green-500,
        html.dark .text-green-600,
        html.dark .text-green-700,
        html.dark .text-green-800,
        html.dark .text-green-900 {
            color: #6ee7b7 !important;
        }

        /* Amber & Yellow (Pending, In Review, In Process) */
        html.dark .bg-amber-50,
        html.dark .bg-amber-100 {
            background-color: rgba(245, 158, 11, 0.2) !important;
            border-color: rgba(245, 158, 11, 0.4) !important;
        }
        html.dark .border-amber-200,
        html.dark .border-amber-500 {
            border-color: rgba(245, 158, 11, 0.4) !important;
        }
        html.dark .text-amber-500,
        html.dark .text-amber-600,
        html.dark .text-amber-700,
        html.dark .text-amber-800,
        html.dark .text-amber-900 {
            color: #fcd34d !important;
        }

        /* Blue & Sky (Credited, Info, Active Links) */
        html.dark .bg-blue-50,
        html.dark .bg-blue-100,
        html.dark .bg-sky-50 {
            background-color: rgba(59, 130, 246, 0.2) !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
        }
        html.dark .border-blue-200 {
            border-color: rgba(59, 130, 246, 0.4) !important;
        }
        html.dark .text-blue-700,
        html.dark .text-blue-800,
        html.dark .text-blue-900,
        html.dark .text-sky-700 {
            color: #93c5fd !important;
        }

        /* Red & Error (Rejected, Failed, Action Required) */
        html.dark .bg-red-50,
        html.dark .bg-red-100,
        html.dark .bg-error-container,
        html.dark .bg-error-container\/50 {
            background-color: rgba(239, 68, 68, 0.2) !important;
            border-color: rgba(239, 68, 68, 0.4) !important;
        }
        html.dark .border-red-200 {
            border-color: rgba(239, 68, 68, 0.4) !important;
        }
        html.dark .text-red-700,
        html.dark .text-red-800,
        html.dark .text-red-900,
        html.dark .text-error,
        html.dark .text-on-error-container {
            color: #fca5a5 !important;
        }

        /* Purple (Special categories, scheduling) */
        html.dark .bg-purple-50,
        html.dark .bg-purple-100 {
            background-color: rgba(168, 85, 247, 0.2) !important;
            border-color: rgba(168, 85, 247, 0.4) !important;
        }
        html.dark .text-purple-700,
        html.dark .text-purple-800 {
            color: #d8b4fe !important;
        }

        /* Teal (Clinical/Health records) */
        html.dark .bg-teal-50,
        html.dark .bg-teal-100 {
            background-color: rgba(20, 184, 166, 0.2) !important;
            border-color: rgba(20, 184, 166, 0.4) !important;
        }
        html.dark .text-teal-700,
        html.dark .text-teal-800 {
            color: #5eead4 !important;
        }

        /* Orange & Secondary (Enrolled, Active modules) */
        html.dark .bg-orange-50,
        html.dark .bg-orange-100,
        html.dark .bg-primary-fixed\/20,
        html.dark .bg-primary-container,
        html.dark .bg-secondary-container,
        html.dark .bg-secondary-container\/50,
        html.dark .bg-secondary-container\/30 {
            background-color: rgba(249, 115, 22, 0.18) !important;
            border-color: rgba(249, 115, 22, 0.35) !important;
        }
        html.dark .text-orange-600,
        html.dark .text-orange-700,
        html.dark .text-orange-800,
        html.dark .text-orange-950,
        html.dark .text-secondary {
            color: #fdba74 !important;
        }

        /* Neutral badges & chips */
        html.dark .bg-gray-100,
        html.dark .bg-slate-200 {
            background-color: rgba(148, 163, 184, 0.15) !important;
            border-color: rgba(148, 163, 184, 0.3) !important;
        }
    </style>
    <script src="../assets/tailwindcss.js"></script>
    <script id="tailwind-config">
        tailwind.config={darkMode:"class",theme:{extend:{"colors":{"surface-container-lowest":"#ffffff","on-surface":"#0f172a","on-tertiary":"#ffffff","tertiary":"#64748b","on-primary":"#ffffff","secondary":"#fb923c","background":"#ffffff","on-secondary-fixed":"#431407","surface-container-high":"#e2e8f0","outline-variant":"#cbd5e1","on-surface-variant":"#334155","surface":"#ffffff","on-primary-fixed-variant":"#7c2d12","on-primary-fixed":"#431407","on-primary-container":"#c2410c","error":"#ef4444","surface-container-highest":"#cbd5e1","secondary-fixed":"#ffedd5","on-error":"#ffffff","surface-tint":"#f97316","on-secondary":"#ffffff","on-secondary-container":"#c2410c","surface-container":"#f1f5f9","primary":"#f97316","inverse-on-surface":"#f8fafc","tertiary-fixed":"#f1f5f9","on-error-container":"#7f1d1d","primary-fixed":"#ffedd5","on-background":"#0f172a","inverse-surface":"#1e293b","error-container":"#fee2e2","surface-variant":"#f1f5f9","on-tertiary-fixed-variant":"#475569","surface-container-low":"#f8fafc","primary-fixed-dim":"#fdba74","on-secondary-fixed-variant":"#9a3412","outline":"#94a3b8","primary-container":"#ffedd5","on-tertiary-container":"#1e293b","secondary-container":"#ffedd5","surface-dim":"#e2e8f0","tertiary-container":"#cbd5e1","on-tertiary-fixed":"#0f172a","tertiary-fixed-dim":"#94a3b8","secondary-fixed-dim":"#fdba74","inverse-primary":"#fdba74","surface-bright":"#ffffff"},"borderRadius":{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},"spacing":{"gutter":"1.5rem","space-xl":"2.5rem","space-md":"1rem","gutter-sm":"1rem","space-xs":"0.25rem","margin":"2rem","margin-sm":"1rem","space-sm":"0.5rem","space-lg":"1.5rem"},"fontFamily":{"label-lg":["Inter"],"headline-sm":["Hanken Grotesk"],"body-lg":["Inter"],"display-lg-mobile":["Hanken Grotesk"],"title-md":["Inter"],"headline-md":["Hanken Grotesk"],"label-md":["Inter"],"body-md":["Inter"],"body-sm":["Inter"],"display-lg":["Hanken Grotesk"],"label-sm":["Inter"],"headline-lg":["Hanken Grotesk"],"headline-lg-mobile":["Hanken Grotesk"]},"fontSize":{"label-lg":["14px",{"lineHeight":"20px","fontWeight":"500"}],"headline-sm":["20px",{"lineHeight":"28px","letterSpacing":"-0.005em","fontWeight":"600"}],"body-lg":["16px",{"lineHeight":"26px","fontWeight":"400"}],"display-lg-mobile":["32px",{"lineHeight":"40px","letterSpacing":"-0.015em","fontWeight":"700"}],"title-md":["16px",{"lineHeight":"24px","letterSpacing":"-0.005em","fontWeight":"600"}],"headline-md":["24px",{"lineHeight":"32px","letterSpacing":"-0.01em","fontWeight":"600"}],"label-md":["12px",{"lineHeight":"16px","letterSpacing":"0.025em","fontWeight":"600"}],"body-md":["14px",{"lineHeight":"22px","fontWeight":"400"}],"body-sm":["12px",{"lineHeight":"18px","fontWeight":"400"}],"display-lg":["48px",{"lineHeight":"56px","letterSpacing":"-0.02em","fontWeight":"700"}],"label-sm":["11px",{"lineHeight":"14px","letterSpacing":"0.04em","fontWeight":"600"}],"headline-lg":["32px",{"lineHeight":"40px","letterSpacing":"-0.015em","fontWeight":"600"}],"headline-lg-mobile":["24px",{"lineHeight":"32px","letterSpacing":"-0.01em","fontWeight":"600"}]}}}};
    </script>
</head>
<body class="bg-background font-body-md text-on-surface antialiased selection:bg-primary selection:text-on-primary transition-colors duration-200">
