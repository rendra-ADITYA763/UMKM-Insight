<?php
/**
 * Sidebar Navigation
 * Merender menu berdasarkan role user yang sedang login
 */
$current_role = $_SESSION['role'] ?? 'client';
$is_premium = ($_SESSION['tier'] ?? 'free') === 'premium';
?>

<aside class="app-sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="assets/image/logo_desai.png" alt="Logo UMKM Insight" style="width: 24px; height: 24px; object-fit: contain;">
        <span>UMKM Insight</span>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <?php if($current_role === 'client'): ?>
                <li class="nav-item">
                    <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
                        <i class="ph ph-squares-four"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="laporan-penjualan.php" class="<?php echo ($activePage == 'laporan') ? 'active' : ''; ?>">
                        <i class="ph ph-trend-up"></i> Laporan Penjualan
                        <?php if(!$is_premium) echo '<span class="premium-tag">PRO</span>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="arus-kas.php" class="<?php echo ($activePage == 'arus-kas') ? 'active' : ''; ?>">
                        <i class="ph ph-money"></i> Arus Kas
                        <?php if(!$is_premium) echo '<span class="premium-tag">PRO</span>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="performa-produk.php" class="<?php echo ($activePage == 'performa') ? 'active' : ''; ?>">
                        <i class="ph ph-package"></i> Performa Produk
                        <?php if(!$is_premium) echo '<span class="premium-tag">PRO</span>'; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pengaduan.php" class="<?php echo ($activePage == 'pengaduan') ? 'active' : ''; ?>">
                        <i class="ph ph-chat-circle-dots"></i> Pengaduan
                    </a>
                </li>

            <?php elseif($current_role === 'operator'): ?>
                <li class="nav-item">
                    <a href="operator.php" class="<?php echo ($activePage == 'operator') ? 'active' : ''; ?>">
                        <i class="ph ph-lightning"></i> Manajemen Operasional
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pengaduan-admin.php" class="<?php echo ($activePage == 'pengaduan-admin') ? 'active' : ''; ?>">
                        <i class="ph ph-chat-circle-text"></i> Tiket Pengaduan
                    </a>
                </li>

            <?php elseif($current_role === 'admin'): ?>
                <li class="nav-item">
                    <a href="admin.php" class="<?php echo ($activePage == 'admin') ? 'active' : ''; ?>">
                        <i class="ph ph-users-three"></i> Manajemen User
                    </a>
                </li>
                <li class="nav-item">
                    <a href="audit-logs.php" class="<?php echo ($activePage == 'audit-logs') ? 'active' : ''; ?>">
                        <i class="ph ph-list-magnifying-glass"></i> Audit Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="system-config.php" class="<?php echo ($activePage == 'system-config') ? 'active' : ''; ?>">
                        <i class="ph ph-gear"></i> System Config
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <?php if($current_role === 'client'): ?>
            <div class="sidebar-section-title">Integrasi Sistem</div>
            <div style="padding:0 14px">
                <div style="background:var(--surface-alt);padding:12px;border-radius:var(--radius-lg);border:1px solid var(--border-light);display:flex;align-items:flex-start;gap:8px;">
                    <i class="ph-fill ph-check-circle" style="color:var(--success);margin-top:2px;"></i>
                    <div>
                        <p style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);">SmartBank Connected</p>
                        <p style="font-size:0.6875rem;color:var(--text-muted);margin-top:4px;">Read-only API<br>GET /analytics/*</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-ghost btn-sm btn-full" style="justify-content:flex-start;">
            <i class="ph ph-sign-out"></i> Keluar
        </a>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Main content wrap starts after sidebar for flex layout -->
<div class="main-wrap">
