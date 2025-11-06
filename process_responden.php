<?php
session_start();
require_once 'db.php';

$db = new DB();
$koneksi = $db->getConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['captcha_verified']) || $_POST['captcha_verified'] !== 'true') {
    echo json_encode(['status' => 'error', 'message' => 'Captcha verification failed.']);
    exit;
}
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$usia = isset($_POST['usia']) ? (int)$_POST['usia'] : 0;
$jenis_kelamin_text = isset($_POST['jenis_kelamin']) ? trim($_POST['jenis_kelamin']) : '';
$jenis_kelamin = 0;
if ($jenis_kelamin_text == 'Laki-laki') {
    $jenis_kelamin = 1;
} elseif ($jenis_kelamin_text == 'Perempuan') {
    $jenis_kelamin = 2;
}
$pendidikan_id = isset($_POST['pendidikan_id']) ? (int)$_POST['pendidikan_id'] : 0;
$pekerjaan_id = isset($_POST['pekerjaan_id']) ? (int)$_POST['pekerjaan_id'] : 0;
$penghasilan_id = isset($_POST['penghasilan_id']) ? (int)$_POST['penghasilan_id'] : 0;
$nomor_telepon = isset($_POST['nomor_telepon']) ? trim($_POST['nomor_telepon']) : '';
$provinces_id = isset($_POST['provinces_id']) ? (int)$_POST['provinces_id'] : 0;
$regencies_id = isset($_POST['regencies_id']) ? (int)$_POST['regencies_id'] : 0;
$kesediaan_menjadi_responden = isset($_POST['kesediaan_menjadi_responden']) ? (int)$_POST['kesediaan_menjadi_responden'] : 0;

if ($usia <= 0 || $usia > 100 || $jenis_kelamin <= 0 || $pendidikan_id <= 0 || $pekerjaan_id <= 0 || $penghasilan_id <= 0 || empty($nomor_telepon) || $provinces_id <= 0 || $regencies_id <= 0 || $kesediaan_menjadi_responden <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau usia tidak valid. Usia harus antara 1-100 tahun.']);
    exit;
}

if (strlen($nomor_telepon) < 10 || strlen($nomor_telepon) > 13) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor telepon harus antara 10-13 angka']);
    exit;
}

if (!preg_match('/^[0-9]+$/', $nomor_telepon)) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor telepon hanya boleh berisi angka']);
    exit;
}

$nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);
$responden_id_exclude = 0;

if (isset($_SESSION['responden_id_from_uuid']) && $_SESSION['responden_id_from_uuid'] > 0) {
    $responden_id_exclude = (int)$_SESSION['responden_id_from_uuid'];
}

if ($responden_id_exclude <= 0 && isset($_POST['responden_id']) && (int)$_POST['responden_id'] > 0) {
    $responden_id_exclude = (int)$_POST['responden_id'];
}

$check_phone = "SELECT id FROM respondens WHERE nomor_telepon = '$nomor_telepon_esc' AND status = 1";
if ($responden_id_exclude > 0) {
    $check_phone .= " AND id != $responden_id_exclude";
}
$check_phone .= " LIMIT 1";

$result_check = mysqli_query($koneksi, $check_phone);
if ($result_check && mysqli_num_rows($result_check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor telepon ini sudah digunakan. Silakan gunakan nomor telepon yang lain.']);
    exit;
}

mysqli_query($koneksi, "START TRANSACTION");

try {
    $nama_esc = mysqli_real_escape_string($koneksi, $nama);

    $insert_query = "INSERT INTO respondens (
        nama, 
        umur, 
        jenis_kelamin, 
        pendidikan_id, 
        pekerjaan_id, 
        penghasilan_id, 
        nomor_telepon, 
        provinces_id, 
        regencies_id, 
        kesediaan_menjadi_responden,
        status,
        user_input,
        tanggal_input
    ) VALUES (
        " . (!empty($nama) ? "'$nama_esc'" : "NULL") . ",
        $usia,
        $jenis_kelamin,
        $pendidikan_id,
        $pekerjaan_id,
        $penghasilan_id,
        '$nomor_telepon_esc',
        $provinces_id,
        $regencies_id,
        $kesediaan_menjadi_responden,
        1,
        'responden_form',
        NOW()
    )";

    $result = mysqli_query($koneksi, $insert_query);

    if (!$result) {
        throw new Exception('Gagal menyimpan data responden: ' . mysqli_error($koneksi));
    }

    mysqli_query($koneksi, "COMMIT");

    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil terkirim!'
    ]);

} catch (Exception $e) {
    mysqli_query($koneksi, "ROLLBACK");
    
    error_log("Error in process_responden.php: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
    ]);
}

mysqli_close($koneksi);
?>
