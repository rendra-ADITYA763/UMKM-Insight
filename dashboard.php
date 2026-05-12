<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Proteksi Halaman: Hanya Role Client yang bisa akses dashboard utama UMKM
requireRole('client');

$user = getCurrentUser($pdo);
$userId = $user['id'];
$isPremium = $user['tier'] === 'premium';
$pageTitle = "Dashboard Analytics";
$activePage = 'dashboard';

// --- DATA AGGREGATION (Tahap 4) ---
// 1. Total Revenue (Income)
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transaction_cache WHERE user_id = ? AND type = 'Income'");
$stmt->execute([$userId]);
$totalRevenue = $stmt->fetchColumn() ?: 0;

// 2. Total Expense
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transaction_cache WHERE user_id = ? AND type = 'Expense'");
$stmt->execute([$userId]);
$totalExpense = $stmt->fetchColumn() ?: 0;

// 3. Net Profit
$netProfit = $totalRevenue - $totalExpense;

// 4. Last 5 Transactions
$stmt = $pdo->prepare("SELECT * FROM transaction_cache WHERE user_id = ? ORDER BY transaction_date DESC LIMIT 5");
$stmt->execute([$userId]);
$recentTransactions = $stmt->fetchAll();

// 5. Chart Data (Last 7 Days Revenue)
$stmt = $pdo->prepare("
    SELECT DATE(transaction_date) as t_date, SUM(amount) as total 
    FROM transaction_cache 
    WHERE user_id = ? AND type = 'Income' AND transaction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(transaction_date)
    ORDER BY t_date ASC
");
$stmt->execute([$userId]);
$chartRows = $stmt->fetchAll();

$chartLabels = [];
$chartValues = [];
foreach ($chartRows as $row) {
    $chartLabels[] = date('d M', strtotime($row['t_date']));
    $chartValues[] = (float)$row['total'];
}

// 6. Fetch Notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <!-- Topbar -->
    <?php include 'includes/topbar.php'; ?>

    <div class="animate-fade-in" style="padding-top: 24px;">
        <!-- Greeting -->
        <div style="margin-bottom:24px;">
            <?php 
                $hour = date('H');
                $greet = ($hour < 12) ? 'Selamat Pagi' : (($hour < 17) ? 'Selamat Siang' : 'Selamat Malam');
            ?>
            <h1 style="font-size:1.625rem;font-weight:800;color:var(--text-primary);letter-spacing:-0.02em;">
                <?php echo $greet . ", " . explode(' ', $user['nama_lengkap'])[0]; ?>! 👋
            </h1>
            <p style="font-size:.875rem;color:var(--text-secondary);margin-top:4px;">Berikut ringkasan bisnis UMKM Anda hari ini.</p>
        </div>

        <!-- Profile + Subscription Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- UMKM Profile -->
            <div class="card p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center">
                        <i class="ph-fill ph-storefront text-2xl text-brand-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold"><?php echo $user['nama_bisnis'] ?: 'Bisnis Belum Dinamai'; ?></h2>
                        <p class="text-xs text-slate-400"><?php echo $user['kategori'] ?: 'Kategori belum diset'; ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</p>
                        <p class="text-sm font-medium text-slate-700"><?php echo $user['email']; ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SmartBank ID</p>
                        <p class="text-sm font-medium text-slate-700"><?php echo $user['smartbank_id'] ?: '—'; ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</p>
                        <p class="text-sm font-medium text-slate-700">Terdaftar</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai Bergabung</p>
                        <p class="text-sm font-medium text-slate-700"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Subscription Status -->
            <?php 
                $isFree = $user['tier'] === 'free';
                $expiry = $user['tier_expiry'];
            ?>
            <div class="card p-6 <?php echo $isFree ? 'bg-gradient-to-br from-emerald-50 to-white border-emerald-100' : 'bg-gradient-to-br from-amber-50 to-white border-amber-100'; ?>">
                <div class="flex items-center gap-3 mb-4">
                    <i class="ph-fill <?php echo $isFree ? 'ph-leaf text-emerald-600' : 'ph-crown text-amber-600'; ?> text-2xl"></i>
                    <div>
                        <h3 class="text-lg font-bold"><?php echo $isFree ? 'Free Tier' : 'Premium Access'; ?></h3>
                        <p class="text-xs text-slate-500"><?php echo $isFree ? 'Akses fitur analitik dasar' : 'Akses fitur lengkap & wawasan mendalam'; ?></p>
                    </div>
                    <span class="badge <?php echo $isFree ? 'badge-free' : 'badge-premium'; ?> ml-auto">
                        <?php echo $isFree ? 'GRATIS' : 'AKTIF'; ?>
                    </span>
                </div>
                
                <?php if(!$isFree): ?>
                    <p class="text-sm text-slate-600 mb-4 italic">Berlaku hingga: <strong><?php echo date('d M Y', strtotime($expiry)); ?></strong></p>
                <?php else: ?>
                    <p class="text-sm text-slate-600 mb-4">Ingin wawasan arus kas dan laporan per produk? Upgrade ke Premium sekarang.</p>
                <?php endif; ?>

                <div class="flex gap-2">
                    <?php if($isFree): ?>
                        <a href="landing.php#harga" class="btn btn-premium btn-sm"><i class="ph-fill ph-crown"></i> Upgrade Sekarang</a>
                    <?php endif; ?>
                    <a href="pengaduan.php" class="btn btn-outline btn-sm">Bantuan Layanan</a>
                </div>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="card p-5 border-l-4 border-emerald-500">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pendapatan</span>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600"><i class="ph ph-wallet"></i></div>
                </div>
                <div class="text-xl font-bold text-slate-800"><?php echo formatRupiah($totalRevenue); ?></div>
                <div class="text-[10px] mt-2 font-medium text-emerald-600">Total Akumulasi</div>
            </div>
            <div class="card p-5 border-l-4 border-rose-500">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pengeluaran</span>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-rose-50 text-rose-600"><i class="ph ph-shopping-cart"></i></div>
                </div>
                <div class="text-xl font-bold text-slate-800"><?php echo formatRupiah($totalExpense); ?></div>
                <div class="text-[10px] mt-2 font-medium text-rose-600">Bulan Berjalan</div>
            </div>
            <div class="card p-5 border-l-4 border-indigo-500">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Laba Bersih</span>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-indigo-50 text-indigo-600"><i class="ph ph-trend-up"></i></div>
                </div>
                <div class="text-xl font-bold text-slate-800"><?php echo formatRupiah($netProfit); ?></div>
                <div class="text-[10px] mt-2 font-medium text-indigo-600">Margin Estimasi</div>
            </div>
            <div class="card p-5 border-l-4 border-amber-500">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Transaksi</span>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-amber-50 text-amber-600"><i class="ph ph-receipt"></i></div>
                </div>
                <div class="text-xl font-bold text-slate-800"><?php echo count($recentTransactions); ?></div>
                <div class="text-[10px] mt-2 font-medium text-amber-600">Baru Saja</div>
            </div>
        </div>

        <!-- Mini Charts -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold flex items-center gap-2"><i class="ph ph-trend-up text-emerald-500"></i> Penjualan</h3>
                </div>
                <div style="height: 100px; position: relative;">
                    <canvas id="mini-sales"></canvas>
                </div>
                <a href="laporan-penjualan.php" class="text-[11px] text-brand-600 font-bold block mt-3">Detail Penjualan →</a>
            </div>
            <div class="card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold flex items-center gap-2"><i class="ph ph-money text-blue-500"></i> Arus Kas</h3>
                </div>
                <div style="height: 100px; position: relative;">
                    <canvas id="mini-cashflow"></canvas>
                </div>
                <a href="arus-kas.php" class="text-[11px] text-brand-600 font-bold block mt-3">Detail Arus Kas →</a>
            </div>
            <div class="card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold flex items-center gap-2"><i class="ph ph-package text-amber-500"></i> Produk</h3>
                </div>
                <div style="height: 100px; position: relative;">
                    <canvas id="mini-product"></canvas>
                </div>
                <a href="performa-produk.php" class="text-[11px] text-brand-600 font-bold block mt-3">Detail Produk →</a>
            </div>
        </div>

        <!-- Transactions & Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="card p-5">
                <h3 class="text-sm font-bold mb-4 flex items-center gap-2"><i class="ph ph-bell-ringing text-amber-500"></i> Info & Notifikasi</h3>
                <div class="flex flex-col gap-3" id="notif-list-dashboard">
                    <?php if(empty($notifications)): ?>
                        <div class="text-center py-6">
                            <p class="text-[10px] text-slate-400">Belum ada notifikasi.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($notifications as $n): ?>
                            <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                                <p class="text-xs font-bold text-slate-700 mb-1"><?php echo $n['title']; ?></p>
                                <p class="text-[10px] text-slate-500 leading-relaxed"><?php echo $n['message']; ?></p>
                                <p class="text-[8px] text-slate-400 mt-2"><?php echo date('d M Y, H:i', strtotime($n['created_at'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="lg:col-span-2 card overflow-hidden">
                <div class="p-5 border-bottom border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold">Transaksi Terbaru</h3>
                        <p class="text-[10px] text-slate-400">Data terhubung langsung dengan API SmartBank</p>
                    </div>
                    <a href="laporan-penjualan.php" class="btn btn-ghost btn-sm">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr><th>ID</th><th>Tipe</th><th>Sumber</th><th class="text-right">Nominal</th><th>Status</th></tr>
                        </thead>
                        <tbody id="trx-body-dashboard">
                            <?php foreach($recentTransactions as $t): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="font-mono text-[10px] text-slate-400"><?php echo $t['external_id']; ?></td>
                                    <td class="font-bold text-slate-700 text-xs"><?php echo $t['type']; ?></td>
                                    <td><span class="badge badge-neutral"><?php echo $t['source']; ?></span></td>
                                    <td class="text-right font-bold <?php echo $t['type'] === 'Income' ? 'text-emerald-500' : 'text-rose-500'; ?>">
                                        <?php echo ($t['type'] === 'Income' ? '+ ' : '- ') . formatRupiah($t['amount']); ?>
                                    </td>
                                    <td><span class="badge badge-success">Success</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="mt-12 text-center text-[11px] text-slate-400 py-6 border-t border-slate-100">
            &copy; <?php echo date('Y'); ?> Ekosistem Ekonomi UMKM. Simulasi Sistem Informasi RPL 2.
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- MINI CHARTS (Tahap 4) ---
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#94a3b8';
        const miniOpts = {responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{display:false},y:{display:false}},elements:{point:{radius:0}}};

        // Revenue Chart (Mini)
        const labels = <?php echo json_encode($chartLabels); ?>;
        const dataValues = <?php echo json_encode($chartValues); ?>;

        new Chart(document.getElementById('mini-sales'), {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No Data'],
                datasets: [{
                    data: dataValues.length ? dataValues : [0],
                    borderColor: '#14b8a6',
                    backgroundColor: 'rgba(20, 184, 166, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: miniOpts
        });
        
        // Placeholder static charts for mini display
        new Chart(document.getElementById('mini-cashflow'),{type:'bar',data:{labels:['M1','M2','M3','M4','M5'],datasets:[{data:[4,6,3,8,5],backgroundColor:'rgba(20, 184, 166, 0.6)',borderRadius:3},{data:[-2,-1,-4,-2,-3],backgroundColor:'rgba(244,63,94,0.4)',borderRadius:3}]},options:{...miniOpts,scales:{x:{display:false},y:{display:false}}}});
        new Chart(document.getElementById('mini-product'),{type:'doughnut',data:{labels:['A','B','C'],datasets:[{data:[40,35,25],backgroundColor:['#14b8a6','#3b82f6','#f59e0b'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},cutout:'75%'}});
    });
</script>

<?php include 'includes/footer.php'; ?>
