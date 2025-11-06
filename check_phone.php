<?php
session_start();
require_once 'db.php';

$db = new DB();
$koneksi = $db->getConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$nomor_telepon = isset($_POST['nomor_telepon']) ? trim($_POST['nomor_telepon']) : '';
$responden_id = isset($_POST['responden_id']) ? (int)$_POST['responden_id'] : 0;

if (empty($nomor_telepon)) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor telepon tidak boleh kosong']);
    exit;
}

// Validasi panjang nomor telepon (min 10, max 13)
if (strlen($nomor_telepon) < 10 || strlen($nomor_telepon) > 13) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor telepon harus antara 10-13 angka']);
    exit;
}

// Validasi hanya angka
if (!preg_match('/^[0-9]+$/', $nomor_telepon)) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor telepon hanya boleh berisi angka']);
    exit;
}

// Escape untuk mencegah SQL injection
$nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);

// Cek apakah nomor telepon sudah ada di database
// Jika ada responden_id (dari UUID), exclude responden tersebut dari pengecekan
$query = "SELECT id, nama FROM respondens WHERE nomor_telepon = '$nomor_telepon_esc' AND status = 1";
if ($responden_id > 0) {
    $query .= " AND id != $responden_id";
}
$query .= " LIMIT 1";

$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        'status' => 'duplicate',
        'message' => 'Nomor telepon ini sudah terdaftar. Silakan gunakan nomor telepon yang lain.',
        'existing_name' => $row['nama']
    ]);
} else {
    echo json_encode([
        'status' => 'available',
        'message' => 'Nomor telepon tersedia'
    ]);
}

mysqli_close($koneksi);
?>

