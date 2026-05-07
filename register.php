<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $bisnis = sanitize($_POST['bisnis']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($nama) || empty($email) || empty($username) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // Cek username unik
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username sudah digunakan.";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, email, nama_bisnis, role, tier) VALUES (?, ?, ?, ?, ?, 'client', 'free')");
            if ($stmt->execute([$username, $hashed, $nama, $email, $bisnis])) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan saat mendaftar.";
            }
        }
    }
}

$pageTitle = "Daftar Akun";
include 'includes/header.php';
?>

<style>
    body { min-height: 100vh; display: flex; background: var(--surface); }
    .reg-container { max-width: 500px; width: 90%; margin: auto; padding: 40px 0; }
</style>

<div class="reg-container animate-fade-in">
    <div class="card p-8 md:p-10">
        <div class="flex items-center gap-2 mb-6 justify-center">
            <i class="ph-fill ph-chart-polar text-3xl text-brand-600"></i>
            <span class="text-2xl font-bold tracking-tight">UMKM Insight</span>
        </div>
        <h1 class="text-2xl font-bold mb-2 text-center">Buat Akun Baru</h1>
        <p class="text-slate-500 mb-8 text-sm text-center">Bergabunglah dengan ribuan UMKM yang telah beralih ke analitik cerdas.</p>

        <?php if($error): ?>
            <div class="bg-rose-50 border border-rose-100 text-rose-600 p-3 rounded-lg text-sm mb-6 flex items-center gap-2">
                <i class="ph ph-warning-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-3 rounded-lg text-sm mb-6 flex items-center gap-2">
                <i class="ph ph-check-circle"></i> <?php echo $success; ?> 
                <a href="login.php" class="font-bold underline ml-auto">Login Sekarang</a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="flex flex-col gap-4">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-input" placeholder="e.g. Budi Santoso" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="budi@email.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Bisnis (Optional)</label>
                    <input type="text" name="bisnis" class="form-input" placeholder="Toko Berkah">
                </div>
            </div>
            <div class="divider h-px bg-slate-100 my-2"></div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="pilih username unik" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="••••••••" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full py-3 mt-4">
                <i class="ph ph-user-plus"></i> Daftar Sekarang
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-8">
            Sudah punya akun? <a href="login.php" class="text-brand-600 font-bold">Masuk di sini</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
