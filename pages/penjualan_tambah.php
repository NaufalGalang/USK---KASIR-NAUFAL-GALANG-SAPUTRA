<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$page_title = 'Transaksi Baru';

$produkList = $pdo->query("SELECT * FROM produk WHERE Stok > 0 ORDER BY NamaProduk")->fetchAll(PDO::FETCH_ASSOC);
$pelangganList = $pdo->query("SELECT * FROM pelanggan ORDER BY NamaPelanggan")->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pelangganID = !empty($_POST['PelangganID']) ? (int)$_POST['PelangganID'] : null;
    $tanggal = $_POST['TanggalPenjualan'];
    $produkIDs = $_POST['produk_id'] ?? [];
    $jumlahArr = $_POST['jumlah'] ?? [];

    if (empty($produkIDs)) {
        $error = 'Tambahkan minimal 1 produk!';
    } else {
        
        $totalHarga = 0;
        $items = [];
        $valid = true;

        foreach ($produkIDs as $idx => $pid) {
            $pid = (int)$pid;
            $jml = (int)($jumlahArr[$idx] ?? 0);
            if ($jml <= 0) continue;

            $prodStmt = $pdo->prepare("SELECT * FROM produk WHERE ProdukID = ?");
            $prodStmt->execute([$pid]);
            $prod = $prodStmt->fetch(PDO::FETCH_ASSOC);

            if (!$prod || $prod['Stok'] < $jml) {
                $error = "Stok tidak cukup untuk produk: " . ($prod['NamaProduk'] ?? '#'.$pid);
                $valid = false;
                break;
            }
            $subtotal = $prod['Harga'] * $jml;
            $totalHarga += $subtotal;
            $items[] = ['pid' => $pid, 'jml' => $jml, 'subtotal' => $subtotal];
        }

        if ($valid && !empty($items)) {
    
            $pdo->prepare("INSERT INTO penjualan (TanggalPenjualan, TotalHarga, PelangganID) VALUES (?,?,?)")
                ->execute([$tanggal, $totalHarga, $pelangganID]);
            $penjualanID = $pdo->lastInsertId();

            foreach ($items as $item) {
                $pdo->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, JumlahProduk, Subtotal) VALUES (?,?,?,?)")
                    ->execute([$penjualanID, $item['pid'], $item['jml'], $item['subtotal']]);
                $pdo->prepare("UPDATE produk SET Stok = Stok - ? WHERE ProdukID = ?")
                    ->execute([$item['jml'], $item['pid']]);
            }

            header('Location: penjualan_detail.php?id=' . $penjualanID . '&msg=ok');
            exit;
        }
    }
}

include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/style.css">
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="card">
    <h2>Transaksi Penjualan Baru</h2>
    <form method="POST" id="formJual">
        <div class="form-row">
            <div class="form-group">
                <label>Tanggal Transaksi</label>
                <input type="date" name="TanggalPenjualan" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Pelanggan (opsional)</label>
                <select name="PelangganID">
                    <option value="">-- Pelanggan Umum --</option>
                    <?php foreach ($pelangganList as $pl): ?>
                    <option value="<?= $pl['PelangganID'] ?>"><?= htmlspecialchars($pl['NamaPelanggan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h3 style="margin: 16px 0 10px;font-size:15px;color:#2c3e50;">Detail Produk</h3>
        <div id="itemWrap"></div>

        <button type="button" onclick="tambahBaris()" class="btn btn-primary" style="margin-bottom:16px;">+ Tambah Produk</button>

        <div style="background:#f8f9fa;padding:16px;border-radius:8px;margin-bottom:16px;">
            <strong>Total: Rp <span id="totalDisplay">0</span></strong>
        </div>

        <button type="submit" class="btn btn-success">Simpan Transaksi</button>
        <a href="penjualan.php" class="btn btn-warning" style="margin-left:8px;">Batal</a>
    </form>
</div>

<script>
var produkData = <?= json_encode(array_column($produkList, null, 'ProdukID')) ?>;
var produkList = <?= json_encode($produkList) ?>;
var rowCount = 0;

function tambahBaris() {
    rowCount++;
    var wrap = document.getElementById('itemWrap');
    var div = document.createElement('div');
    div.id = 'row_' + rowCount;
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center;flex-wrap:wrap;';
    
    var opts = '<option value="">-- Pilih Produk --</option>';
    produkList.forEach(function(p) {
        opts += '<option value="' + p.ProdukID + '" data-harga="' + p.Harga + '">' 
             + p.NamaProduk + ' (Rp ' + Number(p.Harga).toLocaleString('id') + ') - Stok: ' + p.Stok + '</option>';
    });
    
    div.innerHTML = 
        '<select name="produk_id[]" onchange="hitungTotal()" style="flex:2;padding:8px;border:1px solid #ddd;border-radius:6px;">' + opts + '</select>' +
        '<input type="number" name="jumlah[]" min="1" value="1" onchange="hitungTotal()" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px;" placeholder="Jumlah">' +
        '<span class="subtotal-label" style="flex:1;font-size:14px;color:#555;">Rp 0</span>' +
        '<button type="button" onclick="hapusBaris(' + rowCount + ')" class="btn btn-danger btn-sm">✕</button>';
    
    wrap.appendChild(div);
}

function hapusBaris(id) {
    var el = document.getElementById('row_' + id);
    if (el) el.remove();
    hitungTotal();
}

function hitungTotal() {
    var rows = document.querySelectorAll('#itemWrap > div');
    var total = 0;
    rows.forEach(function(row) {
        var sel = row.querySelector('select');
        var jml = row.querySelector('input[type=number]');
        var lbl = row.querySelector('.subtotal-label');
        if (sel && sel.value && jml) {
            var opt = sel.options[sel.selectedIndex];
            var harga = parseFloat(opt.getAttribute('data-harga')) || 0;
            var jumlah = parseInt(jml.value) || 0;
            var sub = harga * jumlah;
            total += sub;
            if (lbl) lbl.textContent = 'Rp ' + sub.toLocaleString('id');
        } else {
            if (lbl) lbl.textContent = 'Rp 0';
        }
    });
    document.getElementById('totalDisplay').textContent = total.toLocaleString('id');
}


tambahBaris();
</script>

<?php include '../includes/footer.php'; ?>