<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireAdmin();

$page_title = 'Registrasi User';
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $nama = trim($_POST['nama']);
    $role = $_POST['role'];

    $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $cek->execute([$username]);
    if ($cek->fetch()) {
        $error = 'Username sudah digunakan!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama, role) VALUES (?,?,?,?)");
        $stmt->execute([$username, $hash, $nama, $role]);
        $msg = 'User berhasil ditambahkan!';
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/style.css">
<div class="card">
    <h2>Registrasi User Baru</h2>
    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="petugas">Petugas</option>
                    <option value="administrator">Administrator</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Daftarkan User</button>
    </form>
</div>

<div class="card">
    <h2>Daftar User</h2>
    <table>
        <thead>
            <tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $i => $u): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($u['nama']) ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><span class="badge-role <?= $u['role'] === 'administrator' ? 'admin' : '' ?>"><?= $u['role'] ?></span></td>
                <td>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <a href="?hapus=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user ini?')">Hapus</a>
                    <?php else: ?>
                    <span style="font-size:12px;color:#aaa;">Akun aktif</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
    header('Location: registrasi.php');
    exit;
}
?>

<?php include '../includes/footer.php'; ?>

<a href="../pages/logout.php">logout</a>