<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$page_title = 'Dashboard';

$totalProduk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$totalPelanggan = $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
$totalPenjualan = $pdo->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(TotalHarga),0) FROM penjualan")->fetchColumn();
$stokRendah = $pdo->query("SELECT COUNT(*) FROM produk WHERE Stok < 10")->fetchColumn();

$recentSales = $pdo->query("
    SELECT p.PenjualanID, p.TanggalPenjualan, p.TotalHarga, pl.NamaPelanggan
    FROM penjualan p
    LEFT JOIN pelanggan pl ON p.PelangganID = pl.PelangganID
    ORDER BY p.PenjualanID DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/style.css">

<div class="stats-grid">
    <div class="stat-card">

        <div class="value"><?= $totalProduk ?></div>
        <div class="label">Total Produk</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= $totalPelanggan ?></div>
        <div class="label">Total Pelanggan</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= $totalPenjualan ?></div>
        <div class="label">Total Transaksi</div>
    </div>
    <div class="stat-card">
        <div class="value">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></div>
        <div class="label">Total Pendapatan</div>
    </div>
    <?php if ($stokRendah > 0): ?>
    <div class="stat-card" style="border: 2px solid #e74c3c;">
        <div class="value" style="color:#e74c3c;"><?= $stokRendah ?></div>
        <div class="label">Produk Stok Rendah</div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Transaksi Terbaru</h2>
    <?php if (empty($recentSales)): ?>
        <p style="color:#888;font-size:14px;">Belum ada transaksi.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#ID</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentSales as $s): ?>
            <tr>
                <td>#<?= $s['PenjualanID'] ?></td>
                <td><?= date('d/m/Y', strtotime($s['TanggalPenjualan'])) ?></td>
                <td><?= htmlspecialchars($s['NamaPelanggan'] ?? 'Umum') ?></td>
                <td>Rp <?= number_format($s['TotalHarga'], 0, ',', '.') ?></td>
                <td><a href="penjualan_detail.php?id=<?= $s['PenjualanID'] ?>" class="btn btn-sm btn-primary">Detail</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <div style="margin-top:12px;">
        <a href="penjualan.php" class="btn btn-success">Lihat Semua Penjualan</a>
        <a href="penjualan_tambah.php" class="btn btn-primary" style="margin-left:8px;">+ Transaksi Baru</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<a href="../pages/logout.php">logout</a>