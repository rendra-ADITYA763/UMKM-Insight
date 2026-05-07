<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

// Jika sudah login, lempar ke dashboard sesuai role
if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') header("Location: admin.php");
    elseif ($_SESSION['role'] === 'operator') header("Location: operator.php");
    else header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login Berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['tier'] = $user['tier'];

            // Redirect sesuai role
            if ($user['role'] === 'admin') header("Location: admin.php");
            elseif ($user['role'] === 'operator') header("Location: operator.php");
            else header("Location: dashboard.php");
            exit();
        } else {
            $error = "Username atau password salah.";
        }
    }
}

$pageTitle = "Masuk";
include 'includes/header.php';
?>

<style>
    body { min-height: 100vh; display: flex; }
    .auth-left { flex: 1; background: linear-gradient(135deg, #0f172a 0%, #134e4a 50%, #0d9488 100%); display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 48px; position: relative; overflow: hidden; }
    .auth-left-content { position: relative; z-index: 2; max-width: 400px; text-align: center; color: white; }
    .auth-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px 24px; background: var(--surface); }
    .auth-card { width: 100%; max-width: 420px; }
    @media(max-width:768px) { .auth-left { display: none; } }
</style>

<div class="auth-left">
    <div class="auth-left-content">
        <h2 class="text-3xl font-extrabold mb-4">Analitik Bisnis Powerful</h2>
        <p class="text-slate-300 mb-8">Pantau kinerja UMKM Anda dengan dashboard interaktif yang terhubung langsung ke SmartBank.</p>
        <div class="flex flex-col gap-3">
            <div class="bg-white/10 p-4 rounded-xl text-left flex gap-3 items-center border border-white/10">
                <i class="ph-fill ph-trend-up text-brand-400 text-xl"></i>
                <span class="text-sm">Laporan penjualan real-time</span>
            </div>
            <div class="bg-white/10 p-4 rounded-xl text-left flex gap-3 items-center border border-white/10">
                <i class="ph-fill ph-crown text-amber-400 text-xl"></i>
                <span class="text-sm">Wawasan premium untuk scale-up</span>
            </div>
        </div>
    </div>
</div>

<div class="auth-right">
    <div class="auth-card">
        <div class="flex items-center gap-2 mb-8">
            <i class="ph-fill ph-chart-polar text-3xl text-brand-600"></i>
            <span class="text-2xl font-bold tracking-tight">UMKM Insight</span>
        </div>
        <h1 class="text-2xl font-bold mb-2">Selamat Datang Kembali</h1>
        <p class="text-slate-500 mb-8 text-sm">Masuk untuk mengelola wawasan bisnis Anda.</p>

        <?php if($error): ?>
            <div class="bg-rose-50 border border-rose-100 text-rose-600 p-3 rounded-lg text-sm mb-6 flex items-center gap-2">
                <i class="ph ph-warning-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="flex flex-col gap-5">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Masukkan username" required>
            </div>
            <div class="form-group">
                <div class="flex justify-between items-center">
                    <label class="form-label">Password</label>
                    <a href="#" class="text-xs text-brand-600 font-semibold">Lupa Password?</a>
                </div>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full py-3">
                <i class="ph ph-sign-in"></i> Masuk
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-8">
            Belum punya akun? <a href="register.php" class="text-brand-600 font-bold">Daftar Sekarang</a>
        </p>

        <div class="mt-8 p-4 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-400 leading-relaxed">
            <strong>DEMO ACCOUNTS:</strong><br>
            Client: <code>budi</code> / <code>password</code><br>
            Operator: <code>op_jaya</code> / <code>operator123</code><br>
            Admin: <code>admin_super</code> / <code>admin123</code>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
