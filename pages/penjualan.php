<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$page_title = 'Data Penjualan';

if (isset($_GET['hapus']) && isAdmin()) {
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM detailpenjualan WHERE PenjualanID = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM penjualan WHERE PenjualanID = ?")->execute([$id]);
    header('Location: penjualan.php?msg=hapus');
    exit;
}

$msg = isset($_GET['msg']) ? ($_GET['msg'] === 'ok' ? 'Transaksi berhasil disimpan!' : 'Transaksi berhasil dihapus!') : '';

$search = trim($_GET['q'] ?? '');
$tgl_dari = $_GET['tgl_dari'] ?? '';
$tgl_sampai = $_GET['tgl_sampai'] ?? '';

$sql = "SELECT p.*, pl.NamaPelanggan FROM penjualan p LEFT JOIN pelanggan pl ON p.PelangganID = pl.PelangganID WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND pl.NamaPelanggan LIKE ?";
    $params[] = "%$search%";
}
if ($tgl_dari) {
    $sql .= " AND p.TanggalPenjualan >= ?";
    $params[] = $tgl_dari;
}
if ($tgl_sampai) {
    $sql .= " AND p.TanggalPenjualan <= ?";
    $params[] = $tgl_sampai;
}
$sql .= " ORDER BY p.PenjualanID DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAll = array_sum(array_column($list, 'TotalHarga'));

include '../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<link rel="stylesheet" href="../assets/style.css">
<div class="card">
    <h2>Data Penjualan</h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;align-items:flex-end;">
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
            <input type="text" name="q" placeholder="Cari pelanggan..." value="<?= htmlspecialchars($search) ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;">
            <input type="date" name="tgl_dari" value="<?= htmlspecialchars($tgl_dari) ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;">
            <input type="date" name="tgl_sampai" value="<?= htmlspecialchars($tgl_sampai) ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;">
            <a href="penjualan.php" class="btn btn-warning">Reset</a>
        </form>
        <a href="penjualan_tambah.php" class="btn btn-success">+ Transaksi Baru</a>
    </div>

    <table>
        <thead>
            <tr><th>#ID</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($list as $p): ?>
            <tr>
                <td>#<?= $p['PenjualanID'] ?></td>
                <td><?= date('d/m/Y', strtotime($p['TanggalPenjualan'])) ?></td>
                <td><?= htmlspecialchars($p['NamaPelanggan'] ?? 'Pelanggan Umum') ?></td>
                <td>Rp <?= number_format($p['TotalHarga'], 0, ',', '.') ?></td>
                <td>
                    <a href="penjualan_detail.php?id=<?= $p['PenjualanID'] ?>" class="btn btn-primary btn-sm">Detail</a>
                    <?php if (isAdmin()): ?>
                    <a href="penjualan.php?hapus=<?= $p['PenjualanID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus transaksi ini?')">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f0f2f5;font-weight:700;">
                <td colspan="3" style="padding:10px 12px;">Total Keseluruhan</td>
                <td colspan="2" style="padding:10px 12px;">Rp <?= number_format($totalAll, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
    <p class="page-info">Total: <?= count($list) ?> transaksi</p>
</div>

<?php include '../includes/footer.php'; ?>