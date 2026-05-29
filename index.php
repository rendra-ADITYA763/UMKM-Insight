<?php
require_once 'includes/auth.php';
$pageTitle = "Wawasan Bisnis UMKM Berbasis Data";
$activePage = 'landing';
include 'includes/header.php';
?>

<!-- Include AOS for rich scroll animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>
    /* Custom CSS for Landing Page */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.5);
    }
    .dark :root {
        --glass-bg: rgba(15, 23, 42, 0.7);
        --glass-border: rgba(30, 41, 59, 0.5);
    }
    
    .landing-nav { position:fixed; top:0; left:0; right:0; z-index:50; transition:all .5s cubic-bezier(0.4, 0, 0.2, 1); padding: 24px 0; }
    .landing-nav.scrolled { padding: 12px 0; background:rgba(255,255,255,0.8); backdrop-filter:blur(16px); box-shadow:0 4px 30px rgba(0,0,0,0.05); border-bottom:1px solid rgba(255,255,255,0.3); }
    .dark .landing-nav.scrolled { background:rgba(15, 23, 42, 0.8); border-bottom:1px solid rgba(255,255,255,0.05); }
    
    /* Glowing Orbs Background */
    .hero-bg {
        position: absolute;
        inset: 0;
        background: #0f172a;
        overflow: hidden;
        z-index: 0;
    }
    .orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.6;
        animation: float 10s infinite ease-in-out alternate;
    }
    .orb-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: #0d9488; animation-delay: 0s; }
    .orb-2 { bottom: -20%; right: -10%; width: 60vw; height: 60vw; background: #3b82f6; animation-delay: -5s; }
    .orb-3 { top: 40%; left: 50%; width: 40vw; height: 40vw; background: #8b5cf6; transform: translate(-50%, -50%); animation-delay: -2s; }
    
    @keyframes float {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(5%, 5%) scale(1.1); }
    }

    .hero-content {
        position: relative;
        z-index: 10;
    }
    
    /* Glass Feature Cards */
    .feature-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        padding: 40px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .dark .feature-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .feature-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .dark .feature-card:hover {
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }

    /* Animated Gradient Text */
    .text-gradient-animate {
        background: linear-gradient(to right, #2dd4bf, #3b82f6, #a855f7, #2dd4bf);
        background-size: 200% auto;
        color: transparent;
        -webkit-background-clip: text;
        background-clip: text;
        animation: gradientText 5s linear infinite;
    }
    @keyframes gradientText {
        to { background-position: 200% center; }
    }

    /* Dashboard Mockup */
    .dashboard-mockup {
        position: relative;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(15,23,42,0.8);
        backdrop-filter: blur(20px);
        overflow: hidden;
        transform: perspective(1000px) rotateX(5deg) scale(0.95);
        transition: transform 0.5s;
    }
    .dashboard-mockup:hover {
        transform: perspective(1000px) rotateX(0deg) scale(1);
    }
    
    /* Pricing PRO Card */
    .pricing-pro {
        position: relative;
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.6));
        border: 2px solid transparent;
        background-clip: padding-box;
    }
    .dark .pricing-pro {
        background: linear-gradient(135deg, rgba(30,41,59,0.9), rgba(15,23,42,0.8));
    }
    .pricing-pro::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
        z-index: -1;
        animation: borderGlow 3s linear infinite;
    }
    @keyframes borderGlow {
        0%, 100% { filter: hue-rotate(0deg); }
        50% { filter: hue-rotate(180deg); }
    }
</style>

<nav class="landing-nav" id="landing-nav">
    <div class="max-w-[1200px] mx-auto px-6 flex items-center justify-between">
        <a href="#" class="flex items-center gap-3 text-white transition-colors duration-300" id="brand-logo">
            <img src="assets/image/logo_desai.png" alt="Logo UMKM Insight" class="w-8 h-8 object-contain drop-shadow-md">
            <span class="font-extrabold text-xl tracking-tight">UMKM Insight</span>
        </a>
        <div class="hidden md:flex items-center gap-8 font-medium">
            <a href="#fitur" class="text-white/80 hover:text-white transition-colors">Fitur</a>
            <a href="#harga" class="text-white/80 hover:text-white transition-colors">Harga</a>
            <a href="login.php" class="text-white/80 hover:text-white transition-colors">Masuk</a>
            <a href="register.php" class="bg-white text-slate-900 hover:bg-slate-100 px-6 py-2.5 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">Mulai Gratis</a>
        </div>
    </div>
</nav>

<section class="relative min-h-screen flex items-center pt-32 pb-20 overflow-hidden">
    <div class="hero-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    
    <div class="max-w-[1200px] mx-auto px-6 hero-content grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div data-aos="fade-right" data-aos-duration="1000">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold uppercase tracking-widest mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                UMKM Insight v2.0 Rilis
            </div>
            <h1 class="text-5xl md:text-6xl font-black text-white leading-tight tracking-tighter mb-6">
                Wawasan Bisnis UMKM, <br>
                <span class="text-gradient-animate">Berbasis Data.</span>
            </h1>
            <p class="text-lg text-slate-300 mb-8 max-w-lg leading-relaxed">
                Analitik penjualan, arus kas, dan performa produk Anda — langsung dari data transaksi. Ambil keputusan bisnis yang lebih cerdas dan kembangkan usaha Anda lebih cepat.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="register.php" class="btn bg-gradient-to-r from-emerald-400 to-teal-500 hover:from-emerald-500 hover:to-teal-600 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:-translate-y-1 transition-all flex items-center gap-2">
                    <i class="ph-bold ph-rocket-launch text-xl"></i> Daftar Gratis Sekarang
                </a>
                <a href="#fitur" class="btn bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md px-8 py-4 rounded-xl font-bold transition-all flex items-center gap-2">
                    <i class="ph-bold ph-play-circle text-xl"></i> Lihat Fitur
                </a>
            </div>
            
            <div class="mt-12 flex items-center gap-6 text-slate-400 text-sm font-medium">
                <div class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-400"></i> Integrasi Instan</div>
                <div class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-400"></i> Keamanan Bank-grade</div>
                <div class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-400"></i> Support 24/7</div>
            </div>
        </div>
        
        <div data-aos="fade-left" data-aos-duration="1200" data-aos-delay="200" class="relative">
            <!-- Decorative floating elements -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl rotate-12 opacity-80 blur-2xl animate-pulse"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full opacity-80 blur-2xl" style="animation: pulse 4s infinite alternate;"></div>
            
            <div class="dashboard-mockup p-2">
                <!-- Abstract Dashboard UI Representation -->
                <div class="bg-slate-900 rounded-xl overflow-hidden border border-slate-700/50 shadow-2xl">
                    <div class="h-8 bg-slate-800 flex items-center px-4 gap-2">
                        <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div class="h-24 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 border border-emerald-500/30 rounded-xl p-4 flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/30 flex items-center justify-center text-emerald-400"><i class="ph-bold ph-wallet"></i></div>
                            <div class="w-24 h-4 bg-emerald-400/50 rounded"></div>
                        </div>
                        <div class="h-24 bg-gradient-to-br from-blue-400/20 to-indigo-500/20 border border-blue-500/30 rounded-xl p-4 flex flex-col justify-between">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/30 flex items-center justify-center text-blue-400"><i class="ph-bold ph-chart-bar"></i></div>
                            <div class="w-20 h-4 bg-blue-400/50 rounded"></div>
                        </div>
                        <div class="col-span-2 h-40 bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 relative overflow-hidden">
                            <!-- Fake chart -->
                            <svg class="absolute bottom-0 left-0 w-full h-24" preserveAspectRatio="none" viewBox="0 0 100 100">
                                <path d="M0,100 L0,50 Q25,20 50,60 T100,30 L100,100 Z" fill="rgba(16, 185, 129, 0.2)"/>
                                <path d="M0,50 Q25,20 50,60 T100,30" fill="none" stroke="#10b981" stroke-width="3"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Floating Badge -->
            <div class="absolute -right-6 top-1/4 bg-white/10 backdrop-blur-xl border border-white/20 p-4 rounded-2xl shadow-xl transform rotate-6 animate-bounce" style="animation-duration: 3s;">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white"><i class="ph-bold ph-trend-up"></i></div>
                    <div>
                        <p class="text-[10px] text-emerald-300 font-bold uppercase">Growth</p>
                        <p class="text-lg font-black text-white">+142.5%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 bg-slate-50 dark:bg-slate-900 relative overflow-hidden" id="fitur">
    <div class="max-w-[1200px] mx-auto px-6 relative z-10">
        <div class="text-center mb-20" data-aos="fade-up">
            <span class="px-4 py-1.5 rounded-full bg-brand-100 dark:bg-brand-900/50 text-brand-700 dark:text-brand-400 text-xs font-bold uppercase tracking-widest inline-block mb-4">Fitur Unggulan</span>
            <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white">Semua yang UMKM Anda Butuhkan</h2>
            <p class="text-lg text-slate-500 mt-4 max-w-2xl mx-auto">Tinggalkan pencatatan manual. Sistem kami memproses data Anda menjadi wawasan visual yang mudah dipahami.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl mb-6 shadow-inner"><i class="ph-fill ph-chart-line-up"></i></div>
                <h3 class="text-2xl font-bold mb-4 text-slate-800 dark:text-white">Laporan Penjualan Real-time</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">Pantau tren pendapatan harian hingga bulanan. Identifikasi kanal penjualan paling produktif untuk bisnis Anda dalam sekilas pandang.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl mb-6 shadow-inner"><i class="ph-fill ph-wallet"></i></div>
                <h3 class="text-2xl font-bold mb-4 text-slate-800 dark:text-white">Manajemen Arus Kas Pintar</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">Pahami aliran dana masuk dan keluar secara mendalam. Pantau pengeluaran stok, operasional, hingga deteksi kebocoran dana lebih dini.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mb-6 shadow-inner"><i class="ph-fill ph-package"></i></div>
                <h3 class="text-2xl font-bold mb-4 text-slate-800 dark:text-white">Analisis Performa Produk</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">Ketahui produk "Star" dan "Slow Mover" Anda. Optimalkan pengadaan stok berdasarkan data preferensi konsumen yang nyata.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-24 relative" id="harga">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white mb-4">Pilih Paket Langganan</h2>
            <p class="text-lg text-slate-500 max-w-xl mx-auto">Mulai gratis selamanya. Upgrade ke Premium kapan saja saat bisnis Anda membutuhkan fitur yang lebih canggih.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto items-center">
            <!-- Free Tier -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-3xl p-10 border border-slate-200 dark:border-slate-700 shadow-xl" data-aos="fade-right" data-aos-duration="1000">
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Basic Starter</h3>
                <p class="text-slate-500 text-sm mt-2 mb-6">Cocok untuk UMKM pemula</p>
                <div class="text-5xl font-black text-slate-900 dark:text-white mb-8">Rp 0<span class="text-lg font-medium text-slate-400">/bln</span></div>
                
                <ul class="space-y-4 mb-10">
                    <li class="flex items-center gap-3 text-slate-700 dark:text-slate-300"><i class="ph-fill ph-check-circle text-emerald-500 text-xl"></i> Akses Dashboard Dasar</li>
                    <li class="flex items-center gap-3 text-slate-700 dark:text-slate-300"><i class="ph-fill ph-check-circle text-emerald-500 text-xl"></i> Laporan 3 Bulan Terakhir</li>
                    <li class="flex items-center gap-3 text-slate-700 dark:text-slate-300"><i class="ph-fill ph-check-circle text-emerald-500 text-xl"></i> Top 5 Produk Terlaris</li>
                    <li class="flex items-center gap-3 text-slate-400 dark:text-slate-600"><i class="ph-bold ph-lock text-xl"></i> Ekspor Laporan Lengkap</li>
                    <li class="flex items-center gap-3 text-slate-400 dark:text-slate-600"><i class="ph-bold ph-lock text-xl"></i> Smart Insights AI</li>
                </ul>
                <a href="register.php" class="btn bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white btn-full py-4 rounded-xl font-bold transition-colors">Mulai Gratis</a>
            </div>
            
            <!-- Pro Tier -->
            <div class="pricing-pro rounded-3xl p-10 shadow-2xl transform md:scale-105 z-10" data-aos="fade-left" data-aos-duration="1000">
                <div class="absolute top-0 right-10 transform -translate-y-1/2 bg-gradient-to-r from-brand-500 to-purple-500 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest shadow-lg">Paling Populer</div>
                
                <h3 class="text-2xl font-bold text-brand-700 dark:text-brand-400">Premium Pro</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm mt-2 mb-6">Untuk bisnis yang siap meroket</p>
                <div class="text-5xl font-black text-slate-900 dark:text-white mb-8">Rp 99k<span class="text-lg font-medium text-slate-500">/bln</span></div>
                
                <ul class="space-y-4 mb-10">
                    <li class="flex items-center gap-3 text-slate-800 dark:text-slate-200"><i class="ph-fill ph-check-circle text-brand-500 text-xl"></i> Semua Fitur Basic</li>
                    <li class="flex items-center gap-3 text-slate-800 dark:text-slate-200"><i class="ph-fill ph-check-circle text-brand-500 text-xl"></i> Laporan Sejarah Tanpa Batas</li>
                    <li class="flex items-center gap-3 text-slate-800 dark:text-slate-200"><i class="ph-fill ph-check-circle text-brand-500 text-xl"></i> Proyeksi Arus Kas Masa Depan</li>
                    <li class="flex items-center gap-3 text-slate-800 dark:text-slate-200"><i class="ph-fill ph-check-circle text-brand-500 text-xl"></i> Analisis Pertumbuhan Lanjutan & AI</li>
                    <li class="flex items-center gap-3 text-slate-800 dark:text-slate-200"><i class="ph-fill ph-check-circle text-brand-500 text-xl"></i> Ekspor Laporan CSV & PDF</li>
                </ul>
                <a href="register.php" class="btn bg-gradient-to-r from-brand-500 to-purple-500 hover:from-brand-600 hover:to-purple-600 text-white btn-full py-4 rounded-xl font-bold shadow-lg shadow-brand-500/30 hover:-translate-y-1 transition-transform">Beralih ke Pro</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-24 relative overflow-hidden bg-brand-900">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAzNGwtMTItMTJ2MzZIMjRWMzhMMTIgMjZWNkgwVjRoMTJ2MTRsMTIgMTJWNGg0djI2bDEyLTEyVjRoNHYyNmwxMiAxMkg2MFYzNGgtOHptLTI0LTE4VjRINHYxMmw4IDh6IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz48L2c+PC9zdmc+')] opacity-20"></div>
    <div class="max-w-[800px] mx-auto px-6 text-center relative z-10" data-aos="zoom-in">
        <h2 class="text-4xl md:text-5xl font-black text-white mb-6">Siap Mengambil Kendali Bisnis Anda?</h2>
        <p class="text-brand-100 text-lg mb-10">Bergabung dengan ribuan UMKM yang telah menggunakan data untuk melipatgandakan keuntungan mereka.</p>
        <a href="register.php" class="inline-block bg-white text-brand-900 font-extrabold px-10 py-5 rounded-2xl text-xl shadow-2xl hover:scale-105 transition-transform duration-300">
            Mulai Sekarang — Gratis!
        </a>
    </div>
</section>

<footer class="bg-slate-950 text-white py-12 px-6 text-center border-t border-slate-800">
    <div class="flex justify-center gap-8 mb-8 text-sm text-slate-400">
        <a href="#fitur" class="hover:text-white transition-colors">Fitur</a>
        <a href="#harga" class="hover:text-white transition-colors">Harga</a>
        <a href="login.php" class="hover:text-white transition-colors">Masuk</a>
        <a href="register.php" class="hover:text-white transition-colors">Daftar</a>
    </div>
    <div class="flex items-center justify-center gap-2 mb-4">
        <img src="assets/image/logo_desai.png" alt="Logo UMKM Insight" class="w-6 h-6 grayscale opacity-50">
        <span class="font-bold text-slate-500">UMKM Insight</span>
    </div>
    <p class="text-slate-600 text-xs">&copy; <?php echo date('Y'); ?> Ekosistem Ekonomi UMKM. Simulasi Sistem Informasi RPL 2.</p>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS animations
    AOS.init({
        once: true,
        offset: 100,
        duration: 800,
        easing: 'ease-out-cubic'
    });

    // Navbar Scroll Effect
    const nav = document.getElementById('landing-nav');
    const brandText = document.querySelector('#brand-logo span');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
            if (!document.documentElement.classList.contains('dark')) {
                brandText.classList.remove('text-white');
                brandText.classList.add('text-slate-900');
            }
        } else {
            nav.classList.remove('scrolled');
            brandText.classList.remove('text-slate-900');
            brandText.classList.add('text-white');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
