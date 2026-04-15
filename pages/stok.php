<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$page_title = 'Stok Barang';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    $pid = (int)$_POST['ProdukID'];
    $stok = (int)$_POST['Stok'];
    $pdo->prepare("UPDATE produk SET Stok = ? WHERE ProdukID = ?")->execute([$stok, $pid]);
    header('Location: stok.php?msg=ok');
    exit;
}

if (isset($_GET['msg'])) $msg = 'Stok berhasil diperbarui!';

$filter = $_GET['filter'] ?? 'semua';
$sql = "SELECT * FROM produk";
if ($filter === 'rendah') $sql .= " WHERE Stok < 10";
elseif ($filter === 'habis') $sql .= " WHERE Stok = 0";
$sql .= " ORDER BY Stok ASC";

$list = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$totalStok = $pdo->query("SELECT SUM(Stok) FROM produk")->fetchColumn();
$produkHabis = $pdo->query("SELECT COUNT(*) FROM produk WHERE Stok = 0")->fetchColumn();
$produkRendah = $pdo->query("SELECT COUNT(*) FROM produk WHERE Stok > 0 AND Stok < 10")->fetchColumn();

include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/style.css">
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card">
        <div class="value"><?= $totalStok ?></div>
        <div class="label">Total Item Stok</div>
    </div>
    <div class="stat-card" style="<?= $produkRendah > 0 ? 'border:2px solid #f39c12;' : '' ?>">
        <div class="value" style="color:#f39c12;"><?= $produkRendah ?></div>
        <div class="label">Stok Menipis (&lt;10)</div>
    </div>
    <div class="stat-card" style="<?= $produkHabis > 0 ? 'border:2px solid #e74c3c;' : '' ?>">
        <div class="value" style="color:#e74c3c;"><?= $produkHabis ?></div>
        <div class="label">Produk Habis</div>
    </div>
</div>

<div class="card">
    <h2>Monitor Stok Barang</h2>
    <div style="margin-bottom:12px;">
        <a href="stok.php" class="btn btn-primary btn-sm <?= $filter==='semua'?'':'btn-warning' ?>">Semua</a>
        <a href="stok.php?filter=rendah" class="btn btn-sm btn-warning" style="margin-left:4px;">Stok Rendah</a>
        <a href="stok.php?filter=habis" class="btn btn-sm btn-danger" style="margin-left:4px;">Habis</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>Nama Produk</th><th>Harga</th><th>Stok Sekarang</th><th>Status</th>
                <?php if (isAdmin()): ?><th>Update Stok</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $i => $p): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($p['NamaProduk']) ?></td>
                <td>Rp <?= number_format($p['Harga'], 0, ',', '.') ?></td>
                <td class="<?= $p['Stok'] == 0 ? 'stok-low' : ($p['Stok'] < 10 ? 'stok-low' : 'stok-ok') ?>">
                    <?= $p['Stok'] ?>
                </td>
                <td>
                    <?php if ($p['Stok'] == 0): ?>
                        <span style="color:#e74c3c;font-weight:700;">HABIS</span>
                    <?php elseif ($p['Stok'] < 10): ?>
                        <span style="color:#f39c12;font-weight:700;">MENIPIS</span>
                    <?php else: ?>
                        <span style="color:#27ae60;">TERSEDIA</span>
                    <?php endif; ?>
                </td>
                <?php if (isAdmin()): ?>
                <td>
                    <form method="POST" style="display:flex;gap:6px;align-items:center;">
                        <input type="hidden" name="ProdukID" value="<?= $p['ProdukID'] ?>">
                        <input type="number" name="Stok" value="<?= $p['Stok'] ?>" min="0" style="width:70px;padding:4px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
                        <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>