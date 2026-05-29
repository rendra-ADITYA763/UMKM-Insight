<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " — UMKM Insight" : "UMKM Insight"; ?></title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Tailwind for utilities (optional, matches existing design) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            900: '#134e4a'
                        }
                    }
                }
            }
        }
    </script>
    <script>
        // Cek preferensi tema
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>

    <!-- Custom Shared CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Global UI Tweaks */
        body { font-family: 'Inter', sans-serif; background: var(--surface); color: var(--text-primary); margin: 0; padding: 0; }
        
        /* Dashboard Layout (Flex row for Sidebar + Main) */
        .dashboard-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-wrap { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .main-content { flex: 1; overflow-y: auto; padding: 24px; }
    </style>
</head>
<body class="<?php echo (isLoggedIn() && (!isset($activePage) || $activePage !== 'landing')) ? 'dashboard-layout' : ''; ?>">
