<?php
session_start();
require_once 'db.php';

 $db = new DB();
 $koneksi = $db->getConnection();
 if (!$koneksi) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
 }
 $sessionId = session_id();

// Cek apakah request datang dari metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Cek apakah captcha sudah diverifikasi dari sisi klien
// Ini adalah cara alternatif karena validasi sekarang di JS
// Untuk keamanan lebih lanjut, Anda bisa tetap mengecek session captcha di sini
if (!isset($_POST['captcha_verified']) || $_POST['captcha_verified'] !== 'true') {
    echo json_encode(['status' => 'error', 'message' => 'Captcha verification failed.']);
    exit;
}

// Ambil data survey dari session
 $surveyData = $_SESSION['survey_data'];

if (empty($surveyData)) {
    echo json_encode(['status' => 'error', 'message' => 'Survey data not found in session.']);
    exit;
}

// --- KODE LAMA ANDA UNTUK MEMPROSES DATA DIMULAI DI SINI ---
// Get survey ID
 $survei_id = isset($surveyData['survei_id']) ? (int)$surveyData['survei_id'] : 0;

if (!$survei_id) {
    echo json_encode(['status' => 'error', 'message' => 'Survey ID tidak ditemukan']);
    exit;
}

// Get biodata
 $nama = isset($surveyData['nama']) ? trim($surveyData['nama']) : '';
 $usia = isset($surveyData['usia']) ? (int)$surveyData['usia'] : 0;
 $jenis_kelamin_text = isset($surveyData['jenis_kelamin']) ? trim($surveyData['jenis_kelamin']) : '';
 // Konversi teks ke integer: Laki-laki = 1, Perempuan = 2
 $jenis_kelamin = 0;
 if ($jenis_kelamin_text == 'Laki-laki') {
     $jenis_kelamin = 1;
 } elseif ($jenis_kelamin_text == 'Perempuan') {
     $jenis_kelamin = 2;
 }
 $pendidikan_id = isset($surveyData['pendidikan_id']) ? (int)$surveyData['pendidikan_id'] : 0;
 $pekerjaan_id = isset($surveyData['pekerjaan_id']) ? (int)$surveyData['pekerjaan_id'] : 0;
 $pekerjaan_lainnya = isset($surveyData['pekerjaan_lainnya']) ? trim($surveyData['pekerjaan_lainnya']) : '';
 $penghasilan_id = isset($surveyData['penghasilan_id']) ? (int)$surveyData['penghasilan_id'] : 0;
 $nomor_telepon = isset($surveyData['nomor_telepon']) ? trim($surveyData['nomor_telepon']) : '';
 $provinces_id = isset($surveyData['provinces_id']) ? (int)$surveyData['provinces_id'] : 0;
 $regencies_id = isset($surveyData['regencies_id']) ? (int)$surveyData['regencies_id'] : 0;
 $kesediaan_menjadi_responden = isset($surveyData['kesediaan_menjadi_responden']) ? (int)$surveyData['kesediaan_menjadi_responden'] : 0;

// Cek apakah ada responden_id dari UUID (jika akses via UUID)
$responden_id_from_uuid = isset($_SESSION['responden_id_from_uuid']) ? (int)$_SESSION['responden_id_from_uuid'] : null;
$survey_uuid = isset($_SESSION['survey_uuid']) ? trim($_SESSION['survey_uuid']) : null;

// Validate biodata
if (empty($nama) || $usia <= 0 || $usia > 100 || $jenis_kelamin <= 0 || $pendidikan_id <= 0 || $pekerjaan_id <= 0 || $penghasilan_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data pribadi tidak lengkap atau usia tidak valid. Usia harus antara 1-100 tahun.']);
    exit;
}

// Validasi nomor telepon jika ada (exclude responden_id dari UUID)
if (!empty($nomor_telepon)) {
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
    
    // Cek nomor telepon duplikat (exclude responden_id jika ada dari UUID atau session)
    $nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);
    $responden_id_exclude = 0;
    
    // Cek apakah ada responden_id dari UUID (session)
    if (isset($_SESSION['responden_id_from_uuid']) && $_SESSION['responden_id_from_uuid'] > 0) {
        $responden_id_exclude = (int)$_SESSION['responden_id_from_uuid'];
    }
    
    // Atau dari session responden_id (untuk autosave)
    if ($responden_id_exclude <= 0 && isset($_SESSION['responden_id']) && $_SESSION['responden_id'] > 0) {
        $responden_id_exclude = (int)$_SESSION['responden_id'];
    }
    
    // Atau dari surveyData (jika ada responden_id di form)
    if ($responden_id_exclude <= 0 && isset($surveyData['responden_id']) && (int)$surveyData['responden_id'] > 0) {
        $responden_id_exclude = (int)$surveyData['responden_id'];
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
}

// Start transaction
 $db->runSQL("START TRANSACTION");

try {
    $nama_esc = mysqli_real_escape_string($koneksi, $nama);
    $pekerjaan_lainnya_esc = mysqli_real_escape_string($koneksi, $pekerjaan_lainnya);
    $nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);
    $responden_id = null;
    
    // Prioritaskan responden_id dari UUID jika ada
    if ($responden_id_from_uuid && $responden_id_from_uuid > 0) {
        $responden_id = $responden_id_from_uuid;
        
        // Update responden dengan semua data termasuk yang baru
        $update_fields = "
            nama = '$nama_esc',
            umur = $usia,
            jenis_kelamin = $jenis_kelamin,
            pendidikan_id = $pendidikan_id,
            pekerjaan_id = $pekerjaan_id,
            penghasilan_id = $penghasilan_id,
            status = 1,
            user_update = 'survey_online',
            tanggal_update = NOW()
        ";
        
        // Tambahkan field opsional jika ada
        if (!empty($nomor_telepon)) {
            $update_fields .= ", nomor_telepon = '$nomor_telepon_esc'";
        }
        if ($provinces_id > 0) {
            $update_fields .= ", provinces_id = $provinces_id";
        }
        if ($regencies_id > 0) {
            $update_fields .= ", regencies_id = $regencies_id";
        }
        if ($kesediaan_menjadi_responden > 0) {
            $update_fields .= ", kesediaan_menjadi_responden = $kesediaan_menjadi_responden";
        }
        
        $update_responden = "UPDATE respondens SET $update_fields WHERE id = $responden_id";
        $db->runSQL($update_responden);
        
        // Hapus jawaban draft lama untuk update dengan yang baru (hanya yang status = 0)
        $delete_old_jawaban = "DELETE FROM jawaban_responden WHERE responden_id = $responden_id AND status = 0";
        $db->runSQL($delete_old_jawaban);
        
    } elseif (isset($_SESSION['responden_id']) && $_SESSION['responden_id'] > 0) {
        // Cek apakah sudah ada responden draft dari autosave
        $responden_id = (int)$_SESSION['responden_id'];
        
        // Update responden dari draft menjadi final
        $update_fields = "
            nama = '$nama_esc',
            umur = $usia,
            jenis_kelamin = $jenis_kelamin,
            pendidikan_id = $pendidikan_id,
            pekerjaan_id = $pekerjaan_id,
            penghasilan_id = $penghasilan_id,
            status = 1,
            user_update = 'survey_online',
            tanggal_update = NOW()
        ";
        
        // Tambahkan field opsional jika ada
        if (!empty($nomor_telepon)) {
            $update_fields .= ", nomor_telepon = '$nomor_telepon_esc'";
        }
        if ($provinces_id > 0) {
            $update_fields .= ", provinces_id = $provinces_id";
        }
        if ($regencies_id > 0) {
            $update_fields .= ", regencies_id = $regencies_id";
        }
        if ($kesediaan_menjadi_responden > 0) {
            $update_fields .= ", kesediaan_menjadi_responden = $kesediaan_menjadi_responden";
        }
        
        $update_responden = "UPDATE respondens SET $update_fields WHERE id = $responden_id";
        $db->runSQL($update_responden);
        
        // Hapus jawaban draft lama untuk update dengan yang baru (hanya yang status = 0)
        $delete_old_jawaban = "DELETE FROM jawaban_responden WHERE responden_id = $responden_id AND status = 0";
        $db->runSQL($delete_old_jawaban);
    } else {
        // Jika tidak ada draft, buat responden baru
        $insert_fields = "nama, umur, jenis_kelamin, pendidikan_id, pekerjaan_id, penghasilan_id, status, user_input, tanggal_input";
        $insert_values = "'$nama_esc', $usia, $jenis_kelamin, $pendidikan_id, $pekerjaan_id, $penghasilan_id, 1, 'survey_online', NOW()";
        
        // Tambahkan field opsional jika ada
        if (!empty($nomor_telepon)) {
            $insert_fields .= ", nomor_telepon";
            $insert_values .= ", '$nomor_telepon_esc'";
        }
        if ($provinces_id > 0) {
            $insert_fields .= ", provinces_id";
            $insert_values .= ", $provinces_id";
        }
        if ($regencies_id > 0) {
            $insert_fields .= ", regencies_id";
            $insert_values .= ", $regencies_id";
        }
        if ($kesediaan_menjadi_responden > 0) {
            $insert_fields .= ", kesediaan_menjadi_responden";
            $insert_values .= ", $kesediaan_menjadi_responden";
        }
        
        $insert_responden = "INSERT INTO respondens ($insert_fields) VALUES ($insert_values)";
        $db->runSQL($insert_responden);
        
        // Get the last inserted responden ID
        $last_responden = $db->getITEM("SELECT LAST_INSERT_ID() as id");
        $responden_id = $last_responden['id'];
    }
    
    // Note: For pekerjaan "Lainnya", the custom text is stored in jawaban_responden
    // if there's a survey question about it, not as a separate field in respondens
    
    // Process all survey answers
    foreach ($surveyData as $key => $value) {
        if (strpos($key, 'kuesioner_') === 0) {
            $kuesioner_id = (int)str_replace('kuesioner_', '', $key);
            
            // Get question details to determine answer type
            $question = $db->getITEM("SELECT tipe_jawaban FROM kuesioner WHERE id = $kuesioner_id");
            $tipe_jawaban = $question ? $question['tipe_jawaban'] : '';
            
            $jawaban_teks = '';
            $opsi_jawaban_id_value = null;
            
            if (is_array($value)) {
                // Handle checkbox (multiple options) - save each as separate entry
                foreach ($value as $opt_id) {
                    $opt_id_int = (int)$opt_id;
                    $option = $db->getITEM("SELECT teks_opsi FROM opsi_jawaban WHERE id = $opt_id_int");
                    if ($option) {
                        $jawaban_teks = $option['teks_opsi'];
                        $opsi_jawaban_id_value = $opt_id_int;
                        
                        $jawaban_esc = addslashes($jawaban_teks);
                        
                        $insert_jawaban = "
                            INSERT INTO jawaban_responden (responden_id, kuesioner_id, opsi_jawaban_id, jawaban_teks, tanggal_jawab, status, user_input, tanggal_input)
                            VALUES ($responden_id, $kuesioner_id, $opsi_jawaban_id_value, '$jawaban_esc', NOW(), 1, 'survey_online', NOW())
                        ";
                        
                        $db->runSQL($insert_jawaban);
                    }
                }
            } else {
                $value = trim($value);
                
                if ($tipe_jawaban === 'skala') {
                    // For scale/rating, ambil text dari opsi_jawaban berdasarkan ID
                    $value_int = (int)$value;
                    $option = $db->getITEM("SELECT teks_opsi FROM opsi_jawaban WHERE id = $value_int");
                    if ($option) {
                        $jawaban_teks = $option['teks_opsi'];
                        $opsi_jawaban_id_value = $value_int;
                    } else {
                        // Fallback jika tidak ditemukan
                        $jawaban_teks = $value;
                        $opsi_jawaban_id_value = $value_int;
                    }
                } elseif (!empty($value) && is_numeric($value) && $tipe_jawaban === 'pilihan') {
                    // This is an option ID for pilihan
                    $opt_id_int = (int)$value;
                    $option = $db->getITEM("SELECT teks_opsi FROM opsi_jawaban WHERE id = $opt_id_int");
                    if ($option) {
                        $jawaban_teks = $option['teks_opsi'];
                        $opsi_jawaban_id_value = $opt_id_int;
                    } else {
                        $jawaban_teks = $value;
                    }
                } else {
                    // Text answer (isian) or other types
                    $jawaban_teks = $value;
                }
                
                if (!empty($jawaban_teks)) {
                    $jawaban_esc = addslashes($jawaban_teks);
                    
                    $insert_jawaban = "
                        INSERT INTO jawaban_responden (responden_id, kuesioner_id, opsi_jawaban_id, jawaban_teks, tanggal_jawab, status, user_input, tanggal_input)
                        VALUES ($responden_id, $kuesioner_id, " . ($opsi_jawaban_id_value !== null ? $opsi_jawaban_id_value : 'NULL') . ", '$jawaban_esc', NOW(), 1, 'survey_online', NOW())
                    ";
                    
                    $db->runSQL($insert_jawaban);
                }
            }
        }
    }
    
    // Update status_partisipasi di survei_peserta menjadi 'selesai' jika ada UUID
    if ($survey_uuid && $responden_id) {
        $uuid_esc = mysqli_real_escape_string($koneksi, $survey_uuid);
        $update_peserta = "
            UPDATE survei_peserta 
            SET status_partisipasi = 'selesai',
                tanggal_selesai = NOW(),
                persentase_selesai = 100.00,
                tanggal_update = NOW()
            WHERE uuid = '$uuid_esc' 
            AND responden_id = $responden_id
            AND survei_id = $survei_id
        ";
        $db->runSQL($update_peserta);
    }
    
    // Commit transaction
    $db->runSQL("COMMIT");

    // Clear session data
    unset($_SESSION['survey_data']);
    unset($_SESSION['captcha_result']);
    unset($_SESSION['responden_id']);
    unset($_SESSION['survey_uuid']);
    unset($_SESSION['responden_id_from_uuid']);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Survey berhasil dikirim',
        'responden_id' => $responden_id
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $db->runSQL("ROLLBACK");
    
    // Get detailed error message
    $error_msg = $e->getMessage();
    
    // Log error untuk debugging
    error_log("Error in process_survey.php: " . $error_msg);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Check if it's an AUTO_INCREMENT error
    if (strpos($error_msg, "doesn't have a default value") !== false) {
        $error_msg = "Error database: Tabel belum terset dengan benar. Silakan import ulang file survei.sql dengan lengkap.";
    }
    
    // Return error dengan detail untuk debugging
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan saat menyimpan survey: ' . $error_msg,
        'debug' => [
            'error_message' => $error_msg,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>