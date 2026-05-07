<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRole('admin');

$user = getCurrentUser($pdo);
$pageTitle = "Admin System Panel";
$activePage = 'admin';

$success = '';
$error = '';

// Handle Custom/Blast Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_notif') {
    $targetId = $_POST['target_user_id'];
    $title = sanitize($_POST['title']);
    $msg = sanitize($_POST['message']);

    if ($targetId === 'all') {
        // Blast to all clients
        $stmt = $pdo->prepare("INSERT INTO notifications (target_user_id, sender_id, type, title, message) 
                               SELECT id, ?, 'admin', ?, ? FROM users WHERE role = 'client'");
        if ($stmt->execute([$user['id'], $title, $msg])) {
            $success = "Blast notifikasi ke seluruh UMKM berhasil dikirim.";
        } else {
            $error = "Gagal mengirim blast notifikasi.";
        }
    } else {
        // Single user
        $stmt = $pdo->prepare("INSERT INTO notifications (target_user_id, sender_id, type, title, message) VALUES (?, ?, 'admin', ?, ?)");
        if ($stmt->execute([$targetId, $user['id'], $title, $msg])) {
            $success = "Notifikasi berhasil dikirim.";
        } else {
            $error = "Gagal mengirim notifikasi.";
        }
    }
}

// Handle Tier Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_tier') {
    $targetId = $_POST['user_id'];
    $newTier = $_POST['new_tier'];
    $expiry = ($newTier === 'premium') ? date('Y-m-d', strtotime('+30 days')) : null;

    $stmt = $pdo->prepare("UPDATE users SET tier = ?, tier_expiry = ? WHERE id = ?");
    if ($stmt->execute([$newTier, $expiry, $targetId])) {
        $success = "Status tier pengguna berhasil diperbarui.";
    } else {
        $error = "Gagal memperbarui tier.";
    }
}

// Fetch Global Stats
$totalClients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$totalOperators = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'operator'")->fetchColumn();
$totalPremium = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND tier = 'premium'")->fetchColumn();
$totalEcosystemRev = $pdo->query("SELECT SUM(amount) FROM transaction_cache WHERE type = 'Income'")->fetchColumn() ?: 0;

// Fetch Users (excluding admins)
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$roleFilter = isset($_GET['role']) && $_GET['role'] !== 'all' ? $_GET['role'] : '%';

$stmt = $pdo->prepare("SELECT * FROM users WHERE role LIKE ? AND (nama_lengkap LIKE ? OR email LIKE ? OR nama_bisnis LIKE ?) AND role != 'admin' ORDER BY created_at DESC");
$stmt->execute([$roleFilter, $search, $search, $search]);
$users = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
    body { background: var(--surface); display: flex; height: 100vh; overflow: hidden; }
    .admin-sidebar { width: 260px; background: #0f172a; color: white; display: flex; flex-direction: column; }
    .admin-nav { flex: 1; padding: 20px 12px; }
    .admin-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; color: #94a3b8; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; margin-bottom: 4px; }
    .admin-nav a:hover { background: rgba(255,255,255,0.05); color: white; }
    .admin-nav a.active { background: var(--brand-600); color: white; }
    .main-wrap { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
</style>

<aside class="admin-sidebar">
    <div class="p-6 border-b border-slate-800 flex items-center gap-3">
        <i class="ph-fill ph-chart-polar text-2xl text-brand-400"></i>
        <span class="font-bold text-lg">Admin Panel</span>
    </div>
    <nav class="admin-nav">
        <a href="admin.php" class="active"><i class="ph ph-users-three"></i> Manajemen User</a>
        <a href="#" class="opacity-50 pointer-events-none"><i class="ph ph-list-magnifying-glass"></i> Audit Logs</a>
        <a href="#" class="opacity-50 pointer-events-none"><i class="ph ph-gear"></i> System Config</a>
    </nav>
    <div class="p-4 border-t border-slate-800">
        <a href="logout.php" class="btn btn-ghost btn-sm btn-full text-slate-400 hover:text-white" style="justify-content: flex-start;"><i class="ph ph-sign-out"></i> Keluar</a>
    </div>
</aside>

<div class="main-wrap">
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-20">
        <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
            <i class="ph-fill ph-shield-check text-brand-600"></i> Super Admin Mode
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400"><?php echo $user['nama_lengkap']; ?></span>
            <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs">A</div>
        </div>
    </header>

    <div class="main-content">
        <div class="mb-8">
            <h1 class="text-2xl font-extrabold tracking-tight">Ekosistem UMKM Insight</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau statistik global dan kelola hak akses seluruh pengguna sistem.</p>
        </div>

        <?php if($success): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-4 rounded-xl text-sm mb-6 flex items-center gap-3">
                <i class="ph-fill ph-check-circle text-xl"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-rose-50 border border-rose-100 text-rose-600 p-4 rounded-xl text-sm mb-6 flex items-center gap-3">
                <i class="ph-fill ph-warning-circle text-xl"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Global Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="card p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Total Konsumen</p>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-bold"><?php echo $totalClients; ?></span>
                    <i class="ph ph-users text-blue-500 bg-blue-50 p-2 rounded-lg"></i>
                </div>
            </div>
            <div class="card p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Staff Operator</p>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-bold"><?php echo $totalOperators; ?></span>
                    <i class="ph ph-user-gear text-purple-500 bg-purple-50 p-2 rounded-lg"></i>
                </div>
            </div>
            <div class="card p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">User Premium</p>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-bold"><?php echo $totalPremium; ?></span>
                    <i class="ph ph-crown text-amber-500 bg-amber-50 p-2 rounded-lg"></i>
                </div>
            </div>
            <div class="card p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Pendapatan Ekosistem</p>
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-emerald-600"><?php echo formatRupiah($totalEcosystemRev); ?></span>
                    <i class="ph ph-bank text-emerald-500 bg-emerald-50 p-2 rounded-lg"></i>
                </div>
            </div>
        </div>

        <!-- User Table -->
        <div class="card overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="font-bold">Daftar Pengguna & Staff</h2>
                    <button class="btn btn-primary btn-sm px-3" onclick="openNotifModal('all', 'Seluruh UMKM (Blast)')">
                        <i class="ph ph-megaphone"></i> Blast Message
                    </button>
                </div>
                <form action="admin.php" method="GET" class="flex gap-2">
                    <input type="text" name="search" class="form-input text-xs py-2" placeholder="Cari nama/email..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    <select name="role" class="form-select text-xs py-2" onchange="this.form.submit()">
                        <option value="all">Semua Role</option>
                        <option value="client" <?php echo isset($_GET['role']) && $_GET['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                        <option value="operator" <?php echo isset($_GET['role']) && $_GET['role'] === 'operator' ? 'selected' : ''; ?>>Operator</option>
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Nama / Bisnis</th><th>Role</th><th>Email</th><th>Tier</th><th class="text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($usersList as $u): ?>
                            <tr>
                                <td>
                                    <p class="font-bold text-sm text-slate-800"><?php echo $u['nama_bisnis'] ?: $u['nama_lengkap']; ?></p>
                                    <p class="text-[10px] text-slate-400"><?php echo $u['nama_lengkap']; ?></p>
                                </td>
                                <td><span class="badge badge-neutral"><?php echo strtoupper($u['role']); ?></span></td>
                                <td class="text-xs"><?php echo $u['email']; ?></td>
                                <td>
                                    <?php if($u['role'] === 'client'): ?>
                                        <span class="badge <?php echo $u['tier'] === 'premium' ? 'badge-premium' : 'badge-free'; ?>">
                                            <?php echo strtoupper($u['tier']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-center">
                                        <button class="btn btn-ghost btn-sm p-2 text-blue-600" title="Kirim Notifikasi" onclick="openNotifModal('<?php echo $u['id']; ?>', '<?php echo $u['nama_lengkap']; ?>')">
                                            <i class="ph ph-bell text-lg"></i>
                                        </button>
                                        <?php if($u['role'] === 'client'): ?>
                                            <form action="admin.php" method="POST" class="inline">
                                                <input type="hidden" name="action" value="change_tier">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <?php if($u['tier'] === 'free'): ?>
                                                    <input type="hidden" name="new_tier" value="premium">
                                                    <button type="submit" class="btn btn-outline btn-sm text-emerald-600 border-emerald-100 hover:bg-emerald-50">UP</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="new_tier" value="free">
                                                    <button type="submit" class="btn btn-outline btn-sm text-rose-600 border-rose-100 hover:bg-rose-50">DOWN</button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Notif Modal -->
<div class="modal-overlay" id="notif-modal">
    <div class="modal max-w-sm">
        <h3 class="text-lg font-bold mb-4">Kirim Pesan Admin</h3>
        <p class="text-xs text-slate-500 mb-4">Ke: <strong id="target-name"></strong></p>
        <form action="admin.php" method="POST" class="flex flex-col gap-4">
            <input type="hidden" name="action" value="send_notif">
            <input type="hidden" name="target_user_id" id="target-id">
            <div class="form-group">
                <label class="form-label">Judul Notifikasi</label>
                <input type="text" name="title" class="form-input" placeholder="e.g. Pemeliharaan Sistem" required>
            </div>
            <div class="form-group">
                <label class="form-label">Isi Pesan</label>
                <textarea name="message" class="form-input" rows="3" required></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('notif-modal').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm px-6">Kirim</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNotifModal(id, name) {
        document.getElementById('target-id').value = id;
        document.getElementById('target-name').textContent = name;
        document.getElementById('notif-modal').classList.add('open');
    }
</script>

<?php include 'includes/footer.php'; ?>
