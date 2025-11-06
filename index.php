<?php

session_start();

ob_start();



// Include database connection

require_once 'db.php';



 $db = new DB();



// Cek apakah ada UUID di URL

// Format: localhost/{namafolder}/uuid/{uuid} atau ?uuid=...

 $uuid = null;



// Method 1: Dari query parameter (jika .htaccess route UUID ke ?uuid=...)

if (isset($_GET['uuid']) && !empty($_GET['uuid'])) {

    $uuid = trim($_GET['uuid']);

}



// Method 2: Ambil dari URL path langsung (format: /uuid/{uuid})

if (!$uuid) {

    $request_uri = $_SERVER['REQUEST_URI'];

    // Hapus query string jika ada

    $request_uri = strtok($request_uri, '?');

    $request_uri = trim($request_uri, '/');

    $path_parts = explode('/', $request_uri);

    

    // Cek apakah path mengikuti format: .../uuid/{uuid}

    // Contoh: ['survei-nala', 'uuid', 'abc123...'] atau ['uuid', 'abc123...']

    if (count($path_parts) >= 2) {

        // Cari index "uuid" di path

        $uuid_index = -1;

        for ($i = 0; $i < count($path_parts) - 1; $i++) {

            if (strtolower($path_parts[$i]) === 'uuid') {

                $uuid_index = $i + 1;

                break;

            }

        }

        

        // Jika ditemukan "uuid" dan setelahnya ada UUID (64 karakter hex)

        if ($uuid_index > 0 && $uuid_index < count($path_parts)) {

            $uuid_part = $path_parts[$uuid_index];

            if ($uuid_part && strlen($uuid_part) == 64 && ctype_xdigit($uuid_part) && strpos($uuid_part, '.php') === false) {

                $uuid = $uuid_part;

            }

        }

    }

}



// Variable untuk menyimpan data responden (untuk pre-fill)

 $responden_data = null;

 $peserta = null;



// Jika UUID ditemukan, query survei_peserta untuk dapat survei_id

if ($uuid && strlen($uuid) == 64 && ctype_xdigit($uuid)) {

    $koneksi = $db->getConnection();

    if ($koneksi) {

        $uuid_escaped = mysqli_real_escape_string($koneksi, $uuid);

        $peserta = $db->getITEM("SELECT survei_id, responden_id, status_partisipasi FROM survei_peserta WHERE uuid = '$uuid_escaped' AND status = 1");

        

        if ($peserta) {

            // Cek apakah survei sudah selesai

            if ($peserta['status_partisipasi'] == 'selesai') {

                // Ambil data survei untuk ditampilkan di halaman terima kasih

                $survei_id_thanks = (int)$peserta['survei_id'];

                $survei_thanks = $db->getITEM("SELECT judul FROM survei WHERE id = $survei_id_thanks");

                $judul_survei = $survei_thanks ? htmlspecialchars($survei_thanks['judul']) : 'Survei';

                

                // Ambil data responden jika ada

                $responden_id_thanks = (int)$peserta['responden_id'];

                $responden_thanks = $db->getITEM("SELECT nama FROM respondens WHERE id = $responden_id_thanks");

                $nama_responden = $responden_thanks ? htmlspecialchars($responden_thanks['nama']) : '';

                

                // Buat halaman terima kasih dengan styling yang konsisten dengan aplikasi

                $thank_you_page = '

                <!DOCTYPE html>

                <html lang="id">

                <head>

                    <meta charset="UTF-8">

                    <meta name="viewport" content="width=device-width, initial-scale=1.0">

                    <title>Terima Kasih - Survei Selesai</title>

                    <style>

                        * {

                            margin: 0;

                            padding: 0;

                            box-sizing: border-box;

                        }

                        body {

                            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;

                            background: linear-gradient(to bottom, #30809C 0%, #1A4A72 100%);

                            min-height: 100vh;

                            padding: 20px;

                            display: flex;

                            justify-content: center;

                            align-items: center;

                        }

                        .container {

                            background: white;

                            border-radius: 20px;

                            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);

                            max-width: 600px;

                            width: 100%;

                            overflow: hidden;

                            animation: slideIn 0.5s ease-out;

                        }

                        @keyframes slideIn {

                            from { opacity: 0; transform: translateY(30px); }

                            to { opacity: 1; transform: translateY(0); }

                        }

                        @keyframes bounce {

                            0%, 100% { transform: translateY(0); }

                            50% { transform: translateY(-20px); }

                        }

                        .header {

                            background: linear-gradient(to right, #30809C 0%, #1A4A72 100%);

                            color: white;

                            padding: 30px;

                            text-align: center;

                        }

                        .header h1 {

                            font-size: 24px;

                            font-weight: 600;

                            margin: 0;

                        }

                        .content {

                            text-align: center;

                            padding: 60px 40px;

                        }

                        .icon {

                            font-size: 80px;

                            margin-bottom: 30px;

                            animation: bounce 1s ease-in-out infinite;

                        }

                        .content h1 {

                            color: #1A4A72;

                            font-size: 32px;

                            margin-bottom: 15px;

                            font-weight: 600;

                        }

                        .content .nama-responden {

                            color: #667eea;

                            font-size: 18px;

                            margin-bottom: 20px;

                            font-weight: 500;

                        }

                        .info-box {

                            background: #f0f7ff;

                            border-left: 4px solid #667eea;

                            padding: 20px;

                            border-radius: 8px;

                            margin: 30px 0;

                            text-align: left;

                        }

                        .info-box p {

                            color: #333;

                            font-size: 16px;

                            line-height: 1.8;

                            margin: 0;

                        }

                        .content > p {

                            color: #666;

                            font-size: 15px;

                            line-height: 1.8;

                            margin-top: 30px;

                        }

                        .status-box {

                            margin-top: 40px;

                            padding-top: 30px;

                            border-top: 2px solid #e0e0e0;

                        }

                        .status-box p {

                            color: #999;

                            font-size: 14px;

                        }

                    </style>

                </head>

                <body>

                    <div class="container">

                        <div class="header">

                            <h1>Survei Selesai</h1>

                        </div>

                        <div class="content">

                            <div class="icon">🎉</div>

                            <h1>Terima Kasih!</h1>

                            ' . (!empty($nama_responden) ? '<p class="nama-responden">' . $nama_responden . '</p>' : '') . '

                            <div class="info-box">

                                <p>

                                    Survei <strong>"' . $judul_survei . '"</strong> yang Anda isi telah berhasil dikirim dan diterima dengan baik.

                                </p>

                            </div>

                            <p>

                                Partisipasi Anda sangat berarti bagi kami. Data yang Anda berikan akan digunakan untuk penelitian dan pengembangan yang lebih baik.

                            </p>

                            <div class="status-box">

                                <p>

                                    <strong>Status:</strong> Survei telah selesai dikerjakan<br>

                                    Anda tidak dapat mengisi survei ini lagi menggunakan link ini.

                                </p>

                            </div>

                        </div>

                    </div>

                </body>

                </html>';

                

                die($thank_you_page);

            }

            

            $survei_id_asli = (int)$peserta['survei_id'];

            // Simpan UUID dan responden_id ke session untuk tracking

            $_SESSION['survey_uuid'] = $uuid;

            $_SESSION['responden_id_from_uuid'] = $peserta['responden_id'];

            $_SESSION['survei_id_asli'] = $survei_id_asli; // Simpan survei_id asli untuk tracking

            

            // Ambil data responden untuk pre-fill form

            $responden_id = (int)$peserta['responden_id'];

            $responden_data = $db->getITEM("SELECT * FROM respondens WHERE id = $responden_id");

        }

    }

}



// Untuk pertanyaan survei, selalu gunakan survei_id = 4

 $survei_id_untuk_kuesioner = 4;



// Jika tidak ada UUID, gunakan survei_id dari GET parameter (default to 4)

if (!isset($survei_id_asli)) {

    $survei_id_asli = isset($_GET['survei_id']) ? (int)$_GET['survei_id'] : 4;

    // Tapi tetap gunakan survei_id 4 untuk kuesioner

    $survei_id_untuk_kuesioner = 4;

}



// Fetch survey details untuk judul dan logo (gunakan survei_id asli jika ada, atau default 4)

 $survei_id_untuk_logo = isset($survei_id_asli) ? $survei_id_asli : $survei_id_untuk_kuesioner;

 $survei = $db->getITEM("SELECT * FROM survei WHERE id = $survei_id_untuk_logo");



if (!$survei) {

    die("Survey tidak ditemukan. Pastikan ID survei valid atau database sudah terhubung dengan benar.");

}



// Cek status survei dan tanggal

$status_publikasi = isset($survei['status_publikasi']) ? $survei['status_publikasi'] : 'draft';

$tanggal_mulai = isset($survei['tanggal_mulai']) ? $survei['tanggal_mulai'] : null;

$tanggal_selesai = isset($survei['tanggal_selesai']) ? $survei['tanggal_selesai'] : null;

$tanggal_sekarang = date('Y-m-d');



// Cek apakah survei bisa diisi

$bisa_diisi = false;

$pesan_error = '';



// Fungsi untuk format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    if (!$tanggal) return 'tidak diketahui';
    
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    $tanggal_format = date('d', $timestamp);
    $bulan_format = $bulan[(int)date('m', $timestamp)];
    $tahun_format = date('Y', $timestamp);
    
    return $tanggal_format . ' ' . $bulan_format . ' ' . $tahun_format;
}

if ($status_publikasi === 'draft') {
    $pesan_error = 'Survei ini belum diterbitkan.';
} elseif ($status_publikasi === 'selesai') {
    $tanggal_selesai_formatted = formatTanggalIndonesia($tanggal_selesai);
    $pesan_error = 'Periode survei telah berakhir pada ' . $tanggal_selesai_formatted . '. Data tidak dapat diisi kembali.';
} elseif ($status_publikasi === 'terbit') {
    // Cek apakah tanggal masih dalam periode
    if ($tanggal_selesai && $tanggal_sekarang > $tanggal_selesai) {
        $tanggal_selesai_formatted = formatTanggalIndonesia($tanggal_selesai);
        $pesan_error = 'Periode survei telah berakhir pada ' . $tanggal_selesai_formatted . '. Data tidak dapat diisi kembali.';
    } elseif ($tanggal_mulai && $tanggal_sekarang < $tanggal_mulai) {
        $tanggal_mulai_formatted = formatTanggalIndonesia($tanggal_mulai);
        $pesan_error = 'Survei ini akan dimulai pada ' . $tanggal_mulai_formatted . '.';
    } else {
        $bisa_diisi = true;
    }
} else {
    $pesan_error = 'Status survei tidak valid.';
}



// Jika tidak bisa diisi, tampilkan pesan error dan stop

if (!$bisa_diisi) {

    $error_page = '

    <!DOCTYPE html>

    <html lang="id">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Survei Tidak Tersedia</title>

        <style>

            * {

                margin: 0;

                padding: 0;

                box-sizing: border-box;

            }

            body {

                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;

                background: linear-gradient(to bottom, #30809C 0%, #1A4A72 100%);

                min-height: 100vh;

                padding: 20px;

                display: flex;

                justify-content: center;

                align-items: center;

            }

            .container {

                background: white;

                border-radius: 20px;

                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);

                max-width: 600px;

                width: 100%;

                overflow: hidden;

                animation: slideIn 0.5s ease-out;

            }

            @keyframes slideIn {

                from { opacity: 0; transform: translateY(30px); }

                to { opacity: 1; transform: translateY(0); }

            }

            .header {

                background: linear-gradient(to right, #30809C 0%, #1A4A72 100%);

                color: white;

                padding: 30px;

                text-align: center;

            }

            .header h1 {

                font-size: 24px;

                font-weight: 600;

                margin: 0;

            }

            .content {

                text-align: center;

                padding: 60px 40px;

            }

            .icon {

                font-size: 80px;

                margin-bottom: 30px;

            }

            .content h2 {

                color: #1A4A72;

                font-size: 28px;

                margin-bottom: 20px;

                font-weight: 600;

            }

            .content p {

                color: #666;

                font-size: 16px;

                line-height: 1.8;

                margin-bottom: 30px;

            }

            .info-box {

                background: #fff3cd;

                border-left: 4px solid #ffc107;

                padding: 20px;

                border-radius: 8px;

                margin: 30px 0;

                text-align: left;

            }

            .info-box p {

                color: #856404;

                font-size: 16px;

                line-height: 1.8;

                margin: 0;

            }

        </style>

    </head>

    <body>

        <div class="container">

            <div class="header">

                <h1>Survei Tidak Tersedia</h1>

            </div>

            <div class="content">

                <div class="icon">⚠️</div>

                <h2>' . htmlspecialchars($survei['judul']) . '</h2>

                <div class="info-box">

                    <p>' . htmlspecialchars($pesan_error) . '</p>

                </div>

                <p>Jika Anda memiliki pertanyaan, silakan hubungi administrator survei.</p>

            </div>

        </div>

    </body>

    </html>';

    

    die($error_page);

}



// Fetch all questions for this survey (selalu dari survei_id 4)

 $kuesioner = $db->getALL("SELECT * FROM kuesioner WHERE survei_id = $survei_id_untuk_kuesioner AND status = 1 ORDER BY id ASC");

if (!is_array($kuesioner)) {

    $kuesioner = array();

}



// Fetch educational options

 $pendidikan = $db->getALL("SELECT * FROM pendidikan WHERE status = 1 ORDER BY id ASC");

if (!is_array($pendidikan)) {

    $pendidikan = array();

}



// Fetch work options

 $pekerjaan = $db->getALL("SELECT * FROM pekerjaan WHERE status = 1 ORDER BY id ASC");

if (!is_array($pekerjaan)) {

    $pekerjaan = array();

}



// Fetch income options

 $penghasilan = $db->getALL("SELECT * FROM penghasilan WHERE status = 1 ORDER BY id ASC");

if (!is_array($penghasilan)) {

    $penghasilan = array();

}



// Fetch provinces

 $provinces = $db->getALL("SELECT * FROM provinces WHERE status = 1 ORDER BY name ASC");

if (!is_array($provinces)) {

    $provinces = array();

}



// Fetch regencies

 $regencies = $db->getALL("SELECT * FROM regencies WHERE status = 1 ORDER BY name ASC");

if (!is_array($regencies)) {

    $regencies = array();

}



// Kita kembalikan action ke process_survey.php, JavaScript yang akan mengatur alurnya

 $content = '<form id="surveyForm" method="POST" action="process_survey.php">';

// Simpan survei_id asli (dari UUID) untuk tracking, tapi pertanyaan tetap dari survei_id 4

 $survei_id_untuk_submit = isset($survei_id_asli) ? $survei_id_asli : $survei_id_untuk_kuesioner;

 $content .= '<input type="hidden" name="survei_id" value="' . $survei_id_untuk_submit . '">';
 
 // Tambahkan hidden input untuk responden_id jika ada (untuk validasi nomor telepon)
 if (isset($responden_data) && isset($responden_data['id'])) {
     $content .= '<input type="hidden" name="responden_id" id="responden_id" value="' . (int)$responden_data['id'] . '">';
 }



// ===== SECTION 1: INFORMASI PRIBADI =====

 $content .= '

<div id="part1">

    <div class="section-title" style="border-bottom: none; padding-bottom: 0;">👤 INFORMASI PRIBADI</div>

    <div style="color: #666; font-size: 14px; margin-bottom: 10px; padding: 0 20px;">

        Data yang Anda berikan sangat berharga untuk membandingkan efisiensi Pembayaran Digital vs Tunai. Kami menjamin kerahasiaan penuh atas semua informasi Anda.

    </div>

    <div style="color: #666; font-size: 14px; margin-bottom: 25px; padding: 0 20px; border-bottom: 2px solid #1A4A72; padding-bottom: 10px;">

        Mohon lengkapi data diri di bawah ini dengan jujur.

    </div>';



// Nama

 $nama_value = $responden_data ? htmlspecialchars($responden_data['nama']) : '';

 $content .= '

        <div class="question-card">

            <div class="question-title">Nama Lengkap</div>

            <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" value="' . $nama_value . '" maxlength="100" pattern="[A-Za-z\s]+">

        </div>';



// Usia

 $umur_value = $responden_data ? (int)$responden_data['umur'] : '';

 $content .= '

        <div class="question-card">

            <div class="question-title">Usia <span class="required">*</span></div>

            <input type="number" id="usia" name="usia" placeholder="Masukkan usia Anda" min="1" max="100" value="' . $umur_value . '" required>

        </div>';



// Jenis Kelamin - Konversi integer dari database ke teks untuk form

 $jk_value = '';

if ($responden_data && isset($responden_data['jenis_kelamin'])) {

    // Konversi integer ke teks: 1 = Laki-laki, 2 = Perempuan

    if ($responden_data['jenis_kelamin'] == 1) {

        $jk_value = 'Laki-laki';

    } elseif ($responden_data['jenis_kelamin'] == 2) {

        $jk_value = 'Perempuan';

    }

}

 $content .= '

        <div class="question-card">

            <div class="question-title">Jenis Kelamin <span class="required">*</span></div>

            <div class="radio-group">

                <div class="radio-option">

                    <input type="radio" id="pria" name="jenis_kelamin" value="Laki-laki" ' . ($jk_value == 'Laki-laki' ? 'checked' : '') . ' required>

                    <label for="pria">Laki-laki</label>

                </div>

                <div class="radio-option">

                    <input type="radio" id="wanita" name="jenis_kelamin" value="Perempuan" ' . ($jk_value == 'Perempuan' ? 'checked' : '') . ' required>

                    <label for="wanita">Perempuan</label>

                </div>

            </div>

        </div>';



// Pendidikan

 $pendidikan_id_value = $responden_data ? (int)$responden_data['pendidikan_id'] : 0;

 $pendidikan_text_value = '';

if ($responden_data && $pendidikan_id_value > 0) {

    foreach ($pendidikan as $p) {

        if ($p['id'] == $pendidikan_id_value) {

            $pendidikan_text_value = htmlspecialchars($p['nama']);

            break;

        }

    }

}

 $content .= '

        <div class="question-card">

            <div class="question-title">Pendidikan Terakhir <span class="required">*</span></div>

            <div class="autocomplete-container">

                <input type="text" id="pendidikan_text" name="pendidikan_text" placeholder="Ketik atau pilih pendidikan terakhir Anda" value="' . $pendidikan_text_value . '" required>

                <input type="hidden" id="pendidikan_id" name="pendidikan_id" value="' . $pendidikan_id_value . '" required>

                <div id="pendidikan_dropdown" class="autocomplete-dropdown">';

                if ($pendidikan) {

                    foreach ($pendidikan as $p) {

                        $content .= '<div class="dropdown-item" data-value="' . $p['id'] . '" data-text="' . htmlspecialchars($p['nama']) . '">' . htmlspecialchars($p['nama']) . '</div>';

                    }

                }

 $content .= '

                </div>

            </div>

        </div>';



// Pekerjaan

 $pekerjaan_id_value = $responden_data ? (int)$responden_data['pekerjaan_id'] : 0;

 $content .= '

        <div class="question-card">

            <div class="question-title">Pekerjaan Saat Ini <span class="required">*</span></div>

            <div class="radio-group">';

            if ($pekerjaan) {

                $index = 0;

                foreach ($pekerjaan as $p) {

                    $index++;

                    $radioId = 'pekerjaan' . $index;

                    $checked = ($pekerjaan_id_value == $p['id']) ? 'checked' : '';

                    $content .= '

                <div class="radio-option">

                    <input type="radio" id="' . $radioId . '" name="pekerjaan_id" value="' . $p['id'] . '" data-nama="' . htmlspecialchars($p['nama']) . '" ' . $checked . ' required>

                    <label for="' . $radioId . '">' . htmlspecialchars($p['nama']) . '</label>

                </div>';

                }

            }

 $content .= '

            </div>

        </div>';



// Penghasilan

 $penghasilan_id_value = $responden_data ? (int)$responden_data['penghasilan_id'] : 0;

 $content .= '

        <div class="question-card">

            <div class="question-title">Penghasilan Rata-rata per Bulan <span class="required">*</span></div>

            <div class="radio-group">';

            if ($penghasilan) {

                $index = 0;

                foreach ($penghasilan as $pg) {

                    $index++;

                    $radioId = 'penghasilan' . $index;

                    $checked = ($penghasilan_id_value == $pg['id']) ? 'checked' : '';

                    $content .= '

                <div class="radio-option">

                    <input type="radio" id="' . $radioId . '" name="penghasilan_id" value="' . $pg['id'] . '" ' . $checked . ' required>

                    <label for="' . $radioId . '">' . htmlspecialchars($pg['kisaran']) . '</label>

                </div>';

                }

            }

 $content .= '

            </div>

        </div>';



// Nomor Telepon

 $nomor_telepon_value = $responden_data ? htmlspecialchars($responden_data['nomor_telepon']) : '';

 $content .= '

        <div class="question-card">

            <div class="question-title">Nomor Telepon <span class="required">*</span></div>

            <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon Anda" value="' . $nomor_telepon_value . '" minlength="10" maxlength="13" pattern="[0-9]+" required>
            <div id="nomor_telepon_error" style="color: #d32f2f; font-size: 12px; margin-top: 5px; display: none;"></div>

</div>';



// Provinsi

 $provinces_id_value = $responden_data ? (int)$responden_data['provinces_id'] : 0;

 $provinces_text_value = '';

 $selected_province_code = '';

if ($responden_data && $provinces_id_value > 0) {

    foreach ($provinces as $p) {

        if ($p['id'] == $provinces_id_value) {

            $provinces_text_value = htmlspecialchars($p['name']);

            $selected_province_code = htmlspecialchars($p['code']);

            break;

        }

    }

}

$content .= '

        <div class="question-card" id="provinces_card">

            <div class="question-title">Provinsi <span class="required">*</span></div>

            <div class="autocomplete-container">

                <input type="text" id="provinces_text" name="provinces_text" placeholder="Ketik atau pilih provinsi Anda" value="' . $provinces_text_value . '" required>

                <input type="hidden" id="provinces_id" name="provinces_id" value="' . $provinces_id_value . '" required>

                <div id="provinces_dropdown" class="autocomplete-dropdown">';

                if ($provinces) {

                    foreach ($provinces as $p) {

                        $content .= '<div class="dropdown-item" data-value="' . $p['id'] . '" data-text="' . htmlspecialchars($p['name']) . '" data-province-code="' . htmlspecialchars($p['code']) . '">' . htmlspecialchars($p['name']) . '</div>';

                    }

                }

 $content .= '

                </div>

            </div>

        </div>';



// Kabupaten/Kota - dengan ID khusus untuk bisa di-hide

 $regencies_id_value = $responden_data ? (int)$responden_data['regencies_id'] : 0;

 $regencies_text_value = '';

if ($responden_data && $regencies_id_value > 0) {

    foreach ($regencies as $r) {

        if ($r['id'] == $regencies_id_value) {

            $regencies_text_value = htmlspecialchars($r['name']);

            break;

        }

    }

}

 $content .= '

        <div class="question-card" id="regencies_card">

            <div class="question-title">Kabupaten/Kota <span class="required">*</span></div>

            <div class="autocomplete-container">

                <input type="text" id="regencies_text" name="regencies_text" placeholder="Ketik atau pilih kabupaten/kota Anda" value="' . $regencies_text_value . '" required>

                <input type="hidden" id="regencies_id" name="regencies_id" value="' . $regencies_id_value . '" required>

                <div id="regencies_dropdown" class="autocomplete-dropdown">';

                if ($regencies) {

                    foreach ($regencies as $r) {

                        $content .= '<div class="dropdown-item" data-value="' . $r['id'] . '" data-text="' . htmlspecialchars($r['name']) . '" data-province-code="' . htmlspecialchars($r['province_code']) . '">' . htmlspecialchars($r['name']) . '</div>';

                    }

                }

 $content .= '

                </div>

            </div>

        </div>';



// Ketersediaan Menjadi Responden

 $kesediaan_value = $responden_data ? (int)$responden_data['kesediaan_menjadi_responden'] : 0;

 $content .= '

        <div class="question-card">

            <div class="question-title">Ketersediaan Menjadi Responden Survei Selanjutnya <span class="required">*</span></div>

            <div class="radio-group">

                <div class="radio-option">

                    <input type="radio" id="bersedia" name="kesediaan_menjadi_responden" value="1" ' . ($kesediaan_value == 1 ? 'checked' : '') . ' required>

                    <label for="bersedia">Bersedia</label>

                </div>

                <div class="radio-option">

                    <input type="radio" id="tidak_bersedia" name="kesediaan_menjadi_responden" value="2" ' . ($kesediaan_value == 2 ? 'checked' : '') . ' required>

                    <label for="tidak_bersedia">Tidak Bersedia</label>

                </div>

            </div>

        </div>';



 $content .= '

        <div style="text-align: center; margin-top: 20px;">

            <button type="button" class="submit-btn" id="nextBtn">Lanjutkan</button>

        </div>

</div>';





// JavaScript untuk auto-save dan restoration data

$content .= '

<script>

    // Fungsi untuk save form data ke sessionStorage

    function saveFormDataToSession() {

        
        
        const negativeKeywords = ["tidak", "buruk", "jelek", "negatif", "kurang", "rendah", "lemah"];

        const positiveKeywords = ["setuju", "puas", "baik", "bagus", "positif", "tinggi", "kuat", "suka"];

        
        
        const firstText = options[0].teks_opsi.toLowerCase();

        const lastText = options[options.length - 1].teks_opsi.toLowerCase();

        
        
        // Jika first adalah positive dan last adalah negative, maka harus dibalik

        const firstIsPositive = positiveKeywords.some(keyword => firstText.includes(keyword));

        const lastIsNegative = negativeKeywords.some(keyword => lastText.includes(keyword));

        
        
        return firstIsPositive && lastIsNegative;

    }

    
    
    // Fungsi untuk save form data ke sessionStorage

    function saveFormDataToSession() {

        const form = document.getElementById("surveyForm");

        if (!form) return;

        
        
        const formData = new FormData(form);

        const data = {};

        
        
        for (let [key, value] of formData.entries()) {

            if (key.endsWith("[]")) {

                const keyName = key.slice(0, -2);

                if (!data[keyName]) {

                    data[keyName] = [];

                }

                data[keyName].push(value);

            } else {

                data[key] = value;

            }

        }

        

        // Simpan juga nilai text dari autocomplete fields

        const provincesTextInput = document.getElementById("provinces_text");

        const provincesHiddenInput = document.getElementById("provinces_id");

        if (provincesTextInput && provincesHiddenInput) {

            data.provinces_text = provincesTextInput.value;

            data.provinces_id = provincesHiddenInput.value;

        }

        

        const regenciesTextInput = document.getElementById("regencies_text");

        const regenciesHiddenInput = document.getElementById("regencies_id");

        if (regenciesTextInput && regenciesHiddenInput) {

            data.regencies_text = regenciesTextInput.value;

            data.regencies_id = regenciesHiddenInput.value;

        }

        
        
        sessionStorage.setItem("surveyFormData", JSON.stringify(data));
        
        // Trigger auto-save ke database juga
        if (typeof triggerAutoSave === "function") {
            triggerAutoSave();
    }

    }

    
    
    // Fungsi untuk restore form data dari sessionStorage

    function restoreFormDataFromSession() {

        const savedData = sessionStorage.getItem("surveyFormData");

        if (!savedData) return;

        
        
        try {

            const data = JSON.parse(savedData);

            const form = document.getElementById("surveyForm");

            if (!form) return;

            
            
            for (let key in data) {

                const value = data[key];

                
                
                if (Array.isArray(value)) {

                    value.forEach(function(val) {

                        const checkbox = form.querySelector("input[name=\'" + key + "[]\'][value=\'" + val + "\']");

                        if (checkbox) {

                            checkbox.checked = true;

                        }

                    });

                } else {

                    const input = form.elements[key];

                    if (input) {

                        if (input.type === "radio") {

                            const radios = form.querySelectorAll("input[name=\'" + key + "\']");

                            radios.forEach(function(radio) {

                                radio.checked = (radio.value === value);

                            });

                        } else if (input.type === "checkbox") {

                            input.checked = (value === "on" || value === "true");

                        } else {

                            input.value = value;

                        }

                    }

                }

            }

            

            // Restore autocomplete fields khusus (provinsi dan regencies)

            if (data.provinces_text && data.provinces_id) {

                const provincesTextInput = document.getElementById("provinces_text");

                const provincesHiddenInput = document.getElementById("provinces_id");

                if (provincesTextInput && provincesHiddenInput) {

                    provincesTextInput.value = data.provinces_text;

                    provincesHiddenInput.value = data.provinces_id;

                    

                    // Set province code untuk filter regencies

                    const provinceItem = document.querySelector("#provinces_dropdown .dropdown-item[data-value=\'" + data.provinces_id + "\']");

                    if (provinceItem) {

                        const provinceCode = provinceItem.getAttribute("data-province-code");

                        document.getElementById("provinces_text").setAttribute("data-province-code", provinceCode);

                    }

                }

            }

            

            if (data.regencies_text && data.regencies_id) {

                const regenciesTextInput = document.getElementById("regencies_text");

                const regenciesHiddenInput = document.getElementById("regencies_id");

                if (regenciesTextInput && regenciesHiddenInput) {

                    regenciesTextInput.value = data.regencies_text;

                    regenciesHiddenInput.value = data.regencies_id;

                }

            }

            
            
            // Trigger update untuk rating sliders

            const ratingSlidersToUpdate = document.querySelectorAll("[id^=\'ratingPoints_\']");

            ratingSlidersToUpdate.forEach(function(slider) {

                const qId = slider.id.replace("ratingPoints_", "");

                const radioInputs = slider.querySelectorAll("input[type=\'radio\']");

                for (let i = 0; i < radioInputs.length; i++) {

                    if (radioInputs[i].checked) {

                        const value = radioInputs[i].value;

                        const selectedOption = radioInputs[i].getAttribute("data-text");

                        const point = slider.querySelector("[data-value=\'" + value + "\']");

                        const emoji = point ? point.getAttribute("data-emoji") : "😐";

                        updateRatingDisplay(qId, value, selectedOption, emoji);

                        break;

                    }

                }

            });

        } catch (e) {

            console.error("Error restoring form data:", e);

        }

    }

    
    
    document.addEventListener("DOMContentLoaded", function() {
        // Cek apakah ada UUID di URL (untuk skip restore dari sessionStorage)
        const currentPath = window.location.pathname;
        const urlParams = new URLSearchParams(window.location.search);
        let uuidFromUrl = urlParams.get("uuid");
        
        if (!uuidFromUrl && currentPath.includes("/uuid/")) {
            const parts = currentPath.split("/uuid/");
            if (parts.length > 1) {
                uuidFromUrl = parts[1].split("/")[0];
            }
        }
        
        // Cek apakah UUID valid (64 karakter hex)
        const uuidRegex = /^[a-f0-9]+$/i;
        const hasValidUUID = uuidFromUrl && uuidFromUrl.length === 64 && uuidRegex.test(uuidFromUrl);
        
        // Cek UUID yang tersimpan di sessionStorage
        const savedUUID = sessionStorage.getItem("survey_uuid");
        
        // Jika ada UUID di URL dan berbeda dengan yang tersimpan, clear sessionStorage
        if (hasValidUUID) {
            if (savedUUID && savedUUID !== uuidFromUrl) {
                // UUID berbeda, clear sessionStorage untuk menghindari data dari link sebelumnya
                sessionStorage.removeItem("surveyFormData");
                sessionStorage.setItem("survey_uuid", uuidFromUrl);
            } else if (!savedUUID) {
                // Pertama kali dengan UUID ini, simpan UUID
                sessionStorage.setItem("survey_uuid", uuidFromUrl);
            }
            // Skip restore karena data sudah di-pre-fill dari database (PHP)
            // Jangan restore dari sessionStorage karena akan overwrite data dari UUID
        } else {
            // Tidak ada UUID, restore dari sessionStorage seperti biasa
        restoreFormDataFromSession();
        }

        // Validasi nama: hanya huruf dan spasi
        const namaInput = document.getElementById("nama");
        if (namaInput) {
            namaInput.addEventListener("input", function() {
                // Hapus karakter selain huruf dan spasi
                this.value = this.value.replace(/[^A-Za-z\s]/g, "");
                // Batasi maksimal 100 karakter
                if (this.value.length > 100) {
                    this.value = this.value.substring(0, 100);
                }
            });
        }

        // Validasi usia: maksimal 100
        const usiaInput = document.getElementById("usia");
        if (usiaInput) {
            usiaInput.addEventListener("input", function() {
                // Hapus karakter selain angka
                this.value = this.value.replace(/[^0-9]/g, "");
                // Batasi maksimal 100
                const usiaValue = parseInt(this.value) || 0;
                if (usiaValue > 100) {
                    this.value = 100;
                }
            });
        }

        // Validasi nomor telepon: cek duplikat
        const nomorTeleponInput = document.getElementById("nomor_telepon");
        const nomorTeleponError = document.getElementById("nomor_telepon_error");
        let phoneCheckTimeout = null;
        let isPhoneValid = false;

        if (nomorTeleponInput && nomorTeleponError) {
            // Validasi: hanya angka, minimal 10, maksimal 13
            nomorTeleponInput.addEventListener("input", function() {
                // Hapus karakter selain angka
                this.value = this.value.replace(/[^0-9]/g, "");
                
                // Batasi maksimal 13 karakter
                if (this.value.length > 13) {
                    this.value = this.value.substring(0, 13);
                }
            });
            
            // Cek nomor telepon saat user selesai mengetik (debounce)
            nomorTeleponInput.addEventListener("input", function() {
                clearTimeout(phoneCheckTimeout);
                const phoneValue = this.value.trim();
                
                // Reset error message
                nomorTeleponError.style.display = "none";
                nomorTeleponError.textContent = "";
                nomorTeleponInput.style.borderColor = "";
                isPhoneValid = false;

                // Validasi panjang minimal 10
                if (phoneValue.length > 0 && phoneValue.length < 10) {
                    nomorTeleponError.textContent = "Nomor telepon minimal 10 angka";
                    nomorTeleponError.style.display = "block";
                    nomorTeleponInput.style.borderColor = "#d32f2f";
                    nomorTeleponInput.setCustomValidity("Nomor telepon minimal 10 angka");
                    isPhoneValid = false;
                    return;
                }

                // Jika kosong atau belum mencapai minimal, tidak perlu cek duplikat
                if (phoneValue.length === 0 || phoneValue.length < 10) {
                    return;
                }

                // Debounce: tunggu 500ms setelah user berhenti mengetik
                phoneCheckTimeout = setTimeout(function() {
                    checkPhoneNumber(phoneValue);
                }, 500);
            });

            // Cek nomor telepon saat blur (saat user keluar dari field)
            nomorTeleponInput.addEventListener("blur", function() {
                const phoneValue = this.value.trim();
                if (phoneValue.length > 0) {
                    checkPhoneNumber(phoneValue);
                }
            });
        }

        // Fungsi untuk cek nomor telepon
        function checkPhoneNumber(phoneNumber) {
            // Cek apakah ada responden_id dari UUID di session PHP
            // Kita akan kirim via hidden input atau ambil dari URL
            const formData = new FormData();
            formData.append("nomor_telepon", phoneNumber);
            
            // Coba ambil responden_id dari hidden input atau session
            const respondenIdInput = document.querySelector("input[name=\"responden_id\"]");
            if (respondenIdInput) {
                formData.append("responden_id", respondenIdInput.value);
            }

            // Gunakan path absolut untuk menghindari masalah dengan URL UUID
            const currentPath = window.location.pathname;
            let checkPhoneUrl = "check_phone.php";
            if (currentPath.includes("/uuid/")) {
                const basePath = currentPath.split("/uuid/")[0];
                checkPhoneUrl = basePath + "/check_phone.php";
            }

            fetch(checkPhoneUrl, {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "duplicate") {
                    // Nomor telepon sudah ada
                    nomorTeleponError.textContent = data.message;
                    nomorTeleponError.style.display = "block";
                    nomorTeleponInput.style.borderColor = "#d32f2f";
                    nomorTeleponInput.setCustomValidity(data.message);
                    isPhoneValid = false;
                } else if (data.status === "available") {
                    // Nomor telepon tersedia
                    nomorTeleponError.style.display = "none";
                    nomorTeleponInput.style.borderColor = "";
                    nomorTeleponInput.setCustomValidity("");
                    isPhoneValid = true;
                }
            })
            .catch(error => {
                console.error("Error checking phone number:", error);
            });
        }

        // Tambahan agar dropdown lain tertutup saat salah satu dibuka

        function closeAllDropdowns(exceptId = null) {

            const dropdowns = document.querySelectorAll(".autocomplete-dropdown");

            dropdowns.forEach(function(d) {

                if (d.id !== exceptId) {

                    d.style.display = "none";

                }

            });

        }

        
        
        // Autocomplete untuk pendidikan

        const input = document.getElementById("pendidikan_text");

        const dropdown = document.getElementById("pendidikan_dropdown");

        const hiddenInput = document.getElementById("pendidikan_id");

        
        
        if (input && dropdown && hiddenInput) {

            input.addEventListener("focus", function() {

                closeAllDropdowns("pendidikan_dropdown");

                // Tampilkan semua item dulu sebelum filter

                const items = dropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                dropdown.style.display = "block";

                filterOptions();

            });

            

            input.addEventListener("click", function(e) {

                e.stopPropagation();

                closeAllDropdowns("pendidikan_dropdown");

                // Tampilkan semua item dulu sebelum filter

                const items = dropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                dropdown.style.display = "block";

                filterOptions();

            });

            
            
            input.addEventListener("input", function() {

                closeAllDropdowns("pendidikan_dropdown");

                // Tampilkan semua item dulu sebelum filter

                const items = dropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                dropdown.style.display = "block";

                filterOptions();

            });

            

            // Tutup dropdown saat click di luar (dengan delay kecil agar tidak konflik dengan click input)
            
            document.addEventListener("click", function(e) {

                const target = e.target;

                if (input && dropdown) {

                    if (!input.contains(target) && !dropdown.contains(target)) {

                    dropdown.style.display = "none";

                }

                }

            });

            
            
            dropdown.addEventListener("click", function(e) {

                if (e.target.classList.contains("dropdown-item")) {

                    input.value = e.target.getAttribute("data-text");

                    hiddenInput.value = e.target.getAttribute("data-value");

                    dropdown.style.display = "none";

                    saveFormDataToSession();

                }

            });

        }

        
        
        function filterOptions() {

            const input = document.getElementById("pendidikan_text");

            const dropdown = document.getElementById("pendidikan_dropdown");

            

            if (!input || !dropdown) return;

            

            const filterValue = input.value.toLowerCase();

            const items = dropdown.getElementsByClassName("dropdown-item");

            

            // Jika tidak ada filter, tampilkan semua item

            if (!filterValue || filterValue.trim() === "") {
            
            for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                return;

            }

            

            // Filter berdasarkan text

            for (let i = 0; i < items.length; i++) {

                const text = items[i].getAttribute("data-text").toLowerCase();

                if (text.includes(filterValue)) {

                    items[i].style.display = "block";

                } else {

                    items[i].style.display = "none";

                }

            }

        }

        

        // Autocomplete untuk provinsi - dengan kontrol dropdown kabupaten

        const provincesInput = document.getElementById("provinces_text");

        const provincesDropdown = document.getElementById("provinces_dropdown");

        const provincesHiddenInput = document.getElementById("provinces_id");

        

        if (provincesInput && provincesDropdown && provincesHiddenInput) {

            provincesInput.addEventListener("focus", function(e) {

                e.stopPropagation();

                closeAllDropdowns("provinces_dropdown");

                // Tampilkan semua item provinsi saat focus pertama kali

                const items = provincesDropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                provincesDropdown.style.display = "block";

                filterProvincesOptions();

            });

            

            provincesInput.addEventListener("click", function(e) {

                e.stopPropagation();

                closeAllDropdowns("provinces_dropdown");

                // Tampilkan semua item provinsi saat click

                const items = provincesDropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                provincesDropdown.style.display = "block";

                filterProvincesOptions();

            });

            

            provincesInput.addEventListener("input", function() {

                if (provincesInput.value.trim() === "") {

                    provincesHiddenInput.value = "";

                    provincesInput.removeAttribute("data-province-code");

                    // Clear kabupaten/kota jika provinsi dihapus

                    const regenciesInput = document.getElementById("regencies_text");

                    const regenciesHiddenInput = document.getElementById("regencies_id");

                    if (regenciesInput && regenciesHiddenInput) {

                        regenciesInput.value = "";

                        regenciesHiddenInput.value = "";

                    }

                }

                closeAllDropdowns("provinces_dropdown");

                // Tampilkan semua item dulu

                const items = provincesDropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                provincesDropdown.style.display = "block";

                filterProvincesOptions();

            });

            

            // Tutup dropdown saat click di luar (dengan delay untuk memastikan click input tidak tertutup)

            let clickOutsideHandler = function(e) {

                const target = e.target;

                if (provincesInput && provincesDropdown) {

                    // Jangan tutup jika click di input atau dropdown

                    if (!provincesInput.contains(target) && !provincesDropdown.contains(target)) {

                        // Delay kecil untuk memastikan event click input sudah selesai

                        setTimeout(function() {

                            if (provincesDropdown && provincesDropdown.style.display === "block") {

                                provincesDropdown.style.display = "none";

                            }

                        }, 50);

                    }

                }

            };

            document.addEventListener("click", clickOutsideHandler, true);

            

            provincesDropdown.addEventListener("click", function(e) {

                e.stopPropagation();

                if (e.target.classList.contains("dropdown-item")) {

                    const oldProvinceCode = provincesInput.getAttribute("data-province-code");

                    const newProvinceCode = e.target.getAttribute("data-province-code");

                    

                    provincesInput.value = e.target.getAttribute("data-text");

                    provincesHiddenInput.value = e.target.getAttribute("data-value");

                    provincesInput.setAttribute("data-province-code", newProvinceCode);

                    provincesDropdown.style.display = "none";

                    

                    // Clear regencies ketika provinsi berubah (jika provinsi berbeda)

                    if (oldProvinceCode !== newProvinceCode) {

                        const regenciesInput = document.getElementById("regencies_text");

                        const regenciesHiddenInput = document.getElementById("regencies_id");

                        if (regenciesInput && regenciesHiddenInput) {

                            regenciesInput.value = "";

                            regenciesHiddenInput.value = "";

                        }

                    }

                    

                    saveFormDataToSession();

                }

            });

        }

        

        function filterProvincesOptions() {

            const provincesInput = document.getElementById("provinces_text");

            const provincesDropdown = document.getElementById("provinces_dropdown");

            

            if (!provincesInput || !provincesDropdown) return;

            

            const filterValue = provincesInput.value.toLowerCase();

            const items = provincesDropdown.getElementsByClassName("dropdown-item");

            

            // Jika tidak ada filter, tampilkan semua item

            if (!filterValue || filterValue.trim() === "") {

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                return;

            }

            

            // Filter berdasarkan text

            for (let i = 0; i < items.length; i++) {

                const text = items[i].getAttribute("data-text").toLowerCase();

                if (text.includes(filterValue)) {

                    items[i].style.display = "block";

                } else {

                    items[i].style.display = "none";

                }

            }

        }

        

        // Autocomplete untuk kabupaten/kota - dengan kontrol dropdown provinsi

        const regenciesInput = document.getElementById("regencies_text");

        const regenciesDropdown = document.getElementById("regencies_dropdown");

        const regenciesHiddenInput = document.getElementById("regencies_id");

        

        if (regenciesInput && regenciesDropdown && regenciesHiddenInput) {

            // Hanya muncul saat focus ke input box kabupaten/kota

            regenciesInput.addEventListener("focus", function(e) {

                e.stopPropagation();

                

                // Cek apakah provinsi sudah dipilih

                const provincesInput = document.getElementById("provinces_text");

                const provinceCode = provincesInput.getAttribute("data-province-code");

                

                if (!provinceCode || provinceCode === "") {

                    // Tampilkan pesan jika provinsi belum dipilih

                    regenciesDropdown.innerHTML = "<div class=\'dropdown-item\' style=\'color: #999; padding: 10px;\'>Silakan pilih provinsi terlebih dahulu</div>";

                    regenciesDropdown.style.display = "block";

                    return;

                }

                

                closeAllDropdowns("regencies_dropdown");

                // Tampilkan semua item dulu sebelum filter (semua item tetap ada di DOM)

                const items = regenciesDropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                regenciesDropdown.style.display = "block";

                filterRegenciesOptions();

            });

            

            // Hanya muncul saat ketik di input box kabupaten/kota

            regenciesInput.addEventListener("input", function(e) {

                e.stopPropagation();

                

                // Cek apakah provinsi sudah dipilih

                const provincesInput = document.getElementById("provinces_text");

                const provinceCode = provincesInput.getAttribute("data-province-code");

                

                if (!provinceCode || provinceCode === "") {

                    // Tampilkan pesan jika provinsi belum dipilih

                    regenciesDropdown.innerHTML = "<div class=\'dropdown-item\' style=\'color: #999; padding: 10px;\'>Silakan pilih provinsi terlebih dahulu</div>";

                    regenciesDropdown.style.display = "block";

                    return;

                }

                

                if (regenciesInput.value.trim() === "") {

                    regenciesHiddenInput.value = "";

                }

                closeAllDropdowns("regencies_dropdown");

                // Tampilkan semua item dulu sebelum filter (semua item tetap ada di DOM)

                const items = regenciesDropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                regenciesDropdown.style.display = "block";

                filterRegenciesOptions();

            });

            

            // Hanya muncul saat click di input box kabupaten/kota

            regenciesInput.addEventListener("click", function(e) {

                e.stopPropagation();

                

                // Cek apakah provinsi sudah dipilih

                const provincesInput = document.getElementById("provinces_text");

                const provinceCode = provincesInput.getAttribute("data-province-code");

                

                if (!provinceCode || provinceCode === "") {

                    // Tampilkan pesan jika provinsi belum dipilih

                    regenciesDropdown.innerHTML = "<div class=\'dropdown-item\' style=\'color: #999; padding: 10px;\'>Silakan pilih provinsi terlebih dahulu</div>";

                    regenciesDropdown.style.display = "block";

                    return;

                }

                

                closeAllDropdowns("regencies_dropdown");

                // Tampilkan semua item dulu sebelum filter (semua item tetap ada di DOM)

                const items = regenciesDropdown.getElementsByClassName("dropdown-item");

                for (let i = 0; i < items.length; i++) {

                    items[i].style.display = "block";

                }

                regenciesDropdown.style.display = "block";

                filterRegenciesOptions();

            });

            

            // Tutup dropdown saat click di luar (dengan delay untuk memastikan click input tidak tertutup)

            let clickOutsideRegenciesHandler = function(e) {

                const target = e.target;

                if (regenciesInput && regenciesDropdown) {

                    // Jangan tutup jika click di input atau dropdown

                    if (!regenciesInput.contains(target) && !regenciesDropdown.contains(target)) {

                        // Delay kecil untuk memastikan event click input sudah selesai

                        setTimeout(function() {

                            if (regenciesDropdown && regenciesDropdown.style.display === "block") {

                                regenciesDropdown.style.display = "none";

                            }

                        }, 50);

                    }

                }

            };

            document.addEventListener("click", clickOutsideRegenciesHandler, true);

            

            // Pilih item dari dropdown

            regenciesDropdown.addEventListener("click", function(e) {

                e.stopPropagation();

                if (e.target.classList.contains("dropdown-item")) {

                    regenciesInput.value = e.target.getAttribute("data-text");

                    regenciesHiddenInput.value = e.target.getAttribute("data-value");

                    regenciesDropdown.style.display = "none";

                    saveFormDataToSession();

                }

            });

        }

        

        function filterRegenciesOptions() {

            const regenciesInput = document.getElementById("regencies_text");

            const regenciesDropdown = document.getElementById("regencies_dropdown");

            const provincesInput = document.getElementById("provinces_text");

            

            if (!regenciesInput || !regenciesDropdown || !provincesInput) return;

            

            const filterValue = regenciesInput.value.toLowerCase();

            const provinceCode = provincesInput.getAttribute("data-province-code");

            

            // Jika provinsi belum dipilih, tampilkan pesan

            if (!provinceCode || provinceCode === "") {

                regenciesDropdown.innerHTML = "<div class=\'dropdown-item\' style=\'color: #999; padding: 10px;\'>Silakan pilih provinsi terlebih dahulu</div>";

                return;

            }

            

            // Ambil semua item regencies (restore dari innerHTML jika sudah diubah)

            let items = regenciesDropdown.getElementsByClassName("dropdown-item");

            

            // Jika items kosong, berarti innerHTML sudah diubah, perlu restore

            if (items.length === 0) {

                // Reload halaman atau restore dari backup jika ada

                // Untuk sementara, kita akan biarkan pesan error

                return;

            }

            

            // Filter berdasarkan text input dan kode provinsi

            for (let i = 0; i < items.length; i++) {

                const text = items[i].getAttribute("data-text").toLowerCase();

                const itemProvinceCode = items[i].getAttribute("data-province-code");

                

                // Tampilkan hanya jika cocok dengan provinsi dan (tidak ada filter atau cocok dengan text)

                if (itemProvinceCode === provinceCode && (!filterValue || filterValue.trim() === "" || text.includes(filterValue))) {

                    items[i].style.display = "block";

                } else {

                    items[i].style.display = "none";

                }

            }

        }

        

        
        
        // Auto-save saat ada perubahan form

        const form = document.getElementById("surveyForm");

        if (form) {

            form.addEventListener("change", function() {

                saveFormDataToSession();

            });

            
            
            form.addEventListener("input", function(e) {

                if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {

                    saveFormDataToSession();

                }

            });

        }

    });

    
    
    window.addEventListener("beforeunload", function(e) {

        // Jangan hapus data saat refresh

    });

</script>';



// ===== SECTION 2-N: PERTANYAAN SURVEI =====

if ($kuesioner) {

    $questions_per_section = 5;

    $total_sections = ceil(count($kuesioner) / $questions_per_section);

    
    
    for ($section = 0; $section < $total_sections; $section++) {

        $section_num = $section + 2;

        $start_idx = $section * $questions_per_section;

        $end_idx = min($start_idx + $questions_per_section, count($kuesioner));

        $section_questions = array_slice($kuesioner, $start_idx, $end_idx - $start_idx);

        
        
        $display_style = 'none';

        $content .= '<div id="part' . $section_num . '" style="display: ' . $display_style . ';">';

        $content .= '<div class="section-title">💡 PERTANYAAN SURVEI (' . ($section + 1) . '/' . $total_sections . ')</div>';

        
        
        foreach ($section_questions as $q) {

            $content .= renderQuestion($q, $db);

        }

        
        
        // Navigation buttons

        $content .= '<div class="button-group">';
        
        
        
        // Tombol Kembali (untuk semua section pertanyaan, kembali ke section sebelumnya atau ke informasi pribadi)
            $prev_section = $section_num - 1;
            $content .= '<button type="button" class="back-btn" onclick="saveAndGoToSection(' . $prev_section . ')">← Kembali</button>';
        
        
        
        // Tombol Lanjutkan / Kirim Survey
        if ($section < $total_sections - 1) {

            $next_section = $section_num + 1;

            $content .= '<button type="button" class="submit-btn" onclick="validateAndGoTo(' . $next_section . ')">Lanjutkan →</button>';

        } else {

            $content .= '<button type="button" class="submit-btn" id="submitFinalBtn">🎉 Kirim Survey</button>';

        }

        
        
        $content .= '</div>';

        $content .= '</div>';

    }

}



 $content .= '</form>';



// Include layout

include 'layout.php';



ob_end_flush();



/**

 * Render question based on type

 */

function renderQuestion($question, $db) {

    $html = '<div class="question-card">';

    $html .= '<div class="question-title">' . htmlspecialchars($question['pertanyaan']) . ' <span class="required">*</span></div>';

    
    
    $type = $question['tipe_jawaban'];

    $q_id = $question['id'];

    $field_name = 'kuesioner_' . $q_id;

    
    
    // Get options for this question

    $options = $db->getALL("SELECT * FROM opsi_jawaban WHERE kuesioner_id = $q_id AND status = 1 ORDER BY id ASC");

    
    
    switch ($type) {

        case 'pilihan':

            $html .= renderPilihan($q_id, $options, $field_name);

            break;
            
            
            
        case 'skala':

            $html .= renderSkala($q_id, $options, $field_name);

            break;
            
            
            
        case 'isian':

            $html .= renderIsian($field_name);

            break;
            
            
            
        case 'checkbox':

            $html .= renderCheckbox($q_id, $options, $field_name);

            break;

    }

    
    
    $html .= '</div>';

    return $html;

}



/**

 * Render pilihan (radio button)

 */

function renderPilihan($q_id, $options, $field_name) {

    $html = '<div class="radio-group">';

    
    
    if ($options) {

        foreach ($options as $opt) {

            $opt_id = 'opt_' . $q_id . '_' . $opt['id'];

            $opt_text = htmlspecialchars($opt['teks_opsi']);

            
            
            $html .= '

            <div class="radio-option">

                <input type="radio" id="' . $opt_id . '" name="' . $field_name . '" value="' . $opt['id'] . '" data-text="' . $opt_text . '" required>

                <label for="' . $opt_id . '">' . $opt_text . '</label>

            </div>';
            

        }

    }

    
    
    $html .= '</div>';

    return $html;

}



function renderSkala($q_id, $options, $field_name) {

    if (!$options || count($options) == 0) {

        return '';

    }

    
    
    // 5 emoji default selalu digunakan

    $emojis = ['😞', '😐', '🙂', '😊', '😍'];

    
    
    // Deteksi apakah perlu reverse urutan (jika pola positif->negatif)

    $shouldReverse = false;

    if (count($options) >= 3) {

        $negativeKeywords = ["tidak", "buruk", "jelek", "negatif", "kurang", "rendah", "jarang", "tidak pernah"];

        $positiveKeywords = ["setuju", "puas", "baik", "bagus", "suka", "tinggi", "sering", "setiap transaksi", "setiap hari"];

        
        
        $firstText = strtolower($options[0]['teks_opsi']);

        $lastText = strtolower($options[count($options)-1]['teks_opsi']);

        
        
        $firstIsPositive = false;

        $lastIsNegative = false;

        
        
        foreach ($positiveKeywords as $kw) {

            if (strpos($firstText, $kw) !== false) {

                $firstIsPositive = true;

                break;

            }

        }

        
        
        foreach ($negativeKeywords as $kw) {

            if (strpos($lastText, $kw) !== false) {

                $lastIsNegative = true;

                break;

            }

        }

        
        
        $shouldReverse = $firstIsPositive && $lastIsNegative;

    }

    
    
    // Jika perlu reverse, balik array

    if ($shouldReverse) {

        $options = array_reverse($options);

    }

    
    
    // Ambil hanya 5 emoji (jika options lebih dari 5, potong)

    $usedEmojis = array_slice($emojis, 0, min(5, count($options)));

    
    
    // Jika options kurang dari 5, tambah emoji sesuai jumlah

    while (count($usedEmojis) < count($options)) {

        $usedEmojis[] = end($usedEmojis);

    }

    
    
    $html = '

    <div class="rating-slider-container">

        <div class="rating-slider-track" id="track_' . $q_id . '">

            <div class="rating-slider-progress" id="sliderProgress_' . $q_id . '"></div>

            <div class="rating-points" id="ratingPoints_' . $q_id . '">';
    
    
    
    // Generate rating points dengan emoji

    foreach ($options as $idx => $opt) {

        $rating_id = 'rating_' . $q_id . '_' . $opt['id'];

        $emoji = isset($usedEmojis[$idx]) ? $usedEmojis[$idx] : '😐';

        
        
        $html .= '

                <div class="rating-point" data-value="' . htmlspecialchars($opt['id']) . '" data-text="' . htmlspecialchars($opt['teks_opsi']) . '" data-emoji="' . $emoji . '">

                    <div class="rating-point-indicator" id="indicator_' . $q_id . '_' . $opt['id'] . '"></div>

                    <div class="rating-point-emoji" id="emoji_' . $q_id . '_' . $opt['id'] . '">' . $emoji . '</div>

                    <div class="rating-point-dot"></div>

                    <input type="radio" id="' . $rating_id . '" name="' . $field_name . '" value="' . htmlspecialchars($opt['id']) . '" data-text="' . htmlspecialchars($opt['teks_opsi']) . '" style="display: none;" required>

                </div>';

    }

    
    
    $html .= '

            </div>

        </div>

        
        
        <div class="rating-labels">';
    
    
    
    foreach ($options as $opt) {

        $html .= '<div class="rating-label">' . htmlspecialchars($opt['teks_opsi']) . '</div>';

    }

    
    
    $html .= '

        </div>

        
        
        <div class="rating-value" id="ratingValue_' . $q_id . '">Pilih salah satu</div>

    </div>';
    
    
    
    $html .= '<script>

        document.addEventListener("DOMContentLoaded", function() {

            initRatingSlider(' . $q_id . ');

        });

        

        // Global state untuk tracking slider yang sedang aktif
        window.activeSliders = window.activeSliders || {};
        
        function initRatingSlider(qId) {

            const track = document.getElementById("track_" + qId);

            const points = document.querySelectorAll("#ratingPoints_" + qId + " .rating-point");

            
            if (!track || points.length === 0) return;
            
            // Default ke tengah
                const middleIdx = Math.floor(points.length / 2);
                const middlePoint = points[middleIdx];
            if (middlePoint) {
                setRating(qId, middlePoint.getAttribute("data-value"), middlePoint.getAttribute("data-text"));
                
                // Untuk pertanyaan yang start dari tengah, set progress bar awal di tengah
                const isCenterStartQuestion = qId == 7 || qId == 9;
                if (isCenterStartQuestion && points.length >= 3) {
                    // Delay sedikit untuk memastikan layout sudah selesai
                    setTimeout(function() {
                        const progressBar = document.getElementById("sliderProgress_" + qId);
                        if (progressBar && middlePoint) {
                            const middleRect = middlePoint.getBoundingClientRect();
                            const containerRect = track.getBoundingClientRect();
                            const middleCenterX = middleRect.left + middleRect.width / 2 - containerRect.left;
                            const containerWidth = containerRect.width;
                            const middlePercent = (middleCenterX / containerWidth) * 100;
                            progressBar.style.left = (middlePercent - 1) + "%";
                            progressBar.style.width = "2%";
                        }
                    }, 100);
                }
            }
            
            

            // Mouse events dengan tracking per slider

            track.addEventListener("mousedown", function(e) {

                e.stopPropagation();

                window.activeSliders[qId] = true;

                updateFromMouse(qId, e);

            });

            

            // Global mouse move handler (hanya sekali untuk semua slider)

            if (!window.ratingMouseMoveHandler) {

                window.ratingMouseMoveHandler = function(e) {

                    for (let id in window.activeSliders) {

                        if (window.activeSliders[id]) {

                            updateFromMouse(id, e);

                        }

                    }

                };

                document.addEventListener("mousemove", window.ratingMouseMoveHandler);

            }

            

            if (!window.ratingMouseUpHandler) {

                window.ratingMouseUpHandler = function() {

                    window.activeSliders = {};

                };

                document.addEventListener("mouseup", window.ratingMouseUpHandler);

            }

            

            // Touch events dengan tracking per slider

            track.addEventListener("touchstart", function(e) {

                e.stopPropagation();

                window.activeSliders[qId] = true;

                updateFromTouch(qId, e);

            });

            

            // Global touch move handler (hanya sekali untuk semua slider)

            if (!window.ratingTouchMoveHandler) {

                window.ratingTouchMoveHandler = function(e) {

                    for (let id in window.activeSliders) {

                        if (window.activeSliders[id]) {

                            updateFromTouch(id, e);

                        }

                    }

                };

                document.addEventListener("touchmove", window.ratingTouchMoveHandler);

            }

            

            if (!window.ratingTouchEndHandler) {

                window.ratingTouchEndHandler = function() {

                    window.activeSliders = {};

                };

                document.addEventListener("touchend", window.ratingTouchEndHandler);

            }

            
            
            // Click event pada setiap point

            points.forEach(function(point) {

                point.addEventListener("click", function(e) {

                    e.stopPropagation();

                    const value = this.getAttribute("data-value");

                    const text = this.getAttribute("data-text");

                    setRating(qId, value, text);

                });

            });

        }

        
        
        function updateFromMouse(qId, e) {

            const track = document.getElementById("track_" + qId);

            if (!track) return;

            

            const points = document.querySelectorAll("#ratingPoints_" + qId + " .rating-point");

            if (points.length === 0) return;

            

            const rect = track.getBoundingClientRect();

            const x = e.clientX - rect.left;

            const percent = Math.max(0, Math.min(100, (x / rect.width) * 100));

            const index = Math.round((percent / 100) * (points.length - 1));

            
            
            if (points[index]) {

                const value = points[index].getAttribute("data-value");

                const text = points[index].getAttribute("data-text");

                setRating(qId, value, text);

            }

        }

        
        
        function updateFromTouch(qId, e) {

            const track = document.getElementById("track_" + qId);

            if (!track) return;

            

            const points = document.querySelectorAll("#ratingPoints_" + qId + " .rating-point");

            if (points.length === 0) return;

            

            if (!e.touches || e.touches.length === 0) return;

            

            const rect = track.getBoundingClientRect();

            const touch = e.touches[0];

            const x = touch.clientX - rect.left;

            const percent = Math.max(0, Math.min(100, (x / rect.width) * 100));

            const index = Math.round((percent / 100) * (points.length - 1));

            
            
            if (points[index]) {

                const value = points[index].getAttribute("data-value");

                const text = points[index].getAttribute("data-text");

                setRating(qId, value, text);

            }

        }

        
        
        function setRating(qId, value, text) {
            const radioId = "rating_" + qId + "_" + value;
            const radioEl = document.getElementById(radioId);
            
            if (radioEl) {
                const point = radioEl.closest(".rating-point");
                const emoji = point ? point.getAttribute("data-emoji") : "😐";
                
                // Check radio button
                radioEl.checked = true;
                
                // Trigger change event untuk memastikan form ter-update dan auto-save
                try {
                    const changeEvent = new Event("change", { bubbles: true });
                    radioEl.dispatchEvent(changeEvent);
                } catch(e) {
                    // Fallback untuk browser lama
                    const changeEvent = document.createEvent("Event");
                    changeEvent.initEvent("change", true, true);
                    radioEl.dispatchEvent(changeEvent);
                }
                
                updateRatingDisplay(qId, value, text, emoji);
                
                // Pastikan auto-save dipanggil setelah update display
                if (typeof saveFormDataToSession === "function") {
                    saveFormDataToSession();
                }
            }
        }

        
        
        window.ratingTimeouts = window.ratingTimeouts || {};

        
        
        function updateRatingDisplay(qId, value, text, emoji) {

            const ratingValue = document.getElementById("ratingValue_" + qId);

            
            
            // Clear timeout sebelumnya

            if (window.ratingTimeouts && window.ratingTimeouts[qId]) {

                clearTimeout(window.ratingTimeouts[qId]);

            }

            
            
            // Tampilkan text value

            ratingValue.textContent = text;

            ratingValue.classList.add("selected");

            
            
            // Reset semua indicator

            const allIndicators = document.querySelectorAll("[id^=\'indicator_" + qId + "_\']");

            allIndicators.forEach(function(ind) {

                ind.textContent = "";

                ind.style.opacity = "0";

            });

            
            
            // Tampilkan indicator di point yang dipilih

            const activeIndicator = document.getElementById("indicator_" + qId + "_" + value);

            if (activeIndicator) {

                activeIndicator.textContent = emoji;

                activeIndicator.style.opacity = "1";

                
                
                // Fade out setelah 2 detik

                window.ratingTimeouts[qId] = setTimeout(function() {

                    activeIndicator.style.opacity = "0";

                    setTimeout(function() {

                        activeIndicator.textContent = "";

                    }, 300);

                }, 2000);

            }

            
            
            // Update active class

            const points = document.querySelectorAll("#ratingPoints_" + qId + " .rating-point");

            points.forEach(function(point) {

                if (point.getAttribute("data-value") === value) {

                    point.classList.add("active");

                } else {

                    point.classList.remove("active");

                }

            });

            
            
            // Update progress bar

            const progressBar = document.getElementById("sliderProgress_" + qId);

            if (progressBar && points.length > 0) {

                let selectedPoint = null;

                points.forEach(function(point) {

                    if (point.getAttribute("data-value") === value) {

                        selectedPoint = point;

                    }

                });

                
                
                if (selectedPoint) {
                    const pointRect = selectedPoint.getBoundingClientRect();
                    const containerRect = selectedPoint.closest(".rating-slider-track").getBoundingClientRect();
                    const dotCenterX = pointRect.left + pointRect.width / 2 - containerRect.left;
                    const containerWidth = containerRect.width;
                    
                    // Cek apakah pertanyaan ini adalah pertanyaan "lebih cepat" (kuesioner_id = 7)
                    // atau pertanyaan lain yang perlu start dari tengah
                    const isCenterStartQuestion = qId == 7 || qId == 9; // ID 7 dan 9 adalah pertanyaan setuju/tidak setuju
                    
                    if (isCenterStartQuestion && points.length >= 3) {
                        // Progress bar dimulai dari tengah (50%)
                        const middleIdx = Math.floor(points.length / 2);
                        const middlePoint = points[middleIdx];
                        const middleRect = middlePoint.getBoundingClientRect();
                        const middleCenterX = middleRect.left + middleRect.width / 2 - containerRect.left;
                        
                        // Cari index dari selectedPoint
                        let selectedIndex = -1;
                        points.forEach(function(point, idx) {
                            if (point.getAttribute("data-value") === value) {
                                selectedIndex = idx;
                            }
                        });
                        
                        if (selectedIndex < middleIdx) {
                            // Pilihan di kiri tengah: progress dari tengah ke kiri
                            const leftPercent = (dotCenterX / containerWidth) * 100;
                            progressBar.style.left = leftPercent + "%";
                            progressBar.style.width = (middleCenterX / containerWidth * 100 - leftPercent) + "%";
                        } else if (selectedIndex > middleIdx) {
                            // Pilihan di kanan tengah: progress dari tengah ke kanan
                            const middlePercent = (middleCenterX / containerWidth) * 100;
                            const rightPercent = (dotCenterX / containerWidth) * 100;
                            progressBar.style.left = middlePercent + "%";
                            progressBar.style.width = (rightPercent - middlePercent) + "%";
                        } else {
                            // Pilihan di tengah: progress hanya di tengah (sangat kecil)
                            const middlePercent = (middleCenterX / containerWidth) * 100;
                            progressBar.style.left = (middlePercent - 1) + "%";
                            progressBar.style.width = "2%";
                        }
                    } else {
                        // Progress bar normal (dari kiri ke kanan)
                    const widthPercent = (dotCenterX / containerWidth) * 100;
                        progressBar.style.left = "0%";
                    progressBar.style.width = widthPercent + "%";
                }
            }

            }

            
            
            // Auto-save

            if (typeof saveFormDataToSession === "function") {

                saveFormDataToSession();

            }

        }

    </script>';

    
    
    return $html;

}



/**

 * Render isian (text input)

 */

function renderIsian($field_name) {

    return '<textarea id="' . $field_name . '" name="' . $field_name . '" rows="4" placeholder="Tuliskan jawaban Anda..." required></textarea>';

}



/**

 * Render checkbox

 */

function renderCheckbox($q_id, $options, $field_name) {

    $html = '<small style="display: block; color: #666; margin-bottom: 10px;">Pilih semua yang sesuai</small>';

    $html .= '<div class="checkbox-group">';

    
    
    if ($options) {

        foreach ($options as $opt) {

            $opt_id = 'chk_' . $q_id . '_' . $opt['id'];

            $opt_text = htmlspecialchars($opt['teks_opsi']);

            
            
            $html .= '

            <div class="checkbox-option">

                <input type="checkbox" id="' . $opt_id . '" name="' . $field_name . '[]" value="' . $opt['id'] . '" data-text="' . $opt_text . '">

                <label for="' . $opt_id . '">' . $opt_text . '</label>

            </div>';
            

        }

    }

    
    
    $html .= '</div>';

    return $html;

}

?>
