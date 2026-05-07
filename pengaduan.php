<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRole('client');

$user = getCurrentUser($pdo);
$pageTitle = "Pusat Bantuan";
$activePage = 'pengaduan';

$success = '';
$error = '';

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_complaint') {
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    if (empty($subject) || empty($message)) {
        $error = "Subjek dan pesan wajib diisi.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO complaints (user_id, subject, message, status) VALUES (?, ?, ?, 'open')");
        if ($stmt->execute([$user['id'], $subject, $message])) {
            $success = "Pengaduan Anda telah dikirim ke tim operator.";
        } else {
            $error = "Terjadi kesalahan saat mengirim pengaduan.";
        }
    }
}

// Fetch History
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$complaints = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="animate-fade-in" style="padding-top: 24px;">
        <div style="margin-bottom:24px;">
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);">Pusat Bantuan & Pengaduan</h1>
            <p style="font-size:.8125rem;color:var(--text-secondary);margin-top:4px;">Ada kendala? Tim operator kami siap membantu Anda.</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Form -->
            <div class="card p-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2"><i class="ph ph-note-pencil text-brand-600"></i> Buat Laporan Baru</h2>
                <form action="pengaduan.php" method="POST" class="flex flex-col gap-4">
                    <input type="hidden" name="action" value="submit_complaint">
                    <div class="form-group">
                        <label class="form-label">Subjek Pengaduan</label>
                        <input type="text" name="subject" class="form-input" placeholder="e.g. Masalah Data Dashboard" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Detail Keluhan</label>
                        <textarea name="message" class="form-input" rows="5" placeholder="Ceritakan detail masalah yang Anda hadapi..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full py-3"><i class="ph ph-paper-plane-tilt"></i> Kirim Pengaduan</button>
                </form>
            </div>

            <!-- History -->
            <div class="card p-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2"><i class="ph ph-clock-counter-clockwise text-slate-500"></i> Riwayat Pengaduan</h2>
                <div class="flex flex-col gap-4 max-h-[500px] overflow-y-auto pr-2">
                    <?php if(empty($complaints)): ?>
                        <div class="text-center py-10 text-slate-400">
                            <i class="ph ph-chats text-4xl mb-2"></i>
                            <p class="text-sm">Belum ada riwayat pengaduan.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($complaints as $c): ?>
                            <div class="p-4 rounded-xl border <?php echo $c['status'] === 'open' ? 'border-amber-100 bg-amber-50' : 'border-emerald-100 bg-emerald-50'; ?>">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-sm text-slate-800"><?php echo $c['subject']; ?></h3>
                                    <span class="badge <?php echo $c['status'] === 'open' ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo strtoupper($c['status']); ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mb-3 leading-relaxed"><?php echo $c['message']; ?></p>
                                <div class="flex justify-between items-center text-[10px] text-slate-400">
                                    <span><?php echo date('d M Y, H:i', strtotime($c['created_at'])); ?></span>
                                    <?php if($c['status'] === 'resolved'): ?>
                                        <span class="text-emerald-600 font-bold">Diselesaikan oleh Operator</span>
                                    <?php else: ?>
                                        <span>Sedang ditinjau</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
