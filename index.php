<?php
require_once 'includes/auth.php';
$pageTitle = "Wawasan Bisnis UMKM Berbasis Data";
$activePage = 'landing';
include 'includes/header.php';
?>

<style>
    .landing-nav{position:fixed;top:0;left:0;right:0;z-index:50;transition:all .3s;}
    .landing-nav.scrolled{background:rgba(255,255,255,0.95);backdrop-filter:blur(12px);box-shadow:var(--shadow-md);border-bottom:1px solid var(--border);}
    .landing-nav .nav-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:16px 24px;}
    .landing-nav .brand{display:flex;align-items:center;gap:10px;text-decoration:none;}
    .landing-nav .brand i{font-size:1.75rem;color:var(--brand-400);}
    .landing-nav .brand span{font-weight:800;font-size:1.25rem;color:white;letter-spacing:-0.02em;}
    .landing-nav.scrolled .brand span{color:var(--text-primary);}
    .landing-nav.scrolled .brand i{color:var(--brand-600);}
    .landing-nav .nav-links{display:flex;gap:8px;}
    .landing-nav .nav-links a{color:rgba(255,255,255,0.8);text-decoration:none;font-size:.875rem;font-weight:500;padding:8px 16px;border-radius:var(--radius-lg);transition:all .2s;}
    .landing-nav .nav-links a:hover{background:rgba(255,255,255,0.15);color:white;}
    .landing-nav.scrolled .nav-links a{color:var(--text-secondary);}
    .landing-nav.scrolled .nav-links a:hover{background:var(--surface-alt);color:var(--text-primary);}
    .landing-nav .nav-links .btn-primary{
        background: #0d9488 !important;
        color: white !important;
        padding: 8px 20px !important;
        opacity: 1 !important;
        display: inline-flex !important;
    }
    .landing-nav .nav-links .btn-primary:hover{
        background: #0f766e !important;
        transform: translateY(-1px);
    }

    .hero{min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:120px 24px 80px;position:relative;overflow:hidden;}
    .hero::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,#0f172a 0%,#134e4a 40%,#0d9488 100%);z-index:0;}
    .hero-content{position:relative;z-index:2;max-width:800px;}
    .hero h1{font-size:3.5rem;font-weight:800;color:white;line-height:1.15;letter-spacing:-0.03em;margin-bottom:20px;}
    .hero h1 .highlight{background:linear-gradient(135deg,var(--brand-300),var(--brand-400));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .hero p{font-size:1.125rem;color:rgba(255,255,255,0.7);max-width:600px;margin:0 auto 36px;line-height:1.7;}
    
    .section{padding:100px 24px;max-width:1200px;margin:0 auto;}
    .pricing-card.featured{border-color:var(--brand-500);position:relative;background:linear-gradient(180deg,var(--brand-50) 0%,white 40%);}
    .pricing-card.featured::before{content:'POPULER';position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--brand-600);color:white;padding:4px 16px;border-radius:999px;font-size:.6875rem;font-weight:700;letter-spacing:.1em;}
</style>

<nav class="landing-nav" id="landing-nav">
    <div class="nav-inner">
        <a href="#" class="brand"><i class="ph-fill ph-chart-polar"></i><span>UMKM Insight</span></a>
        <div class="nav-links">
            <a href="#fitur">Fitur</a>
            <a href="#harga">Harga</a>
            <a href="login.php" class="btn btn-primary btn-sm">Masuk</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="hero-content animate-fade-in">
        <h1>Wawasan Bisnis UMKM, <span class="highlight">Berbasis Data</span></h1>
        <p>Analitik penjualan, arus kas, dan performa produk Anda — langsung dari data transaksi SmartBank. Ambil keputusan bisnis yang lebih cerdas.</p>
        <div class="flex gap-4 justify-center flex-wrap">
            <a href="register.php" class="btn btn-primary btn-lg px-10 py-4 text-lg shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300"><i class="ph ph-rocket-launch"></i> Daftar Gratis</a>
            <a href="login.php" class="btn btn-lg px-10 py-4 text-lg shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300" style="background-color: white !important; color: #0d9488 !important; border: none;"><i class="ph ph-sign-in"></i> Masuk</a>
        </div>
    </div>
</section>

<section class="section" id="fitur">
    <div class="text-center mb-16">
        <span class="text-xs font-bold text-brand-600 uppercase tracking-widest">Fitur Unggulan</span>
        <h2 class="text-3xl font-extrabold mt-3">Semua yang UMKM Anda Butuhkan</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="card p-8 hover:-translate-y-2 transition-transform">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mb-6"><i class="ph-fill ph-trend-up"></i></div>
            <h3 class="text-lg font-bold mb-3">Laporan Penjualan</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Pantau tren pendapatan harian hingga bulanan. Identifikasi kanal penjualan paling produktif untuk bisnis Anda.</p>
        </div>
        <div class="card p-8 hover:-translate-y-2 transition-transform">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mb-6"><i class="ph-fill ph-money"></i></div>
            <h3 class="text-lg font-bold mb-3">Arus Kas (Cashflow)</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Pahami aliran dana masuk dan keluar secara mendalam. Pantau pengeluaran stok, operasional, hingga pajak.</p>
        </div>
        <div class="card p-8 hover:-translate-y-2 transition-transform">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-6"><i class="ph-fill ph-package"></i></div>
            <h3 class="text-lg font-bold mb-3">Performa Produk</h3>
            <h3 class="text-lg font-bold mb-3">Performa Produk</h3>
            <p class="text-sm text-slate-500 leading-relaxed">Ketahui produk "Star" dan "Slow Mover" Anda. Optimalkan stok berdasarkan data preferensi konsumen nyata.</p>
        </div>
    </div>
</section>

<section class="section bg-slate-50" id="harga" style="max-width: 100%;">
    <div class="max-w-[1200px] mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold">Paket Langganan</h2>
            <p class="text-slate-500 mt-2">Mulai gratis, upgrade kapan saja untuk fitur profesional.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <div class="card p-10 flex flex-col">
                <h3 class="text-xl font-bold">Free Tier</h3>
                <div class="text-3xl font-black my-4">Rp 0<span class="text-sm font-normal text-slate-400">/bulan</span></div>
                <ul class="space-y-4 mb-10 flex-1">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Dashboard Dasar</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Laporan 3 Bulan Terakhir</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Top 5 Produk Terlaris</li>
                    <li class="flex items-center gap-2 text-sm text-slate-300"><i class="ph ph-lock text-slate-300"></i> Export Laporan CSV</li>
                </ul>
                <a href="register.php" class="btn btn-outline btn-full py-4 font-bold">Mulai Gratis</a>
            </div>
            <div class="card p-10 flex flex-col featured border-brand-500">
                <h3 class="text-xl font-bold text-brand-700">Premium Pro</h3>
                <div class="text-3xl font-black my-4">Rp 99K<span class="text-sm font-normal text-slate-400">/bulan</span></div>
                <ul class="space-y-4 mb-10 flex-1">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Semua Fitur Free</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Laporan Sejarah Tanpa Batas</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Proyeksi Arus Kas Mendatang</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Analisis Pertumbuhan Lanjutan</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="ph-fill ph-check-circle text-brand-500"></i> Export CSV & PDF</li>
                </ul>
                <a href="register.php" class="btn btn-primary btn-full py-4 font-bold shadow-lg shadow-brand-200">Beralih ke Pro</a>
            </div>
        </div>
    </div>
</section>

<footer class="bg-slate-900 text-white py-16 px-6 text-center">
    <div class="flex justify-center gap-8 mb-8 text-sm text-slate-400">
        <a href="#fitur" class="hover:text-white transition-colors">Fitur</a>
        <a href="#harga" class="hover:text-white transition-colors">Harga</a>
        <a href="login.php" class="hover:text-white transition-colors">Masuk</a>
        <a href="register.php" class="hover:text-white transition-colors">Daftar</a>
    </div>
    <p class="text-slate-500 text-xs">&copy; <?php echo date('Y'); ?> Ekosistem Ekonomi UMKM. Simulasi Sistem Informasi RPL 2.</p>
</footer>

<script>
    window.addEventListener('scroll', () => {
        document.getElementById('landing-nav').classList.toggle('scrolled', window.scrollY > 50);
    });
</script>

<?php include 'includes/footer.php'; ?>
