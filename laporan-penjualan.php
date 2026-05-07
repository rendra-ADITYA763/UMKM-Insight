<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRole('client');

$user = getCurrentUser($pdo);
$userId = $user['id'];
$isPremium = $user['tier'] === 'premium';
$pageTitle = "Laporan Penjualan";
$activePage = 'laporan';

// --- DATA AGGREGATION (Tahap 4) ---
// 1. Fetch Income Transactions
$query = "SELECT * FROM transaction_cache WHERE user_id = ? AND type = 'Income' ORDER BY transaction_date DESC";
if (!$isPremium) {
    $query .= " LIMIT 10"; // Free users limited to 10 recent income transactions
}
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$salesTransactions = $stmt->fetchAll();

// 2. Stats
$stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM transaction_cache WHERE user_id = ? AND type = 'Income'");
$stmt->execute([$userId]);
$stats = $stmt->fetch();
$totalSalesCount = $stats['count'] ?: 0;
$totalRevenue = $stats['total'] ?: 0;
$avgOrder = $totalSalesCount > 0 ? $totalRevenue / $totalSalesCount : 0;

// 3. Chart Data (Aggregate by Date)
$stmt = $pdo->prepare("
    SELECT DATE(transaction_date) as t_date, SUM(amount) as total 
    FROM transaction_cache 
    WHERE user_id = ? AND type = 'Income' 
    GROUP BY DATE(transaction_date) 
    ORDER BY t_date ASC 
    LIMIT 30
");
$stmt->execute([$userId]);
$chartRows = $stmt->fetchAll();

$chartLabels = [];
$chartValues = [];
foreach ($chartRows as $row) {
    $chartLabels[] = date('d M', strtotime($row['t_date']));
    $chartValues[] = (float)$row['total'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="animate-fade-in" style="padding-top: 24px;">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">Laporan Penjualan</h1>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    Analisis pendapatan dan tren penjualan 
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-100">
                        <i class="ph-fill ph-bank"></i> SmartBank Connected
                    </span>
                </p>
            </div>
            <div class="flex gap-2">
                <?php if($isPremium): ?>
                    <button class="btn btn-outline btn-sm"><i class="ph ph-download-simple"></i> Export CSV</button>
                <?php endif; ?>
                <button onclick="location.reload()" class="btn btn-primary btn-sm"><i class="ph ph-arrows-clockwise"></i> Refresh Data</button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card p-5 mb-6 flex flex-wrap gap-4 items-end">
            <div class="form-group flex-1 min-w-[150px]">
                <label class="form-label">Periode Mulai</label>
                <input type="date" class="form-input" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
            </div>
            <div class="form-group flex-1 min-w-[150px]">
                <label class="form-label">Periode Akhir</label>
                <input type="date" class="form-input" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group flex-1 min-w-[150px]">
                <label class="form-label">Tipe Transaksi</label>
                <select class="form-select">
                    <option>Semua Transaksi</option>
                    <option>Penjualan POS</option>
                    <option>Marketplace</option>
                </select>
            </div>
            <button class="btn btn-outline btn-sm px-6">Terapkan Filter</button>
        </div>

        <!-- KPI Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card p-5 border-l-4 border-emerald-500">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
                    <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600"><i class="ph ph-wallet"></i></div>
                </div>
                <div class="text-xl font-bold text-slate-800"><?php echo formatRupiah($totalRevenue); ?></div>
                <div class="text-[10px] mt-2 font-medium text-emerald-600">Kumulatif</div>
            </div>
            <div class="card p-5 border-l-4 border-blue-500">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</span>
                    <div class="p-2 rounded-lg bg-blue-50 text-blue-600"><i class="ph ph-receipt"></i></div>
                </div>
                <div class="text-xl font-bold text-slate-800"><?php echo $totalSalesCount; ?> Pesanan</div>
                <div class="text-[10px] mt-2 font-medium text-blue-600">Update Hari Ini</div>
            </div>
            <div class="card p-5 border-l-4 border-amber-500">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Pesanan</span>
                    <div class="p-2 rounded-lg bg-amber-50 text-amber-600"><i class="ph ph-chart-bar"></i></div>
                </div>
                <div class="text-xl font-bold text-slate-800"><?php echo formatRupiah($avgOrder); ?></div>
                <div class="text-[10px] mt-2 font-medium text-amber-600">Per Transaksi</div>
            </div>
        </div>

        <!-- Main Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 card p-6">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="ph ph-chart-line text-brand-600"></i> Tren Pendapatan 
                    <?php if(!$isPremium): ?>
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded ml-2">Terbatas (3 Bulan)</span>
                    <?php endif; ?>
                </h3>
                <div style="height: 300px;">
                    <canvas id="chart-sales-main"></canvas>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="ph ph-chart-pie text-brand-600"></i> Sumber Penjualan
                </h3>
                <div style="height: 220px;">
                    <canvas id="chart-sales-source"></canvas>
                </div>
                <div class="mt-6 space-y-2" id="source-legend">
                    <!-- Legend content -->
                </div>
            </div>
        </div>

        <!-- Premium Feature Section -->
        <div class="card p-8 mb-6 relative overflow-hidden <?php echo !$isPremium ? 'premium-locked' : ''; ?>">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="ph ph-lightning text-amber-500"></i> Analisis Pertumbuhan Lanjutan 
                    <span class="badge badge-premium">PRO</span>
                </h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Growth Month-over-Month</p>
                    <p class="text-2xl font-bold text-emerald-600">+14.2%</p>
                    <p class="text-[10px] text-slate-500 mt-1">Meningkat pesat di kategori Makanan</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Customer Retention</p>
                    <p class="text-2xl font-bold text-brand-600">68%</p>
                    <p class="text-[10px] text-slate-500 mt-1">Repeat order via Marketplace</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Average Order Value</p>
                    <p class="text-2xl font-bold text-slate-800">Rp 42.500</p>
                    <p class="text-[10px] text-slate-500 mt-1">Turun 2% dari periode sebelumnya</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Peak Sales Hour</p>
                    <p class="text-2xl font-bold text-amber-600">18:00 - 20:00</p>
                    <p class="text-[10px] text-slate-500 mt-1">Waktu paling ramai transaksi POS</p>
                </div>
            </div>

            <?php if(!$isPremium): ?>
                <div class="premium-lock-badge" onclick="window.location.href='landing.php#harga'">
                    <i class="ph-fill ph-crown"></i> Upgrade ke Premium untuk akses Wawasan Lanjutan
                </div>
            <?php endif; ?>
        </div>

        <!-- Transaction List -->
        <div class="card overflow-hidden mb-12">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold">Daftar Transaksi Lengkap</h3>
                <span class="text-[10px] text-slate-400">Menampilkan <?php echo $isPremium ? 'Seluruh' : '10 Transaksi Terakhir (Free)'; ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Tanggal</th><th>Tipe</th><th>Sumber</th><th class="text-right">Nominal</th><th>Status</th></tr>
                    </thead>
                    <tbody id="trx-body-full">
                        <?php if(empty($salesTransactions)): ?>
                            <tr><td colspan="6" class="text-center py-20 text-slate-400">Tidak ada data penjualan ditemukan.</td></tr>
                        <?php else: ?>
                            <?php foreach($salesTransactions as $t): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="font-mono text-[10px] text-slate-400"><?php echo $t['external_id']; ?></td>
                                    <td class="text-xs text-slate-500"><?php echo date('d M Y, H:i', strtotime($t['transaction_date'])); ?></td>
                                    <td class="font-bold text-slate-700"><?php echo $t['type']; ?></td>
                                    <td><span class="badge badge-neutral"><?php echo $t['source']; ?></span></td>
                                    <td class="text-right font-bold text-emerald-500">+ <?php echo formatRupiah($t['amount']); ?></td>
                                    <td><span class="badge badge-success">Success</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(!$isPremium): ?>
                <div class="p-4 text-center bg-slate-50 border-t border-slate-100">
                    <button class="btn btn-ghost btn-sm text-brand-600 font-bold" onclick="showToast('Hanya akun Premium yang dapat melihat sejarah transaksi lengkap', 'info')">
                        <i class="ph ph-lock-key"></i> Tampilkan Lebih Banyak (Hanya Premium)
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isPremium = <?php echo $isPremium ? 'true' : 'false'; ?>;

        // --- TREND CHART (Tahap 4) ---
        const ctxSales = document.getElementById('chart-sales-main').getContext('2d');
        const labels = <?php echo json_encode($chartLabels); ?>;
        const dataValues = <?php echo json_encode($chartValues); ?>;
        
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No Data'],
                datasets: [{
                    label: 'Pendapatan',
                    data: dataValues.length ? dataValues : [0],
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Doughnut Source (Static for Demo)
        new Chart(document.getElementById('chart-sales-source'), {
            type: 'doughnut',
            data: {
                labels: ['POS', 'Marketplace', 'Lainnya'],
                datasets: [{
                    data: [35, 55, 10],
                    backgroundColor: ['#0d9488', '#3b82f6', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: { cutout: '75%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Legend
        const legendColors = ['#0d9488', '#3b82f6', '#f59e0b'];
        const legendLabels = ['POS (Kasir Toko)', 'Marketplace Online', 'Channel Lainnya'];
        document.getElementById('source-legend').innerHTML = legendLabels.map((l, i) => `
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background:${legendColors[i]}"></span>
                    <span class="text-slate-600">${l}</span>
                </div>
                <span class="font-bold text-slate-800">${[35, 55, 10][i]}%</span>
            </div>
        `).join('');
    });
</script>
</script>

<?php include 'includes/footer.php'; ?>
