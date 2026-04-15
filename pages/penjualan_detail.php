<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$page_title = 'Detail Transaksi';
$id = (int)($_GET['id'] ?? 0);

$penjualan = $pdo->prepare("
    SELECT p.*, pl.NamaPelanggan, pl.NomorTelepon, pl.Alamat
    FROM penjualan p
    LEFT JOIN pelanggan pl ON p.PelangganID = pl.PelangganID
    WHERE p.PenjualanID = ?
");
$penjualan->execute([$id]);
$penjualan = $penjualan->fetch(PDO::FETCH_ASSOC);

if (!$penjualan) {
    header('Location: penjualan.php');
    exit;
}

$details = $pdo->prepare("
    SELECT d.*, pr.NamaProduk, pr.Harga
    FROM detailpenjualan d
    JOIN produk pr ON d.ProdukID = pr.ProdukID
    WHERE d.PenjualanID = ?
");
$details->execute([$id]);
$details = $details->fetchAll(PDO::FETCH_ASSOC);

$msg = isset($_GET['msg']) ? 'Transaksi berhasil disimpan!' : '';

include '../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<link rel="stylesheet" href="../assets/style.css">
<div style="display:flex;gap:8px;margin-bottom:16px;">
    <a href="penjualan.php" class="btn btn-warning">← Kembali</a>
</div>

<div class="card" id="struk">
    <div style="text-align:center;border-bottom:2px dashed #ccc;padding-bottom:16px;margin-bottom:16px;">
        <h2 style="border:none;margin:0;font-size:22px;">🛒 APLIKASI KASIR</h2>
        <p style="font-size:13px;color:#888;">UKK RPL Paket 4</p>
        <p style="font-size:13px;">No. Transaksi: <strong>#<?= $penjualan['PenjualanID'] ?></strong></p>
        <p style="font-size:13px;">Tanggal: <?= date('d F Y', strtotime($penjualan['TanggalPenjualan'])) ?></p>
        <?php if ($penjualan['NamaPelanggan']): ?>
        <p style="font-size:13px;">Pelanggan: <strong><?= htmlspecialchars($penjualan['NamaPelanggan']) ?></strong></p>
        <?php else: ?>
        <p style="font-size:13px;">Pelanggan: Umum</p>
        <?php endif; ?>
    </div>

    <table style="font-size:14px;">
        <thead>
            <tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($details as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['NamaProduk']) ?></td>
                <td>Rp <?= number_format($d['Harga'], 0, ',', '.') ?></td>
                <td><?= $d['JumlahProduk'] ?></td>
                <td>Rp <?= number_format($d['Subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="border-top: 2px solid #333; font-weight:bold; background:#f0f2f5;">
                <td colspan="3" style="padding:10px 12px;">TOTAL</td>
                <td style="padding:10px 12px;font-size:16px;">Rp <?= number_format($penjualan['TotalHarga'], 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align:center;border-top:2px dashed #ccc;padding-top:16px;margin-top:16px;">
        <p style="font-size:13px;color:#888;">Terima kasih atas kunjungan Anda! 😊</p>
    </div>
</div>

<style>
@media print {
    .navbar, .btn, .alert { display: none !important; }
    body { background: white; }
    .container { margin: 0; }
    #struk { box-shadow: none; border: 1px solid #ccc; }
}
</style>

<?php include '../includes/footer.php'; ?>