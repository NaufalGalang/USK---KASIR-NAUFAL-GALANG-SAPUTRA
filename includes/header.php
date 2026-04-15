<?php
$base = '/usk-kasir';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Aplikasi Kasir' ?></title>
<link rel="stylesheet" href="<?= $base ?>/asset/style.css">
</head>
<body>

<?php if (isLoggedIn()): ?>
<div class="sidebar">

    <a href="<?= $base ?>/pages/dashboard.php">Dashboard</a>

    <?php if (isAdmin()): ?>
        <a href="<?= $base ?>/pages/registrasi.php">Registrasi</a>
    <?php endif; ?>

    <a href="<?= $base ?>/pages/produk.php">Produk</a>
    <a href="<?= $base ?>/pages/pelanggan.php">Pelanggan</a>
    <a href="<?= $base ?>/pages/penjualan.php">Penjualan</a>
    <a href="<?= $base ?>/pages/stok.php">Stok</a>

    <hr style="border-color:#444; margin:12px 0;">

    <div style="font-size:12px; margin-bottom:10px;">
        <?= htmlspecialchars($_SESSION['nama']) ?><br>
        (<?= $_SESSION['role'] ?>)
    </div>

    <a href="<?= $base ?>/pages/logout.php">Logout</a>
</div>
<?php endif; ?>

<div class="main-content">