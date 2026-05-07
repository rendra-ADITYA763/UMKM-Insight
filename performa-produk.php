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

<div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="animate-fade-in" style="padding-top: 24px;">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">Performa Produk</h1>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    Analisis penjualan & tren produk terpopuler Anda 
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold border border-blue-100">
                        <i class="ph-fill ph-storefront"></i> Marketplace & POS Data
                    </span>
                </p>
            </div>
            <button onclick="location.reload()" class="btn btn-outline btn-sm"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6" id="kpi-product">
            <!-- Simulated via JS -->
        </div>

        <!-- Main Product Section (Free/Basic) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card p-6">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="ph ph-chart-bar text-indigo-600"></i> Top 5 Produk Terlaris (Unit)
                </h3>
                <div style="height: 280px;">
                    <canvas id="chart-product-top"></canvas>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="ph ph-chart-pie text-amber-500"></i> Distribusi Kategori Produk
                </h3>
                <div style="height: 220px;">
                    <canvas id="chart-product-category"></canvas>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-2" id="category-stats">
                    <!-- Stats from JS -->
                </div>
            </div>
        </div>

        <!-- Product Ranking List -->
        <div class="card overflow-hidden mb-6">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold">Peringkat Produk</h3>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Berdasarkan volume penjualan kumulatif</p>
            </div>
            <div class="divide-y divide-slate-100" id="product-ranking-list">
                <!-- Rows from JS -->
            </div>
            <?php if(!$isPremium): ?>
                <div class="p-4 text-center bg-white">
                    <div class="premium-lock-badge" style="position:relative; transform:none;" onclick="window.location.href='landing.php#harga'">
                        <i class="ph-fill ph-crown"></i> Lihat Seluruh Daftar Produk (Premium Only)
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Premium Analysis Section -->
        <div class="relative <?php echo !$isPremium ? 'premium-locked' : ''; ?> mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 card p-6">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="ph ph-chart-line-up text-brand-600"></i> Tren Penjualan Produk Unggulan <span class="badge badge-premium">PRO</span>
                    </h3>
                    <div style="height: 300px;">
                        <canvas id="chart-product-trend"></canvas>
                    </div>
                </div>
                <div class="card p-6">
                    <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="ph ph-lightning text-purple-600"></i> Smart Insights <span class="badge badge-premium">PRO</span>
                    </h3>
                    <div class="space-y-4" id="product-insights">
                        <!-- Insights from JS -->
                    </div>
                </div>
            </div>

            <?php if(!$isPremium): ?>
                <div class="premium-lock-badge" onclick="window.location.href='landing.php#harga'">
                    <i class="ph-fill ph-crown"></i> Buka Wawasan Produk Lanjutan
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isPremium = <?php echo $isPremium ? 'true' : 'false'; ?>;
        const pData = generateProductData();

        // Render KPI
        document.getElementById('kpi-product').innerHTML = [
            {l:'Total Terjual', v:'<?php echo $totalUnitSold; ?> Unit', icon:'ph-shopping-bag', bg:'#eff6ff', c:'#2563eb'},
            {l:'Nilai Produk', v:'<?php echo formatRupiah($totalProductValue); ?>', icon:'ph-currency-circle-dollar', bg:'#ecfdf5', c:'#059669'},
            {l:'Stok Tersedia', v:'<?php echo array_sum(array_column($allProducts, "stok")); ?> Item', icon:'ph-package', bg:'#fffbeb', c:'#d97706'}
        ].map(k=>`<div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg" style="background:${k.bg};color:${k.c}"><i class="ph ${k.icon} text-xl"></i></div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">${k.l}</p>
                    <p class="text-lg font-bold text-slate-800">${k.v}</p>
                </div>
            </div>
        </div>`).join('');

        // Top 5 Chart (Tahap 4)
        <?php 
            $top5 = array_slice($allProducts, 0, 5);
            $top5Labels = array_column($top5, 'nama_produk');
            $top5Values = array_column($top5, 'total_sold');
        ?>
        new Chart(document.getElementById('chart-product-top'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($top5Labels); ?>,
                datasets: [{ label: 'Unit Terjual', data: <?php echo json_encode($top5Values); ?>, backgroundColor: '#6366f1', borderRadius: 6, barThickness: 24 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { borderDash: [5,5] } }, y: { grid: { display: false } } } }
        });

        // Category Chart (Tahap 4)
        new Chart(document.getElementById('chart-product-category'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($catLabels); ?>,
                datasets: [{ data: <?php echo json_encode($catCounts); ?>, backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#6366f1', '#f43f5e'], borderWidth: 0 }]
            },
            options: { cutout: '75%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // Ranking List (Tahap 4)
        <?php 
            $displayProducts = $isPremium ? $allProducts : array_slice($allProducts, 0, 5);
        ?>
        document.getElementById('product-ranking-list').innerHTML = `
            <?php foreach($displayProducts as $i => $p): ?>
                <div class="flex items-center p-4 hover:bg-slate-50 transition-colors">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs <?php echo $i<3 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'; ?> mr-4"><?php echo $i+1; ?></div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-800"><?php echo $p['nama_produk']; ?></p>
                        <p class="text-[10px] text-slate-400"><?php echo $p['kategori']; ?> · Stok: <?php echo $p['stok']; ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-800"><?php echo $p['total_sold']; ?> unit</p>
                        <p class="text-[10px] text-emerald-600 font-bold"><?php echo formatRupiah($p['total_revenue'] ?: 0); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        `;

        if (isPremium) {
            // Trend Chart
            new Chart(document.getElementById('chart-product-trend'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: pData.trend.datasets.map((ds, i) => ({
                        label: ds.label, data: ds.data, borderColor: ['#10b981','#3b82f6','#f59e0b'][i], tension: 0.4, borderWidth: 2, pointRadius: 3
                    }))
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6 } } } }
            });

            // Insights
            const insights = [
                {l:'Paling Cuan', v:pData.products.sort((a,b)=>b.revenue-a.revenue)[0].name, c:'text-emerald-600', i:'ph-medal'},
                {l:'Fast Growing', v:'Kopi Susu Gula Aren (+12%)', c:'text-blue-600', i:'ph-trend-up'},
                {l:'Perlu Restock', v:'Keripik Singkong Pedas', c:'text-amber-600', i:'ph-warning'}
            ];
            document.getElementById('product-insights').innerHTML = insights.map(ins => `
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="ph-fill ${ins.i} ${ins.c}"></i>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${ins.l}</span>
                    </div>
                    <p class="text-xs font-bold text-slate-700">${ins.v}</p>
                </div>
            `).join('');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
