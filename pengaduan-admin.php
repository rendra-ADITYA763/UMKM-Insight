<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRole('operator');

$user = getCurrentUser($pdo);
$pageTitle = "Manajemen Tiket Bantuan";
$activePage = 'pengaduan-admin';

$success = '';
$error = '';

// Handle Resolve Ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_ticket') {
    $ticketId = $_POST['ticket_id'];
    $userId = $_POST['target_user_id'];
    $subject = $_POST['ticket_subject'];

    $stmt = $pdo->prepare("UPDATE complaints SET status = 'resolved' WHERE id = ?");
    if ($stmt->execute([$ticketId])) {
        // Create Notification
        $msg = "Laporan Anda mengenai \"$subject\" telah diselesaikan oleh tim operator.";
        $stmtNotif = $pdo->prepare("INSERT INTO notifications (target_user_id, type, title, message) VALUES (?, 'system', 'Pengaduan Selesai', ?)");
        $stmtNotif->execute([$userId, $msg]);
        
        $success = "Tiket berhasil diselesaikan dan pengguna telah dinotifikasi.";
    } else {
        $error = "Gagal menyelesaikan tiket.";
    }
}

// Filter Status
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$query = "SELECT c.*, u.nama_lengkap, u.nama_bisnis FROM complaints c JOIN users u ON c.user_id = u.id";
if ($filter !== 'all') {
    $query .= " WHERE c.status = " . $pdo->quote($filter);
}
$query .= " ORDER BY c.created_at DESC";
$complaints = $pdo->query($query)->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="animate-fade-in" style="padding-top: 24px;">
        <div class="flex justify-between items-start mb-6 flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">Manajemen Tiket Bantuan</h1>
                <p class="text-sm text-slate-500 mt-1">Tanggapi dan selesaikan keluhan pengguna untuk menjaga kualitas layanan.</p>
            </div>
            <div class="flex gap-2">
                <a href="pengaduan-admin.php?status=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?> btn-sm">Semua</a>
                <a href="pengaduan-admin.php?status=open" class="btn <?php echo $filter === 'open' ? 'btn-primary' : 'btn-outline'; ?> btn-sm">Terbuka</a>
                <a href="pengaduan-admin.php?status=resolved" class="btn <?php echo $filter === 'resolved' ? 'btn-primary' : 'btn-outline'; ?> btn-sm">Selesai</a>
            </div>
        </div>

        <?php if($success): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-4 rounded-xl text-sm mb-6 flex items-center gap-3">
                <i class="ph-fill ph-check-circle text-xl"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-12">ID</th>
                            <th>UMKM / Pengguna</th>
                            <th>Subjek & Keluhan</th>
                            <th>Waktu Masuk</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($complaints)): ?>
                            <tr><td colspan="6" class="text-center py-20 text-slate-400">Tidak ada tiket ditemukan dalam kategori ini.</td></tr>
                        <?php else: ?>
                            <?php foreach($complaints as $c): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="font-mono text-[10px] text-slate-400"><?php echo $c['id']; ?></td>
                                    <td>
                                        <p class="font-bold text-sm text-slate-800"><?php echo $c['nama_bisnis'] ?: 'Non-Bisnis'; ?></p>
                                        <p class="text-[10px] text-slate-400"><?php echo $c['nama_lengkap']; ?></p>
                                    </td>
                                    <td class="max-w-md">
                                        <p class="font-bold text-sm text-slate-700"><?php echo $c['subject']; ?></p>
                                        <p class="text-[10px] text-slate-500 line-clamp-2"><?php echo $c['message']; ?></p>
                                    </td>
                                    <td class="text-xs text-slate-500">
                                        <?php echo date('d/m/y H:i', strtotime($c['created_at'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $c['status'] === 'open' ? 'badge-warning' : 'badge-success'; ?>">
                                            <?php echo strtoupper($c['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($c['status'] === 'open'): ?>
                                            <form action="pengaduan-admin.php?status=<?php echo $filter; ?>" method="POST">
                                                <input type="hidden" name="action" value="resolve_ticket">
                                                <input type="hidden" name="ticket_id" value="<?php echo $c['id']; ?>">
                                                <input type="hidden" name="target_user_id" value="<?php echo $c['user_id']; ?>">
                                                <input type="hidden" name="ticket_subject" value="<?php echo $c['subject']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm px-4">Selesaikan</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-[10px] text-emerald-600 font-bold"><i class="ph ph-check-circle"></i> Resolved</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
