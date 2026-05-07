<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRole('operator');

$user = getCurrentUser($pdo);
$pageTitle = "Manajemen Operasional";
$activePage = 'operator';

$success = '';
$error = '';

// Handle Tier Approval/Rejection (Tahap 4)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_tier_request') {
    $requestId = $_POST['request_id'];
    $targetUserId = $_POST['user_id'];
    $status = $_POST['status']; // 'approved' or 'rejected'

    $pdo->beginTransaction();
    try {
        // Update request status
        $stmt = $pdo->prepare("UPDATE tier_requests SET status = ?, processed_at = NOW(), processed_by = ? WHERE id = ?");
        $stmt->execute([$status, $user['id'], $requestId]);

        if ($status === 'approved') {
            $expiry = date('Y-m-d', strtotime('+30 days'));
            $pdo->prepare("UPDATE users SET tier = 'premium', tier_expiry = ? WHERE id = ?")->execute([$expiry, $targetUserId]);
            
            // Send Notification
            $pdo->prepare("INSERT INTO notifications (target_user_id, sender_id, type, title, message) VALUES (?, ?, 'auto', 'Akun Premium Aktif!', 'Selamat! Pengajuan upgrade akun Anda telah disetujui. Nikmati fitur premium sekarang.')")
                ->execute([$targetUserId, $user['id']]);
        } else {
            // Send Notification for Rejection
            $pdo->prepare("INSERT INTO notifications (target_user_id, sender_id, type, title, message) VALUES (?, ?, 'auto', 'Pengajuan Tier Ditolak', 'Maaf, pengajuan upgrade akun Anda belum dapat disetujui saat ini. Silakan hubungi bantuan.')")
                ->execute([$targetUserId, $user['id']]);
        }

        $pdo->commit();
        $success = "Pengajuan tier berhasil diproses.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Gagal memproses pengajuan: " . $e->getMessage();
    }
}

// Handle Manual Tier Change (Admin/Op override)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_tier') {
    $targetId = $_POST['user_id'];
    $newTier = $_POST['new_tier'];
    $expiry = ($newTier === 'premium') ? date('Y-m-d', strtotime('+30 days')) : null;

    $stmt = $pdo->prepare("UPDATE users SET tier = ?, tier_expiry = ? WHERE id = ?");
    if ($stmt->execute([$newTier, $expiry, $targetId])) {
        $success = "Tier pengguna berhasil diperbarui secara manual.";
    } else {
        $error = "Gagal memperbarui tier.";
    }
}

// Handle New Offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_offer') {
    $title = sanitize($_POST['title']);
    $desc = sanitize($_POST['description']);
    $price = (int)$_POST['price'];
    $target = $_POST['target_tier'];

    $stmt = $pdo->prepare("INSERT INTO offers (title, description, price, target_tier, created_by) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $desc, $price, $target, $user['id']])) {
        $success = "Penawaran baru berhasil dipublikasi.";
    } else {
        $error = "Gagal menyimpan penawaran.";
    }
}

// Fetch Data (Tahap 4)
$pendingRequests = $pdo->query("SELECT tr.*, u.nama_lengkap, u.username, u.nama_bisnis FROM tier_requests tr JOIN users u ON tr.user_id = u.id WHERE tr.status = 'pending' ORDER BY tr.requested_at ASC")->fetchAll();
$clients = $pdo->query("SELECT * FROM users WHERE role = 'client' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$offers = $pdo->query("SELECT * FROM offers ORDER BY created_at DESC")->fetchAll();
$openComplaints = $pdo->query("SELECT c.*, u.nama_lengkap FROM complaints c JOIN users u ON c.user_id = u.id WHERE c.status = 'open' ORDER BY c.created_at DESC LIMIT 5")->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="animate-fade-in" style="padding-top: 24px;">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight">Pusat Kendali Operasional</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola tier pengguna, penawaran promo, dan pengaduan layanan.</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: User & Tier Management -->
            <div class="lg:col-span-2 flex flex-col gap-8">
                <!-- Pending Tier Requests (Tahap 4) -->
                <div class="card p-6 border-l-4 border-amber-500">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold flex items-center gap-2"><i class="ph-fill ph-lightning text-amber-500"></i> Pengajuan Tier Premium</h2>
                        <span class="badge badge-neutral"><?php echo count($pendingRequests); ?> MENUNGGU</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr><th>UMKM / Bisnis</th><th>Waktu Pengajuan</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php if(empty($pendingRequests)): ?>
                                    <tr><td colspan="3" class="text-center py-10 text-slate-400">Tidak ada pengajuan baru.</td></tr>
                                <?php else: ?>
                                    <?php foreach($pendingRequests as $pr): ?>
                                        <tr>
                                            <td>
                                                <p class="font-bold text-sm text-slate-800"><?php echo $pr['nama_bisnis'] ?: $pr['nama_lengkap']; ?></p>
                                                <p class="text-[10px] text-slate-400">@<?php echo $pr['username']; ?></p>
                                            </td>
                                            <td class="text-xs text-slate-500"><?php echo date('d M Y, H:i', strtotime($pr['requested_at'])); ?></td>
                                            <td class="flex gap-2">
                                                <form action="operator.php" method="POST">
                                                    <input type="hidden" name="action" value="process_tier_request">
                                                    <input type="hidden" name="request_id" value="<?php echo $pr['id']; ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo $pr['user_id']; ?>">
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn btn-primary btn-sm px-4">Approve</button>
                                                </form>
                                                <form action="operator.php" method="POST">
                                                    <input type="hidden" name="action" value="process_tier_request">
                                                    <input type="hidden" name="request_id" value="<?php echo $pr['id']; ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo $pr['user_id']; ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn btn-outline btn-sm px-4">Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Manual Management -->
                <!-- Tier Approval -->
                <div class="card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold flex items-center gap-2"><i class="ph ph-crown text-amber-500"></i> Manajemen Tier Pengguna</h2>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Update Manual</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr><th>Pengguna</th><th>Tier Saat Ini</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($clients as $c): ?>
                                    <tr>
                                        <td>
                                            <p class="font-bold text-sm text-slate-800"><?php echo $c['nama_bisnis'] ?: $c['nama_lengkap']; ?></p>
                                            <p class="text-[10px] text-slate-400"><?php echo $c['username']; ?></p>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $c['tier'] === 'premium' ? 'badge-premium' : 'badge-free'; ?>">
                                                <?php echo strtoupper($c['tier']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form action="operator.php" method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $c['id']; ?>">
                                                <input type="hidden" name="action" value="change_tier">
                                                <?php if($c['tier'] === 'free'): ?>
                                                    <input type="hidden" name="new_tier" value="premium">
                                                    <button type="submit" class="btn btn-primary btn-sm px-4">Approve PRO</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="new_tier" value="free">
                                                    <button type="submit" class="btn btn-outline btn-sm px-4">Revoke PRO</button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Offers Management -->
                <div class="card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold flex items-center gap-2"><i class="ph ph-megaphone text-blue-500"></i> Kelola Penawaran & Promo</h2>
                        <button class="btn btn-primary btn-sm" onclick="document.getElementById('offer-modal').classList.add('open')">
                            <i class="ph ph-plus"></i> Buat Promo
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach($offers as $o): ?>
                            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-bold text-sm text-slate-800"><?php echo $o['title']; ?></h3>
                                        <span class="text-[10px] font-bold text-brand-600"><?php echo formatRupiah($o['price']); ?></span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 mb-3"><?php echo $o['description']; ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-400">Target: <?php echo strtoupper($o['target_tier']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Complaints & Insights -->
            <div class="flex flex-col gap-8">
                <!-- Complaints Summary -->
                <div class="card p-6">
                    <h2 class="text-lg font-bold mb-6 flex items-center gap-2"><i class="ph ph-chat-circle-dots text-rose-500"></i> Pengaduan Terkini</h2>
                    <div class="flex flex-col gap-3">
                        <?php if(empty($openComplaints)): ?>
                            <p class="text-center py-10 text-xs text-slate-400">Semua pengaduan selesai ditangani.</p>
                        <?php else: ?>
                            <?php foreach($openComplaints as $oc): ?>
                                <div class="p-3 bg-rose-50 border border-rose-100 rounded-lg">
                                    <p class="font-bold text-xs text-rose-700"><?php echo $oc['subject']; ?></p>
                                    <p class="text-[10px] text-rose-600 mt-1 line-clamp-1"><?php echo $oc['message']; ?></p>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-[9px] text-rose-400"><?php echo $oc['nama_lengkap']; ?></span>
                                        <a href="pengaduan-admin.php" class="text-[10px] font-bold text-rose-700">Balas →</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a href="pengaduan-admin.php" class="btn btn-ghost btn-sm btn-full mt-4">Lihat Semua Tiket</a>
                </div>

                <!-- Guidance -->
                <div class="card p-6 bg-brand-900 text-white border-none shadow-lg">
                    <h3 class="font-bold mb-3 flex items-center gap-2"><i class="ph ph-info"></i> Tips Operator</h3>
                    <p class="text-xs text-brand-100 leading-relaxed">
                        Sebelum melakukan <strong>Approve PRO</strong>, pastikan Anda telah memverifikasi bukti transfer pembayaran di sistem SmartBank sesuai dengan ID pengguna yang bersangkutan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offer Modal -->
<div class="modal-overlay" id="offer-modal">
    <div class="modal max-w-md">
        <h2 class="text-xl font-bold mb-6">Buat Penawaran Baru</h2>
        <form action="operator.php" method="POST" class="flex flex-col gap-4">
            <input type="hidden" name="action" value="add_offer">
            <div class="form-group">
                <label class="form-label">Judul Promo</label>
                <input type="text" name="title" class="form-input" placeholder="e.g. Promo Ramadhan" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi Singkat</label>
                <textarea name="description" class="form-input" rows="3" placeholder="Jelaskan keuntungan promo ini..." required></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" class="form-input" placeholder="10000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Tier</label>
                    <select name="target_tier" class="form-select">
                        <option value="all">Semua Pengguna</option>
                        <option value="free">Free User</option>
                        <option value="premium">Premium User</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('offer-modal').classList.remove('open')">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm px-6">Publikasikan</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
