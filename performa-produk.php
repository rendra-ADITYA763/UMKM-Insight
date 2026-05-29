<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRole('client');

$user = getCurrentUser($pdo);
$userId = $user['id'];
$isPremium = $user['tier'] === 'premium';
$pageTitle = "Performa Produk";
$activePage = 'performa';

// --- DATA AGGREGATION (Tahap 4) ---
// 1. Fetch Product Ranking & Performance
$stmt = $pdo->prepare("
    SELECT p.*, 
           COUNT(tc.id) as total_sold, 
           SUM(tc.amount) as total_revenue
    FROM products p
    LEFT JOIN transaction_cache tc ON p.id = tc.product_id AND tc.type = 'Income'
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY total_revenue DESC
");
$stmt->execute([$userId]);
$allProducts = $stmt->fetchAll();

// 2. Stats for KPIs
$totalUnitSold = 0;
$totalProductValue = 0;
foreach ($allProducts as $p) {
    $totalUnitSold += $p['total_sold'];
    $totalProductValue += $p['total_revenue'];
}

// 3. Category Distribution
$stmt = $pdo->prepare("SELECT kategori, COUNT(*) as count FROM products WHERE user_id = ? GROUP BY kategori");
$stmt->execute([$userId]);
$categoryRows = $stmt->fetchAll();

$catLabels = [];
$catCounts = [];
foreach ($categoryRows as $row) {
    $catLabels[] = $row['kategori'];
    $catCounts[] = (int)$row['count'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content animated-bg">
    <?php include 'includes/topbar.php'; ?>

    <div class="animate-fade-in stagger-1" style="padding-top: 24px; padding-left: 24px; padding-right: 24px;">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6 flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 animate-pop-in drop-shadow-sm mb-1">
                    Performa Produk
                </h1>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2 animate-pop-in stagger-1">
                    Analisis penjualan & tren produk terpopuler Anda 
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[10px] font-bold border border-blue-200 dark:border-blue-800/50">
                        <i class="ph-fill ph-storefront"></i> Marketplace & POS Data
                    </span>
                </p>
            </div>
            <button onclick="location.reload()" class="btn bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all btn-sm animate-pop-in stagger-2"><i class="ph-bold ph-arrows-clockwise"></i> Refresh</button>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6" id="kpi-product">
            <!-- TOTAL TERJUAL -->
            <div class="glass-card animate-pop-in stagger-3 p-6 bg-gradient-to-br from-blue-500 to-indigo-600 text-white border-0 shadow-lg relative overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-white rounded-full mix-blend-overlay opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-white/20 backdrop-blur-md shadow-inner text-white"><i class="ph-fill ph-shopping-bag text-2xl"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-blue-100 uppercase tracking-wider mb-1">Total Terjual</p>
                        <p class="text-3xl font-black tracking-tight drop-shadow-md"><?php echo $totalUnitSold; ?> <span class="text-sm font-bold opacity-80">Unit</span></p>
                    </div>
                </div>
            </div>

            <!-- NILAI PRODUK -->
            <div class="glass-card animate-pop-in stagger-4 p-6 bg-gradient-to-br from-emerald-400 to-teal-600 text-white border-0 shadow-lg relative overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-white rounded-full mix-blend-overlay opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-white/20 backdrop-blur-md shadow-inner text-white"><i class="ph-fill ph-currency-circle-dollar text-2xl"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-emerald-100 uppercase tracking-wider mb-1">Nilai Produk (Estimasi)</p>
                        <p class="text-2xl font-black tracking-tight drop-shadow-md"><?php echo formatRupiah($totalProductValue); ?></p>
                    </div>
                </div>
            </div>

            <!-- STOK TERSEDIA -->
            <div class="glass-card animate-pop-in stagger-5 p-6 bg-gradient-to-br from-amber-400 to-orange-500 text-white border-0 shadow-lg relative overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-white rounded-full mix-blend-overlay opacity-20 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-white/20 backdrop-blur-md shadow-inner text-white"><i class="ph-fill ph-package text-2xl"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-amber-100 uppercase tracking-wider mb-1">Stok Tersedia</p>
                        <p class="text-3xl font-black tracking-tight drop-shadow-md"><?php echo array_sum(array_column($allProducts, "stok")); ?> <span class="text-sm font-bold opacity-80">Item</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Product Section (Free/Basic) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="glass-card animate-pop-in stagger-5 p-6">
                <h3 class="font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600"><i class="ph-bold ph-chart-bar"></i></div>
                    Top 5 Produk Terlaris (Unit)
                </h3>
                <div style="height: 280px; position: relative;">
                    <canvas id="chart-product-top"></canvas>
                </div>
            </div>
            <div class="glass-card animate-pop-in stagger-6 p-6 flex flex-col">
                <h3 class="font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600"><i class="ph-bold ph-chart-pie"></i></div>
                    Distribusi Kategori Produk
                </h3>
                <div style="height: 220px; position: relative;" class="flex-1">
                    <canvas id="chart-product-category"></canvas>
                </div>
            </div>
        </div>

        <!-- Product Ranking List -->
        <div class="glass-card animate-pop-in stagger-6 overflow-hidden flex flex-col mb-6">
            <div class="p-5 border-b border-white/40 dark:border-slate-700/40 bg-white/20 dark:bg-slate-800/20 backdrop-blur-md flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">Peringkat Produk</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-1">Berdasarkan volume penjualan kumulatif</p>
                </div>
            </div>
            <div class="divide-y divide-white/30 dark:divide-slate-700/30" id="product-ranking-list">
                <?php 
                    $displayProducts = $isPremium ? $allProducts : array_slice($allProducts, 0, 5);
                ?>
                <?php foreach($displayProducts as $i => $p): ?>
                    <div class="flex items-center p-4 hover:bg-white/60 dark:hover:bg-slate-700/60 transition-colors">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs <?php echo $i<3 ? 'bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-md' : 'bg-slate-200/50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300'; ?> mr-4"><?php echo $i+1; ?></div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200"><?php echo $p['nama_produk']; ?></p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium"><?php echo $p['kategori']; ?> · Stok: <?php echo $p['stok']; ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200"><?php echo $p['total_sold']; ?> unit</p>
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold"><?php echo formatRupiah($p['total_revenue'] ?: 0); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if(!$isPremium): ?>
                <div class="p-4 text-center bg-slate-50/50 dark:bg-slate-800/50 backdrop-blur-sm">
                    <div class="premium-lock-badge cursor-pointer inline-flex w-auto mx-auto px-6 py-2 shadow-sm rounded-full" style="position:relative; transform:none;" onclick="window.location.href='landing.php#harga'">
                        <i class="ph-bold ph-crown mr-2"></i> Lihat Seluruh Daftar Produk (Premium Only)
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Premium Analysis Section -->
        <div class="glass-card animate-pop-in stagger-7 p-8 mb-12 relative overflow-hidden <?php echo !$isPremium ? 'premium-locked' : ''; ?>">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply dark:mix-blend-screen opacity-5 dark:opacity-10 pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 relative z-10">
                <div class="lg:col-span-2 p-5 rounded-2xl bg-white/60 dark:bg-slate-800/60 border border-white/80 dark:border-slate-700/80 backdrop-blur-md">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-brand-100 dark:bg-brand-900/50 text-brand-600"><i class="ph-bold ph-chart-line-up"></i></div>
                        Tren Penjualan Produk Unggulan 
                        <span class="badge bg-brand-100 dark:bg-brand-900/50 text-brand-800 dark:text-brand-400 border-brand-300 dark:border-brand-700 ml-2 shadow-sm">PRO</span>
                    </h3>
                    <div style="height: 300px; position: relative;">
                        <canvas id="chart-product-trend"></canvas>
                    </div>
                </div>
                <div class="p-5 rounded-2xl bg-white/60 dark:bg-slate-800/60 border border-white/80 dark:border-slate-700/80 backdrop-blur-md">
                    <h3 class="font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600"><i class="ph-bold ph-lightning"></i></div>
                        Smart Insights 
                        <span class="badge bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-400 border-purple-300 dark:border-purple-700 ml-2 shadow-sm">PRO</span>
                    </h3>
                    <div class="space-y-4" id="product-insights">
                        <!-- Insights rendered via JS -->
                    </div>
                </div>
            </div>

            <?php if(!$isPremium): ?>
                <div class="premium-lock-badge" onclick="window.location.href='landing.php#harga'">
                    <i class="ph-fill ph-crown text-2xl mb-2"></i>
                    <span class="font-bold">Buka Wawasan Produk Lanjutan</span>
                </div>
            <?php endif; ?>
        </div>
        
        <footer class="mt-8 mb-4 text-center text-[11px] text-slate-400 py-6 border-t border-slate-100 dark:border-slate-800">
            &copy; <?php echo date('Y'); ?> Ekosistem Ekonomi UMKM. Simulasi Sistem Informasi RPL 2.
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isPremium = <?php echo $isPremium ? 'true' : 'false'; ?>;
        
        // Mock data generator since the original used one
        const generateProductData = () => ({
            trend: {
                datasets: [
                    { label: 'Produk A', data: [120, 150, 180, 220, 260, 310] },
                    { label: 'Produk B', data: [200, 190, 180, 195, 185, 170] },
                    { label: 'Produk C', data: [50, 80, 100, 150, 200, 280] }
                ]
            },
            products: [
                { name: 'Kopi Susu Gula Aren', revenue: 15000000 },
                { name: 'Keripik Singkong Pedas', revenue: 8000000 }
            ]
        });
        const pData = generateProductData();
        
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';

        // Top 5 Chart (Tahap 4)
        <?php 
            $top5 = array_slice($allProducts, 0, 5);
            $top5Labels = array_column($top5, 'nama_produk');
            $top5Values = array_column($top5, 'total_sold');
        ?>
        const ctxTop = document.getElementById('chart-product-top').getContext('2d');
        let gradBar = ctxTop.createLinearGradient(0, 0, 400, 0);
        gradBar.addColorStop(0, 'rgba(99, 102, 241, 0.8)'); // indigo-500
        gradBar.addColorStop(1, 'rgba(139, 92, 246, 0.8)'); // purple-500

        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($top5Labels); ?>,
                datasets: [{ 
                    label: 'Unit Terjual', 
                    data: <?php echo json_encode($top5Values); ?>, 
                    backgroundColor: gradBar, 
                    borderRadius: 6, 
                    barThickness: 20,
                    hoverBackgroundColor: 'rgba(99, 102, 241, 1)'
                }]
            },
            options: { 
                indexAxis: 'y', 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false },
                    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8 }
                }, 
                scales: { 
                    x: { grid: { borderDash: [5,5], color: 'rgba(148, 163, 184, 0.1)' }, border: {display: false} }, 
                    y: { grid: { display: false }, border: {display: false}, ticks: { font: { weight: 'bold' } } } 
                } 
            }
        });

        // Category Chart (Tahap 4)
        new Chart(document.getElementById('chart-product-category'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($catLabels); ?>,
                datasets: [{ 
                    data: <?php echo json_encode($catCounts); ?>, 
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#f43f5e'], 
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { 
                cutout: '75%', 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8, padding: 15 } },
                    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8 }
                } 
            }
        });

        if (isPremium) {
            // Trend Chart
            new Chart(document.getElementById('chart-product-trend'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: pData.trend.datasets.map((ds, i) => ({
                        label: ds.label, 
                        data: ds.data, 
                        borderColor: ['#10b981','#3b82f6','#f59e0b'][i], 
                        tension: 0.4, 
                        borderWidth: 3, 
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        backgroundColor: ['#10b981','#3b82f6','#f59e0b'][i]
                    }))
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 20 } },
                        tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8 }
                    },
                    scales: {
                        y: { grid: { borderDash: [5, 5], color: 'rgba(148, 163, 184, 0.1)' }, border: { display: false } },
                        x: { grid: { display: false }, border: { display: false } }
                    }
                }
            });

            // Insights
            const insights = [
                {l:'Paling Cuan', v:pData.products.sort((a,b)=>b.revenue-a.revenue)[0].name, c:'text-emerald-600', bg:'bg-emerald-100 dark:bg-emerald-900/50', i:'ph-medal'},
                {l:'Fast Growing', v:'Kopi Susu Gula Aren (+12%)', c:'text-blue-600', bg:'bg-blue-100 dark:bg-blue-900/50', i:'ph-trend-up'},
                {l:'Perlu Restock', v:'Keripik Singkong Pedas', c:'text-amber-600', bg:'bg-amber-100 dark:bg-amber-900/50', i:'ph-warning'}
            ];
            document.getElementById('product-insights').innerHTML = insights.map(ins => `
                <div class="p-4 rounded-xl bg-slate-50/50 dark:bg-slate-800/50 border border-slate-200/50 dark:border-slate-700/50 shadow-sm flex items-center gap-4 hover:bg-white dark:hover:bg-slate-700 transition-colors">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center ${ins.bg} ${ins.c}">
                        <i class="ph-fill ${ins.i} text-xl"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${ins.l}</span>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">${ins.v}</p>
                    </div>
                </div>
            `).join('');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
