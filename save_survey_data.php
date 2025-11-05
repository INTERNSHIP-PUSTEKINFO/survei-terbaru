<?php
session_start();
require_once 'db.php';

// Cek apakah request datang dari metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$surveiId = isset($_POST['survei_id']) ? (int)$_POST['survei_id'] : 0;
if ($surveiId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID survei tidak ditemukan']);
    exit;
}

// Simpan semua data dari form ke session untuk digunakan saat submit akhir
// Pastikan semua data termasuk jawaban kuesioner tersimpan
$_SESSION['survey_data'] = $_POST;
error_log("Survey data saved to session: " . json_encode(array_keys($_POST)));

// Siapkan koneksi database
try {
    $db = new DB();
    $koneksi = $db->getConnection();

    if (!$koneksi) {
        throw new Exception('Koneksi database gagal: ' . mysqli_connect_error());
    }

    // Cek apakah ada responden_id dari UUID (prioritas utama)
    $responden_id_from_uuid = isset($_SESSION['responden_id_from_uuid']) ? (int)$_SESSION['responden_id_from_uuid'] : null;
    $survey_uuid = isset($_SESSION['survey_uuid']) ? trim($_SESSION['survey_uuid']) : null;

    // Ambil data biodata
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $usia = isset($_POST['usia']) ? (int)$_POST['usia'] : 0;
    $jenis_kelamin_text = isset($_POST['jenis_kelamin']) ? trim($_POST['jenis_kelamin']) : '';
    // Konversi teks ke integer: Laki-laki = 1, Perempuan = 2
    $jenis_kelamin = 0;
    if ($jenis_kelamin_text == 'Laki-laki') {
        $jenis_kelamin = 1;
    } elseif ($jenis_kelamin_text == 'Perempuan') {
        $jenis_kelamin = 2;
    }
    $pendidikan_id = isset($_POST['pendidikan_id']) ? (int)$_POST['pendidikan_id'] : 0;
    $pekerjaan_id = isset($_POST['pekerjaan_id']) ? (int)$_POST['pekerjaan_id'] : 0;
    $pekerjaan_lainnya = isset($_POST['pekerjaan_lainnya']) ? trim($_POST['pekerjaan_lainnya']) : '';
    $penghasilan_id = isset($_POST['penghasilan_id']) ? (int)$_POST['penghasilan_id'] : 0;
    $nomor_telepon = isset($_POST['nomor_telepon']) ? trim($_POST['nomor_telepon']) : '';
    $provinces_id = isset($_POST['provinces_id']) ? (int)$_POST['provinces_id'] : 0;
    $regencies_id = isset($_POST['regencies_id']) ? (int)$_POST['regencies_id'] : 0;
    $kesediaan_menjadi_responden = isset($_POST['kesediaan_menjadi_responden']) ? (int)$_POST['kesediaan_menjadi_responden'] : 0;

    // Start transaction
    mysqli_query($koneksi, "START TRANSACTION");

    $responden_id = null;

    // Prioritaskan responden_id dari UUID jika ada
    if ($responden_id_from_uuid && $responden_id_from_uuid > 0) {
        $responden_id = $responden_id_from_uuid;
        
        // Update responden dengan semua data yang ada (bahkan jika belum lengkap)
        $nama_esc = mysqli_real_escape_string($koneksi, $nama);
        $nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);
        
        $update_fields = array();
        
        if (!empty($nama)) {
            $update_fields[] = "nama = '{$nama_esc}'";
        }
        if ($usia > 0) {
            $update_fields[] = "umur = {$usia}";
        }
        if ($jenis_kelamin > 0) {
            $update_fields[] = "jenis_kelamin = {$jenis_kelamin}";
        }
        if ($pendidikan_id > 0) {
            $update_fields[] = "pendidikan_id = {$pendidikan_id}";
        }
        if ($pekerjaan_id > 0) {
            $update_fields[] = "pekerjaan_id = {$pekerjaan_id}";
        }
        if ($penghasilan_id > 0) {
            $update_fields[] = "penghasilan_id = {$penghasilan_id}";
        }
        if (!empty($nomor_telepon)) {
            $update_fields[] = "nomor_telepon = '{$nomor_telepon_esc}'";
        }
        if ($provinces_id > 0) {
            $update_fields[] = "provinces_id = {$provinces_id}";
        }
        if ($regencies_id > 0) {
            $update_fields[] = "regencies_id = {$regencies_id}";
        }
        if ($kesediaan_menjadi_responden > 0) {
            $update_fields[] = "kesediaan_menjadi_responden = {$kesediaan_menjadi_responden}";
        }
        
        // Tambahkan timestamp update (selalu update timestamp meskipun tidak ada field lain)
        $update_fields[] = "tanggal_update = NOW()";
        $update_fields[] = "user_update = 'survey_online_autosave'";
        
        // Update selalu dilakukan (minimal update timestamp)
        $updateQuery = "UPDATE respondens SET " . implode(", ", $update_fields) . " WHERE id = {$responden_id}";
        $resultUpdate = mysqli_query($koneksi, $updateQuery);
        if (!$resultUpdate) {
            throw new Exception('Gagal update responden: ' . mysqli_error($koneksi));
        }
        
    } else {
        // Jika tidak ada UUID, gunakan session atau buat baru
        $sessionId = session_id();
        $sessionEscaped = mysqli_real_escape_string($koneksi, $sessionId);
        
        // Cek apakah sudah ada responden draft untuk session ini
        $checkResponden = "SELECT id FROM respondens WHERE user_input = 'survey_online_draft_{$sessionEscaped}' ORDER BY tanggal_input DESC LIMIT 1";
        $resultCheck = mysqli_query($koneksi, $checkResponden);
        
        if ($resultCheck && mysqli_num_rows($resultCheck) > 0) {
            // Update responden yang sudah ada
            $row = mysqli_fetch_assoc($resultCheck);
            $responden_id = $row['id'];
            
            $nama_esc = mysqli_real_escape_string($koneksi, $nama);
            $nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);
            
            $update_fields = array();
            if (!empty($nama)) {
                $update_fields[] = "nama = '{$nama_esc}'";
            }
            if ($usia > 0) {
                $update_fields[] = "umur = {$usia}";
            }
            if ($jenis_kelamin > 0) {
                $update_fields[] = "jenis_kelamin = {$jenis_kelamin}";
            }
            if ($pendidikan_id > 0) {
                $update_fields[] = "pendidikan_id = {$pendidikan_id}";
            }
            if ($pekerjaan_id > 0) {
                $update_fields[] = "pekerjaan_id = {$pekerjaan_id}";
            }
            if ($penghasilan_id > 0) {
                $update_fields[] = "penghasilan_id = {$penghasilan_id}";
            }
            if (!empty($nomor_telepon)) {
                $update_fields[] = "nomor_telepon = '{$nomor_telepon_esc}'";
            }
            if ($provinces_id > 0) {
                $update_fields[] = "provinces_id = {$provinces_id}";
            }
            if ($regencies_id > 0) {
                $update_fields[] = "regencies_id = {$regencies_id}";
            }
            if ($kesediaan_menjadi_responden > 0) {
                $update_fields[] = "kesediaan_menjadi_responden = {$kesediaan_menjadi_responden}";
            }
            $update_fields[] = "tanggal_update = NOW()";
            
            // Update selalu dilakukan (minimal update timestamp)
            $updateResponden = "UPDATE respondens SET " . implode(", ", $update_fields) . " WHERE id = {$responden_id}";
            $resultUpdate = mysqli_query($koneksi, $updateResponden);
            if (!$resultUpdate) {
                throw new Exception('Gagal update responden: ' . mysqli_error($koneksi));
            }
        } else {
            // Buat responden baru jika ada minimal data
            if (!empty($nama) || $usia > 0 || $jenis_kelamin > 0) {
                $nama_esc = mysqli_real_escape_string($koneksi, $nama);
                $nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);
                
                $insert_fields = array();
                $insert_values = array();
                
                if (!empty($nama)) {
                    $insert_fields[] = "nama";
                    $insert_values[] = "'{$nama_esc}'";
                }
                if ($usia > 0) {
                    $insert_fields[] = "umur";
                    $insert_values[] = $usia;
                }
                if ($jenis_kelamin > 0) {
                    $insert_fields[] = "jenis_kelamin";
                    $insert_values[] = $jenis_kelamin;
                }
                if ($pendidikan_id > 0) {
                    $insert_fields[] = "pendidikan_id";
                    $insert_values[] = $pendidikan_id;
                }
                if ($pekerjaan_id > 0) {
                    $insert_fields[] = "pekerjaan_id";
                    $insert_values[] = $pekerjaan_id;
                }
                if ($penghasilan_id > 0) {
                    $insert_fields[] = "penghasilan_id";
                    $insert_values[] = $penghasilan_id;
                }
                if (!empty($nomor_telepon)) {
                    $insert_fields[] = "nomor_telepon";
                    $insert_values[] = "'{$nomor_telepon_esc}'";
                }
                if ($provinces_id > 0) {
                    $insert_fields[] = "provinces_id";
                    $insert_values[] = $provinces_id;
                }
                if ($regencies_id > 0) {
                    $insert_fields[] = "regencies_id";
                    $insert_values[] = $regencies_id;
                }
                if ($kesediaan_menjadi_responden > 0) {
                    $insert_fields[] = "kesediaan_menjadi_responden";
                    $insert_values[] = $kesediaan_menjadi_responden;
                }
                
                $insert_fields[] = "status";
                $insert_values[] = "0";
                $insert_fields[] = "user_input";
                $insert_values[] = "'survey_online_draft_{$sessionEscaped}'";
                $insert_fields[] = "tanggal_input";
                $insert_values[] = "NOW()";
                
                $insertResponden = "INSERT INTO respondens (" . implode(", ", $insert_fields) . ") VALUES (" . implode(", ", $insert_values) . ")";
                $resultInsert = mysqli_query($koneksi, $insertResponden);
                if (!$resultInsert) {
                    throw new Exception('Gagal insert responden: ' . mysqli_error($koneksi));
                }
                $responden_id = mysqli_insert_id($koneksi);
            }
        }
    }

    // Simpan responden_id ke session untuk digunakan di autosave berikutnya
    // Pastikan responden_id ada (baik dari UUID atau dari create/update sebelumnya)
    if (!$responden_id && isset($_SESSION['responden_id']) && $_SESSION['responden_id'] > 0) {
        $responden_id = (int)$_SESSION['responden_id'];
    }
    
    // Cek apakah ada jawaban kuesioner yang perlu disimpan
    $hasJawabanKuesioner = false;
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'kuesioner_') === 0) {
            $hasJawabanKuesioner = true;
            break;
        }
    }
    
    // Jika masih tidak ada responden_id, coba buat responden baru dari data yang ada
    // Buat responden baru jika ada minimal data biodata ATAU ada jawaban kuesioner
    if ((!$responden_id || $responden_id <= 0) && (!empty($nama) || $usia > 0 || $jenis_kelamin > 0 || $hasJawabanKuesioner)) {
        // Buat responden baru jika ada minimal data
        if (!empty($nama) || $usia > 0 || $jenis_kelamin > 0 || $hasJawabanKuesioner) {
            $nama_esc = mysqli_real_escape_string($koneksi, $nama);
            $nomor_telepon_esc = mysqli_real_escape_string($koneksi, $nomor_telepon);
            
            $insert_fields = array();
            $insert_values = array();
            
            if (!empty($nama)) {
                $insert_fields[] = "nama";
                $insert_values[] = "'{$nama_esc}'";
            }
            if ($usia > 0) {
                $insert_fields[] = "umur";
                $insert_values[] = $usia;
            }
            if ($jenis_kelamin > 0) {
                $insert_fields[] = "jenis_kelamin";
                $insert_values[] = $jenis_kelamin;
            }
            if ($pendidikan_id > 0) {
                $insert_fields[] = "pendidikan_id";
                $insert_values[] = $pendidikan_id;
            }
            if ($pekerjaan_id > 0) {
                $insert_fields[] = "pekerjaan_id";
                $insert_values[] = $pekerjaan_id;
            }
            if ($penghasilan_id > 0) {
                $insert_fields[] = "penghasilan_id";
                $insert_values[] = $penghasilan_id;
            }
            if (!empty($nomor_telepon)) {
                $insert_fields[] = "nomor_telepon";
                $insert_values[] = "'{$nomor_telepon_esc}'";
            }
            if ($provinces_id > 0) {
                $insert_fields[] = "provinces_id";
                $insert_values[] = $provinces_id;
            }
            if ($regencies_id > 0) {
                $insert_fields[] = "regencies_id";
                $insert_values[] = $regencies_id;
            }
            if ($kesediaan_menjadi_responden > 0) {
                $insert_fields[] = "kesediaan_menjadi_responden";
                $insert_values[] = $kesediaan_menjadi_responden;
            }
            
            $insert_fields[] = "status";
            $insert_values[] = "0";
            
            // Jika tidak ada field yang diisi, set minimal nama
            if (empty($insert_fields) || count($insert_fields) == 1) {
                $insert_fields = ["nama", "status"];
                $insert_values = ["'Survey Respondent'", "0"];
            }
            
            // Gunakan UUID jika ada, atau session ID
            if ($responden_id_from_uuid && $responden_id_from_uuid > 0) {
                // Jika ada UUID, gunakan responden_id dari UUID (tidak perlu insert)
                $responden_id = $responden_id_from_uuid;
            } else {
                $sessionId = session_id();
                $sessionEscaped = mysqli_real_escape_string($koneksi, $sessionId);
                $insert_fields[] = "user_input";
                $insert_values[] = "'survey_online_draft_{$sessionEscaped}'";
                
                $insert_fields[] = "tanggal_input";
                $insert_values[] = "NOW()";
                
                $insertResponden = "INSERT INTO respondens (" . implode(", ", $insert_fields) . ") VALUES (" . implode(", ", $insert_values) . ")";
                $resultInsert = mysqli_query($koneksi, $insertResponden);
                if ($resultInsert) {
                    $responden_id = mysqli_insert_id($koneksi);
                } else {
                    throw new Exception('Gagal membuat responden baru: ' . mysqli_error($koneksi));
                }
            }
        }
    }
    
    // Pastikan responden_id ter-set sebelum menyimpan jawaban
    if (!$responden_id && isset($_SESSION['responden_id']) && $_SESSION['responden_id'] > 0) {
        $responden_id = (int)$_SESSION['responden_id'];
    }
    
    // Log semua POST data untuk debugging
    error_log("POST data received: " . json_encode($_POST));
    error_log("Responden ID before processing: " . ($responden_id ? $responden_id : 'null'));
    
    // Cek apakah ada jawaban kuesioner yang perlu disimpan
    $hasJawaban = false;
    $jawabanCount = 0;
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'kuesioner_') === 0) {
            $hasJawaban = true;
            $jawabanCount++;
            error_log("Found jawaban: $key = " . (is_array($value) ? json_encode($value) : $value));
        }
    }
    
    error_log("Total jawaban found: $jawabanCount");
    
    // Jika ada jawaban kuesioner tapi belum ada responden_id, buat responden baru minimal
    if ($hasJawaban && (!$responden_id || $responden_id <= 0)) {
        error_log("Creating minimal responden for jawaban kuesioner");
        $sessionId = session_id();
        $sessionEscaped = mysqli_real_escape_string($koneksi, $sessionId);
        $insertMinimal = "INSERT INTO respondens (nama, status, user_input, tanggal_input) 
            VALUES ('Survey Respondent', 0, 'survey_online_draft_{$sessionEscaped}', NOW())";
        $resultMinimal = mysqli_query($koneksi, $insertMinimal);
        if ($resultMinimal) {
            $responden_id = mysqli_insert_id($koneksi);
            $_SESSION['responden_id'] = $responden_id;
            error_log("Created minimal responden_id=$responden_id");
        } else {
            error_log("Failed to create minimal responden: " . mysqli_error($koneksi));
        }
    }
    
    if ($responden_id && $responden_id > 0) {
        $_SESSION['responden_id'] = $responden_id;
        
        error_log("Responden ID confirmed: $responden_id");
        
        // Hapus jawaban lama untuk responden ini (untuk update) - hanya jika ada jawaban baru
        if ($hasJawaban) {
            $deleteOldAnswers = "DELETE FROM jawaban_responden WHERE responden_id = {$responden_id} AND status = 0";
            $resultDelete = mysqli_query($koneksi, $deleteOldAnswers);
            if (!$resultDelete) {
                // Log warning tapi jangan throw error karena ini hanya cleanup
                error_log('Warning: Gagal delete jawaban lama: ' . mysqli_error($koneksi));
            } else {
                $deletedCount = mysqli_affected_rows($koneksi);
                error_log("Deleted old draft answers for responden_id=$responden_id (count: $deletedCount)");
            }
        }

        // Simpan semua jawaban ke tabel jawaban_responden
        // Hanya simpan jika ada responden_id yang valid
        $savedCount = 0;
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'kuesioner_') === 0) {
                $kuesioner_id = (int)str_replace('kuesioner_', '', $key);
                
                // Skip jika kuesioner_id tidak valid
                if ($kuesioner_id <= 0) continue;
                
                // Log untuk debugging
                error_log("Processing jawaban: kuesioner_id=$kuesioner_id, responden_id=$responden_id, value=" . (is_array($value) ? json_encode($value) : $value));
                
                // Get question details
                $question = $db->getITEM("SELECT tipe_jawaban FROM kuesioner WHERE id = $kuesioner_id");
                $tipe_jawaban = $question ? $question['tipe_jawaban'] : '';
                
                $jawaban_teks = '';
                $opsi_jawaban_id_value = null;
                
                if (is_array($value)) {
                    // Handle checkbox (multiple options)
                    foreach ($value as $opt_id) {
                        $opt_id_int = (int)$opt_id;
                        $option = $db->getITEM("SELECT teks_opsi FROM opsi_jawaban WHERE id = $opt_id_int");
                        if ($option) {
                            $jawaban_teks = $option['teks_opsi'];
                            $opsi_jawaban_id_value = $opt_id_int;
                            
                            $jawaban_esc = mysqli_real_escape_string($koneksi, $jawaban_teks);
                            
                            $insertJawaban = "INSERT INTO jawaban_responden (responden_id, kuesioner_id, opsi_jawaban_id, jawaban_teks, tanggal_jawab, status, user_input, tanggal_input)
                                VALUES ({$responden_id}, {$kuesioner_id}, {$opsi_jawaban_id_value}, '{$jawaban_esc}', NOW(), 0, 'survey_online_autosave', NOW())";
                            
                            error_log("Inserting checkbox jawaban: responden_id=$responden_id, kuesioner_id=$kuesioner_id, opsi_jawaban_id=$opsi_jawaban_id_value, jawaban_teks=$jawaban_esc");
                            
                            $resultInsertJawaban = mysqli_query($koneksi, $insertJawaban);
                            if (!$resultInsertJawaban) {
                                $error_msg = mysqli_error($koneksi);
                                error_log('Error insert jawaban checkbox: ' . $error_msg . ' - Query: ' . $insertJawaban);
                                // Jangan throw error untuk checkbox karena bisa multiple insert
                            } else {
                                error_log("Successfully inserted checkbox jawaban for kuesioner_id=$kuesioner_id, responden_id=$responden_id");
                                $savedCount++;
                            }
                        }
                    }
                } else {
                    $value = trim($value);
                    
                    if (empty($value)) continue; // Skip jika kosong
                    
                    if ($tipe_jawaban === 'skala') {
                        // For scale/rating - ambil text dari opsi_jawaban
                        $value_int = (int)$value;
                        $option = $db->getITEM("SELECT teks_opsi FROM opsi_jawaban WHERE id = $value_int");
                        if ($option) {
                            $jawaban_teks = $option['teks_opsi'];
                            $opsi_jawaban_id_value = $value_int;
                        } else {
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
                    
                    $jawaban_esc = mysqli_real_escape_string($koneksi, $jawaban_teks);
                    
                    $opsi_value = ($opsi_jawaban_id_value !== null ? $opsi_jawaban_id_value : 'NULL');
                    $insertJawaban = "INSERT INTO jawaban_responden (responden_id, kuesioner_id, opsi_jawaban_id, jawaban_teks, tanggal_jawab, status, user_input, tanggal_input)
                        VALUES ({$responden_id}, {$kuesioner_id}, {$opsi_value}, '{$jawaban_esc}', NOW(), 0, 'survey_online_autosave', NOW())";
                    
                    error_log("Inserting jawaban: responden_id=$responden_id, kuesioner_id=$kuesioner_id, opsi_jawaban_id=$opsi_value, jawaban_teks=$jawaban_esc");
                    
                    $resultInsertJawaban = mysqli_query($koneksi, $insertJawaban);
                    if (!$resultInsertJawaban) {
                        $error_msg = mysqli_error($koneksi);
                        error_log('Error insert jawaban: ' . $error_msg . ' - Query: ' . $insertJawaban);
                        // Throw error hanya jika bukan duplicate key error
                        if (strpos($error_msg, 'Duplicate entry') === false) {
                            throw new Exception('Gagal menyimpan jawaban untuk kuesioner ' . $kuesioner_id . ': ' . $error_msg);
                        }
                    } else {
                        error_log("Successfully inserted jawaban for kuesioner_id=$kuesioner_id, responden_id=$responden_id");
                        $savedCount++;
                    }
                }
                
                // Handle "Lainnya" field
                $lainnya_key = $key . '_lainnya';
                if (isset($_POST[$lainnya_key]) && !empty(trim($_POST[$lainnya_key]))) {
                    $lainnya_value = trim($_POST[$lainnya_key]);
                    $lainnya_esc = mysqli_real_escape_string($koneksi, $lainnya_value);
                    
                    $insertLainnya = "INSERT INTO jawaban_responden (responden_id, kuesioner_id, jawaban_teks, tanggal_jawab, status, user_input, tanggal_input)
                        VALUES ({$responden_id}, {$kuesioner_id}, 'Lainnya: {$lainnya_esc}', NOW(), 0, 'survey_online_autosave', NOW())";
                    
                    $resultInsertLainnya = mysqli_query($koneksi, $insertLainnya);
                    if (!$resultInsertLainnya) {
                        error_log('Error insert jawaban lainnya: ' . mysqli_error($koneksi));
                    } else {
                        $savedCount++;
                    }
                }
            }
        }
        
        error_log("Total jawaban saved in this request: $savedCount");
    } else {
        error_log("WARNING: Cannot save jawaban - responden_id is null or invalid");
    }

    // Pastikan responden_id ada sebelum commit (dari update, insert, atau session)
    if (!$responden_id && isset($_SESSION['responden_id']) && $_SESSION['responden_id'] > 0) {
        $responden_id = (int)$_SESSION['responden_id'];
    }
    
    // Commit transaction
    $resultCommit = mysqli_query($koneksi, "COMMIT");
    if (!$resultCommit) {
        throw new Exception('Gagal commit transaction: ' . mysqli_error($koneksi));
    }

    // Count total jawaban yang disimpan
    $totalJawabanSaved = 0;
    if ($responden_id && $responden_id > 0) {
        $countResult = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jawaban_responden WHERE responden_id = {$responden_id} AND status = 0");
        if ($countResult) {
            $countRow = mysqli_fetch_assoc($countResult);
            $totalJawabanSaved = (int)$countRow['total'];
        }
    }
    
    error_log("Total jawaban saved: $totalJawabanSaved for responden_id=" . ($responden_id ? $responden_id : 'null'));

    echo json_encode([
        'status' => 'success',
        'message' => 'Survey data saved successfully',
        'responden_id' => $responden_id ? $responden_id : null,
        'from_uuid' => ($responden_id_from_uuid ? true : false),
        'total_jawaban_saved' => $totalJawabanSaved
    ]);

} catch (Exception $e) {
    // Rollback on error
    if (isset($koneksi)) {
        mysqli_query($koneksi, "ROLLBACK");
    }
    
    // Log error
    error_log('Error autosave database: ' . $e->getMessage());
    
    // Return error dengan detail untuk debugging
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan data: ' . $e->getMessage()
    ]);
}
?>
