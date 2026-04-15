<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$page_title = 'Data Produk';
$msg = '';
$error = '';
$edit = null;

if (isset($_GET['hapus']) && isAdmin()) {
    $pdo->prepare("DELETE FROM produk WHERE ProdukID = ?")->execute([(int)$_GET['hapus']]);
    header('Location: produk.php?msg=hapus');
    exit;
}

if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM produk WHERE ProdukID = ?");
    $edit->execute([(int)$_GET['edit']]);
    $edit = $edit->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['NamaProduk']);
    $harga = (float)$_POST['Harga'];
    $stok = (int)$_POST['Stok'];
    $id = (int)($_POST['ProdukID'] ?? 0);

    if ($id) {
        $pdo->prepare("UPDATE produk SET NamaProduk=?, Harga=?, Stok=? WHERE ProdukID=?")
            ->execute([$nama, $harga, $stok, $id]);
        $msg = 'Produk berhasil diperbarui!';
    } else {
        $pdo->prepare("INSERT INTO produk (NamaProduk, Harga, Stok) VALUES (?,?,?)")
            ->execute([$nama, $harga, $stok]);
        $msg = 'Produk berhasil ditambahkan!';
    }
    header('Location: produk.php?msg=ok');
    exit;
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'] === 'ok' ? 'Data produk berhasil disimpan!' : 'Produk berhasil dihapus!';
}

$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE NamaProduk LIKE ? ORDER BY ProdukID DESC");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM produk ORDER BY ProdukID DESC");
}
$produkList = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/style.css">

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h2><?= $edit ? 'Edit Produk' : 'Tambah Produk' ?></h2>
    <form method="POST">
        <?php if ($edit): ?>
            <input type="hidden" name="ProdukID" value="<?= $edit['ProdukID'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="NamaProduk" value="<?= htmlspecialchars($edit['NamaProduk'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="Harga" step="0.01" value="<?= $edit['Harga'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="Stok" value="<?= $edit['Stok'] ?? '0' ?>" required>
            </div>
        </div>
        <button type="submit" class="btn btn-success"><?= $edit ? 'Update Produk' : 'Tambah Produk' ?></button>
        <?php if ($edit): ?>
            <a href="produk.php" class="btn btn-warning" style="margin-left:8px;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>Daftar Produk</h2>
    <form method="GET" style="margin-bottom:12px;display:flex;gap:8px;">
        <input type="text" name="q" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1;">
        <button type="submit" class="btn btn-primary">Cari</button>
        <?php if ($search): ?><a href="produk.php" class="btn btn-warning">Reset</a><?php endif; ?>
    </form>
    <table>
        <thead>
            <tr><th>#</th><th>Nama Produk</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($produkList as $i => $p): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($p['NamaProduk']) ?></td>
                <td>Rp <?= number_format($p['Harga'], 0, ',', '.') ?></td>
                <td class="<?= $p['Stok'] < 10 ? 'stok-low' : 'stok-ok' ?>"><?= $p['Stok'] ?><?= $p['Stok'] < 10 ? ' ⚠️' : '' ?></td>
                <td>
                    <a href="produk.php?edit=<?= $p['ProdukID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <?php if (isAdmin()): ?>
                    <a href="produk.php?hapus=<?= $p['ProdukID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="page-info">Total: <?= count($produkList) ?> produk</p>
</div>

<?php include '../includes/footer.php'; ?>