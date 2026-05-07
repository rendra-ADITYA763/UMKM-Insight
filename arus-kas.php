<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRole('client');

$user = getCurrentUser($pdo);
$userId = $user['id'];
$isPremium = $user['tier'] === 'premium';
$pageTitle = "Arus Kas (Cashflow)";
$activePage = 'arus-kas';

// --- DATA AGGREGATION (Tahap 4) ---
// 1. Total In (Income)
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transaction_cache WHERE user_id = ? AND type = 'Income'");
$stmt->execute([$userId]);
$totalIn = $stmt->fetchColumn() ?: 0;

// 2. Total Out (Expense)
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transaction_cache WHERE user_id = ? AND type = 'Expense'");
$stmt->execute([$userId]);
$totalOut = $stmt->fetchColumn() ?: 0;

// 3. Net Cash
$netCash = $totalIn - $totalOut;

// 4. Daily Chart Data (Last 7 Days)
$stmt = $pdo->prepare("
    SELECT DATE(transaction_date) as t_date, 
           SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as income,
           SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as expense
    FROM transaction_cache 
    WHERE user_id = ? AND transaction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(transaction_date)
    ORDER BY t_date ASC
");
$stmt->execute([$userId]);
$dailyRows = $stmt->fetchAll();

$chartLabels = [];
$incomeValues = [];
$expenseValues = [];
foreach ($dailyRows as $row) {
    $chartLabels[] = date('d M', strtotime($row['t_date']));
    $incomeValues[] = (float)$row['income'];
    $expenseValues[] = (float)$row['expense'];
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
                <h1 class="text-2xl font-extrabold tracking-tight">Arus Kas (Cashflow)</h1>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    Aliran kas masuk & keluar bisnis Anda 
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-100">
                        <i class="ph-fill ph-bank"></i> SmartBank Integrated
                    </span>
                </p>
            </div>
            <button onclick="location.reload()" class="btn btn-outline btn-sm"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6" id="kpi-cashflow">
            <!-- Simulated via JS -->
        </div>

        <!-- Daily Trend (Free) -->
        <div class="card p-6 mb-6">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="ph ph-chart-line text-emerald-600"></i> Tren Arus Kas Harian (7 Hari Terakhir)
            </h3>
            <div style="height: 320px;">
                <canvas id="chart-cashflow-daily"></canvas>
            </div>
        </div>

        <!-- Premium Analysis Section -->
        <div class="relative <?php echo !$isPremium ? 'premium-locked' : ''; ?>">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="card p-6">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="ph ph-chart-bar text-indigo-600"></i> Perbandingan Kas Bulanan <span class="badge badge-premium">PRO</span>
                    </h3>
                    <div style="height: 280px;">
                        <canvas id="chart-cashflow-monthly"></canvas>
                    </div>
                </div>
                <div class="card p-6">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="ph ph-crystal-ball text-purple-600"></i> Proyeksi Saldo Masa Depan <span class="badge badge-premium">PRO</span>
                    </h3>
                    <div style="height: 280px;">
                        <canvas id="chart-cashflow-forecast"></canvas>
                    </div>
                </div>
            </div>

            <div class="card p-6 mb-6">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="ph ph-stack text-amber-500"></i> Analisis Kategori Pengeluaran <span class="badge badge-premium">PRO</span>
                </h3>
                <div class="space-y-4" id="cashflow-categories">
                    <!-- Progress bars for categories -->
                </div>
            </div>

            <?php if(!$isPremium): ?>
                <div class="premium-lock-badge" onclick="window.location.href='landing.php#harga'">
                    <i class="ph-fill ph-crown"></i> Akses Analisis Arus Kas Lanjutan (Premium Only)
                </div>
            <?php endif; ?>
        </div>

        <?php if($isPremium): ?>
            <div class="flex justify-end mb-12">
                <button class="btn btn-primary"><i class="ph ph-file-pdf"></i> Download Laporan Cashflow (PDF)</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isPremium = <?php echo $isPremium ? 'true' : 'false'; ?>;
        const cfData = generateCashflowData();

        // Render KPI
        const netCash = <?php echo $netCash; ?>;
        const totalIn = <?php echo $totalIn; ?>;
        const totalOut = <?php echo $totalOut; ?>;
        const netPos = netCash >= 0;

        document.getElementById('kpi-cashflow').innerHTML = [
            {l:'Kas Bersih',v:formatRupiah(netCash),icon:'ph-scales',bg:netPos?'#ecfdf5':'#fef2f2',c:netPos?'#059669':'#dc2626',t:netPos?' Surplus':' Defisit'},
            {l:'Total Masuk',v:formatRupiah(totalIn),icon:'ph-arrow-circle-down',bg:'#ecfdf5',c:'#059669',t:'Kumulatif'},
            {l:'Total Keluar',v:formatRupiah(totalOut),icon:'ph-arrow-circle-up',bg:'#fef2f2',c:'#dc2626',t:'Kumulatif'}
        ].map(k=>`<div class="card p-5 border-b-4" style="border-bottom-color:${k.c}">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">${k.l}</span>
                <div class="p-2 rounded-lg" style="background:${k.bg};color:${k.c}"><i class="ph ${k.icon}"></i></div>
            </div>
            <div class="text-xl font-bold text-slate-800">${k.v}</div>
            <div class="text-[10px] mt-2 font-medium" style="color:${k.c}"><i class="ph ph-info"></i> Status: ${k.t}</div>
        </div>`).join('');

        // Daily Chart (Tahap 4)
        const labels = <?php echo json_encode($chartLabels); ?>;
        const income = <?php echo json_encode($incomeValues); ?>;
        const expense = <?php echo json_encode($expenseValues); ?>;

        new Chart(document.getElementById('chart-cashflow-daily'), {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No Data'],
                datasets: [
                    { label: 'Masuk', data: income.length ? income : [0], borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4 },
                    { label: 'Keluar', data: expense.length ? expense : [0], borderColor: '#f43f5e', backgroundColor: 'rgba(244, 63, 94, 0.05)', fill: true, tension: 0.4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6 } } },
                scales: { 
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });

        if (isPremium) {
            // Monthly
            new Chart(document.getElementById('chart-cashflow-monthly'), {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','Mei','Jun'],
                    datasets: [
                        { label: 'Masuk', data: [32e6,35e6,38e6,40e6,42e6,45e6], backgroundColor: '#10b981', borderRadius: 4 },
                        { label: 'Keluar', data: [20e6,22e6,25e6,24e6,26e6,28e6], backgroundColor: '#f43f5e', borderRadius: 4 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });

            // Forecast
            new Chart(document.getElementById('chart-cashflow-forecast'), {
                type: 'line',
                data: {
                    labels: ['Minggu 1','Minggu 2','Minggu 3','Minggu 4'],
                    datasets: [{ label: 'Proyeksi Saldo', data: [11e6,12.5e6,13e6,14.5e6], borderColor: '#8b5cf6', borderDash: [5,5], tension: 0.3, fill: false }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Categories
            const cats = [
                {l:'Penjualan Produk', v:35000000, p:100, c:'#10b981'},
                {l:'Pembelian Stok', v:15000000, p:42, c:'#f43f5e'},
                {l:'Biaya Operasional', v:5000000, p:14, c:'#f59e0b'},
                {l:'Fee Marketplace', v:1500000, p:4, c:'#3b82f6'},
                {l:'Pajak Sistem', v:900000, p:2, c:'#6366f1'}
            ];
            document.getElementById('cashflow-categories').innerHTML = cats.map(ct => `
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-bold text-slate-700">${ct.l}</span>
                        <span class="text-slate-500">${formatRupiah(ct.v)} (${ct.p}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:${ct.p}%; background:${ct.c}"></div>
                    </div>
                </div>
            `).join('');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
