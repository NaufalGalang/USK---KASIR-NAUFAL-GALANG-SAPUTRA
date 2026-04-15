<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$page_title = 'Data Pelanggan';
$msg = '';
$edit = null;

if (isset($_GET['hapus']) && isAdmin()) {
    $pdo->prepare("DELETE FROM pelanggan WHERE PelangganID = ?")->execute([(int)$_GET['hapus']]);
    header('Location: pelanggan.php?msg=hapus');
    exit;
}

if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM pelanggan WHERE PelangganID = ?");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['NamaPelanggan']);
    $alamat = trim($_POST['Alamat']);
    $telp = trim($_POST['NomorTelepon']);
    $id = (int)($_POST['PelangganID'] ?? 0);

    if ($id) {
        $pdo->prepare("UPDATE pelanggan SET NamaPelanggan=?, Alamat=?, NomorTelepon=? WHERE PelangganID=?")
            ->execute([$nama, $alamat, $telp, $id]);
    } else {
        $pdo->prepare("INSERT INTO pelanggan (NamaPelanggan, Alamat, NomorTelepon) VALUES (?,?,?)")
            ->execute([$nama, $alamat, $telp]);
    }
    header('Location: pelanggan.php?msg=ok');
    exit;
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'] === 'ok' ? 'Data pelanggan berhasil disimpan!' : 'Pelanggan berhasil dihapus!';
}

$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE NamaPelanggan LIKE ? OR NomorTelepon LIKE ? ORDER BY PelangganID DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY PelangganID DESC");
}
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/style.css">
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card">
    <h2><?= $edit ? 'Edit Pelanggan' : 'Tambah Pelanggan' ?></h2>
    <form method="POST">
        <?php if ($edit): ?>
            <input type="hidden" name="PelangganID" value="<?= $edit['PelangganID'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="text" name="NamaPelanggan" value="<?= htmlspecialchars($edit['NamaPelanggan'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="NomorTelepon" value="<?= htmlspecialchars($edit['NomorTelepon'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="Alamat" rows="2"><?= htmlspecialchars($edit['Alamat'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success"><?= $edit ? 'Update Pelanggan' : 'Tambah Pelanggan' ?></button>
        <?php if ($edit): ?>
            <a href="pelanggan.php" class="btn btn-warning" style="margin-left:8px;">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>Daftar Pelanggan</h2>
    <form method="GET" style="margin-bottom:12px;display:flex;gap:8px;">
        <input type="text" name="q" placeholder="Cari nama / telepon..." value="<?= htmlspecialchars($search) ?>" style="padding:8px;border:1px solid #ddd;border-radius:6px;flex:1;">
        <button type="submit" class="btn btn-primary">Cari</button>
        <?php if ($search): ?><a href="pelanggan.php" class="btn btn-warning">Reset</a><?php endif; ?>
    </form>
    <table>
        <thead>
            <tr><th>#</th><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($list as $i => $p): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($p['NamaPelanggan']) ?></td>
                <td><?= htmlspecialchars($p['NomorTelepon'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['Alamat'] ?? '-') ?></td>
                <td>
                    <a href="pelanggan.php?edit=<?= $p['PelangganID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <?php if (isAdmin()): ?>
                    <a href="pelanggan.php?hapus=<?= $p['PelangganID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pelanggan ini?')">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="page-info">Total: <?= count($list) ?> pelanggan</p>
</div>

<?php include '../includes/footer.php'; ?>