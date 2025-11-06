<?php
session_start();
ob_start();

require_once 'db.php';
$db = new DB();

// Fetch data untuk dropdown
$pendidikan = $db->getALL("SELECT * FROM pendidikan WHERE status = 1 ORDER BY id ASC");
if (!is_array($pendidikan)) {
    $pendidikan = array();
}

$pekerjaan = $db->getALL("SELECT * FROM pekerjaan WHERE status = 1 ORDER BY id ASC");
if (!is_array($pekerjaan)) {
    $pekerjaan = array();
}

$penghasilan = $db->getALL("SELECT * FROM penghasilan WHERE status = 1 ORDER BY id ASC");
if (!is_array($penghasilan)) {
    $penghasilan = array();
}

$provinces = $db->getALL("SELECT * FROM provinces WHERE status = 1 ORDER BY name ASC");
if (!is_array($provinces)) {
    $provinces = array();
}

$regencies = $db->getALL("SELECT * FROM regencies WHERE status = 1 ORDER BY name ASC");
if (!is_array($regencies)) {
    $regencies = array();
}

// Fetch survey untuk header/logo (gunakan survei_id = 4 sebagai default)
$survei = $db->getITEM("SELECT * FROM survei WHERE id = 4");
if (!$survei) {
    die("Survey tidak ditemukan.");
}

// Override judul untuk form responden
$survei['judul'] = "Formulir Seleksi Responden Survei";

// Override deskripsi untuk form responden (akan digunakan di layout.php)
$survey_description = "Kami sedang melaksanakan survei untuk mengumpulkan insight dari berbagai kalangan. Silakan isi data berikut untuk membantu kami menentukan kesesuaian Anda sebagai responden. Semua informasi akan dijaga kerahasiaannya sesuai dengan kebijakan privasi kami.";

// Start building content
$content = '<form id="respondenForm" method="POST" action="process_responden.php">';

$content .= '
<div id="part1">
    <div class="section-title" style="border-bottom: 2px solid #1A4A72; padding-bottom: 10px; margin-bottom: 10px;">👤 INFORMASI PRIBADI</div>
    <div style="color: #666; font-size: 14px; margin-bottom: 25px; padding: 0 20px;">
        Mohon lengkapi data diri di bawah ini dengan jujur.
    </div>';

// Nama
$content .= '
    <div class="question-card">
        <div class="question-title">Nama Lengkap</div>
        <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" maxlength="100" pattern="[A-Za-z\s]+">
    </div>';

// Usia
$content .= '
    <div class="question-card">
        <div class="question-title">Usia <span class="required">*</span></div>
        <input type="number" id="usia" name="usia" placeholder="Masukkan usia Anda" min="1" max="100" required>
    </div>';

// Jenis Kelamin
$content .= '
    <div class="question-card">
        <div class="question-title">Jenis Kelamin <span class="required">*</span></div>
        <div class="radio-group">
            <div class="radio-option">
                <input type="radio" id="pria" name="jenis_kelamin" value="Laki-laki" required>
                <label for="pria">Laki-laki</label>
            </div>
            <div class="radio-option">
                <input type="radio" id="wanita" name="jenis_kelamin" value="Perempuan" required>
                <label for="wanita">Perempuan</label>
            </div>
        </div>
    </div>';

// Pendidikan
$content .= '
    <div class="question-card">
        <div class="question-title">Pendidikan Terakhir <span class="required">*</span></div>
        <div class="autocomplete-container">
            <input type="text" id="pendidikan_text" name="pendidikan_text" placeholder="Ketik atau pilih pendidikan terakhir Anda" required>
            <input type="hidden" id="pendidikan_id" name="pendidikan_id" required>
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
$content .= '
    <div class="question-card">
        <div class="question-title">Pekerjaan Saat Ini <span class="required">*</span></div>
        <div class="radio-group">';
if ($pekerjaan) {
    $index = 0;
    foreach ($pekerjaan as $p) {
        $index++;
        $radioId = 'pekerjaan' . $index;
        $content .= '
            <div class="radio-option">
                <input type="radio" id="' . $radioId . '" name="pekerjaan_id" value="' . $p['id'] . '" required>
                <label for="' . $radioId . '">' . htmlspecialchars($p['nama']) . '</label>
            </div>';
    }
}
$content .= '
        </div>
    </div>';

// Penghasilan
$content .= '
    <div class="question-card">
        <div class="question-title">Penghasilan Rata-rata per Bulan <span class="required">*</span></div>
        <div class="radio-group">';
if ($penghasilan) {
    $index = 0;
    foreach ($penghasilan as $pg) {
        $index++;
        $radioId = 'penghasilan' . $index;
        $content .= '
            <div class="radio-option">
                <input type="radio" id="' . $radioId . '" name="penghasilan_id" value="' . $pg['id'] . '" required>
                <label for="' . $radioId . '">' . htmlspecialchars($pg['kisaran']) . '</label>
            </div>';
    }
}
$content .= '
        </div>
    </div>';

// Nomor Telepon
$content .= '
    <div class="question-card">
        <div class="question-title">Nomor Telepon <span class="required">*</span></div>
        <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon Anda" minlength="10" maxlength="13" pattern="[0-9]+" required>
        <div id="nomor_telepon_error" style="color: #d32f2f; font-size: 12px; margin-top: 5px; display: none;"></div>
    </div>';

// Provinsi
$content .= '
    <div class="question-card" id="provinces_card">
        <div class="question-title">Provinsi <span class="required">*</span></div>
        <div class="autocomplete-container">
            <input type="text" id="provinces_text" name="provinces_text" placeholder="Ketik atau pilih provinsi Anda" required>
            <input type="hidden" id="provinces_id" name="provinces_id" required>
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

// Kabupaten/Kota
$content .= '
    <div class="question-card" id="regencies_card">
        <div class="question-title">Kabupaten/Kota <span class="required">*</span></div>
        <div class="autocomplete-container">
            <input type="text" id="regencies_text" name="regencies_text" placeholder="Ketik atau pilih kabupaten/kota Anda" required>
            <input type="hidden" id="regencies_id" name="regencies_id" required>
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
$content .= '
    <div class="question-card">
        <div class="question-title">Ketersediaan Menjadi Responden Survei Selanjutnya <span class="required">*</span></div>
        <div class="radio-group">
            <div class="radio-option">
                <input type="radio" id="bersedia" name="kesediaan_menjadi_responden" value="1" required>
                <label for="bersedia">Bersedia</label>
            </div>
            <div class="radio-option">
                <input type="radio" id="tidak_bersedia" name="kesediaan_menjadi_responden" value="2" required>
                <label for="tidak_bersedia">Tidak Bersedia</label>
            </div>
        </div>
    </div>';

// Submit button
$content .= '
    <div style="text-align: center; margin-top: 20px;">
        <button type="button" class="submit-btn" id="submitRespondenBtn">Kirim Data</button>
    </div>
</div>';

$content .= '</form>';

// Success message akan di-override di layout.php, tapi kita tambahkan yang khusus untuk responden
$content .= '
<div id="successMessageResponden" class="success-message" style="display: none;">
    <div class="success-icon">✓</div>
    <h2>Data Berhasil Terkirim!</h2>
    <p>Terima kasih telah mengisi data diri Anda. Data yang Anda berikan telah berhasil disimpan.</p>
</div>';

// Include layout
include 'layout.php';
?>

<script>
// Copy JavaScript dari index.php untuk validasi dan autocomplete
// (akan di-include di layout.php)
document.addEventListener("DOMContentLoaded", function() {
    // Validasi nama: hanya huruf dan spasi
    const namaInput = document.getElementById("nama");
    if (namaInput) {
        namaInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, "");
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

    // Validasi nomor telepon: cek duplikat (copy dari index.php)
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

            phoneCheckTimeout = setTimeout(function() {
                checkPhoneNumber(phoneValue);
            }, 500);
        });

        nomorTeleponInput.addEventListener("blur", function() {
            const phoneValue = this.value.trim();
            // Validasi panjang minimal saat blur
            if (phoneValue.length > 0 && phoneValue.length < 10) {
                nomorTeleponError.textContent = "Nomor telepon minimal 10 angka";
                nomorTeleponError.style.display = "block";
                nomorTeleponInput.style.borderColor = "#d32f2f";
                nomorTeleponInput.setCustomValidity("Nomor telepon minimal 10 angka");
                isPhoneValid = false;
                return;
            }
            if (phoneValue.length >= 10) {
                checkPhoneNumber(phoneValue);
            }
        });
    }

    function checkPhoneNumber(phoneNumber) {
        const formData = new FormData();
        formData.append("nomor_telepon", phoneNumber);

        fetch("check_phone.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "duplicate") {
                nomorTeleponError.textContent = data.message;
                nomorTeleponError.style.display = "block";
                nomorTeleponInput.style.borderColor = "#d32f2f";
                nomorTeleponInput.setCustomValidity(data.message);
                isPhoneValid = false;
            } else if (data.status === "available") {
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

    // Submit button dengan captcha
    const submitBtn = document.getElementById("submitRespondenBtn");
    if (submitBtn) {
        submitBtn.addEventListener("click", function() {
            const form = document.getElementById("respondenForm");
            
            // Validasi form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Cek nomor telepon jika sudah diisi
            if (nomorTeleponInput && nomorTeleponInput.value.trim() !== '') {
                if (!nomorTeleponInput.checkValidity()) {
                    alert(nomorTeleponError.textContent || "Nomor telepon tidak valid");
                    nomorTeleponInput.focus();
                    return;
                }
            }

            // Tampilkan modal captcha
            generateCaptcha();
            document.getElementById('captchaModal').style.display = 'block';
        });
    }

    // Override captcha verification untuk form responden
    const existingVerifyBtn = document.getElementById('verifyAndSubmitBtn');
    if (existingVerifyBtn) {
        // Simpan handler asli untuk form survey
        const originalHandler = existingVerifyBtn.onclick;
        
        // Cek apakah ini form responden
        const respondenForm = document.getElementById('respondenForm');
        if (respondenForm) {
            // Override untuk form responden
            existingVerifyBtn.onclick = function() {
                const userAnswer = parseInt(document.getElementById('captchaAnswer').value);
                
                if (typeof captchaAnswer === 'undefined' || isNaN(userAnswer) || userAnswer !== captchaAnswer) {
                    document.getElementById('captchaError').classList.add('show');
                    return;
                }

                // Submit form responden
                const form = document.getElementById('respondenForm');
                const formData = new FormData(form);
                formData.append('captcha_verified', 'true');

                fetch('process_responden.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('captchaModal').style.display = 'none';
                        document.getElementById('respondenForm').style.display = 'none';
                        // Tampilkan success message (gunakan ID yang sesuai)
                        const successMsg = document.getElementById('successMessageResponden') || document.getElementById('successMessage');
                        if (successMsg) {
                            successMsg.style.display = 'block';
                            successMsg.classList.add('show');
                        }
                        if (document.querySelector('.header')) {
                            document.querySelector('.header').style.display = 'none';
                        }
                        if (document.querySelector('.progress-bar')) {
                            document.querySelector('.progress-bar').style.display = 'none';
                        }
                    } else {
                        alert('Terjadi kesalahan: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
            };
        }
    }

    // Autocomplete untuk Pendidikan, Provinsi, dan Kabupaten
    // (Menggunakan logika yang sama dengan index.php)
    
    function closeAllDropdowns(exceptId = null) {
        const dropdowns = document.querySelectorAll('.autocomplete-dropdown');
        dropdowns.forEach(d => {
            if (d.id !== exceptId) d.style.display = 'none';
        });
    }

    // Autocomplete Pendidikan
    const pendidikanInput = document.getElementById("pendidikan_text");
    const pendidikanDropdown = document.getElementById("pendidikan_dropdown");
    const pendidikanHidden = document.getElementById("pendidikan_id");
    
    if (pendidikanInput && pendidikanDropdown && pendidikanHidden) {
        function filterPendidikan() {
            const filter = pendidikanInput.value.toLowerCase();
            const items = pendidikanDropdown.getElementsByClassName("dropdown-item");
            for (let i = 0; i < items.length; i++) {
                const text = items[i].getAttribute("data-text").toLowerCase();
                items[i].style.display = text.includes(filter) ? "block" : "none";
            }
        }
        
        pendidikanInput.addEventListener("focus", () => {
            closeAllDropdowns("pendidikan_dropdown");
            pendidikanDropdown.style.display = "block";
            filterPendidikan();
        });
        
        pendidikanInput.addEventListener("input", () => {
            closeAllDropdowns("pendidikan_dropdown");
            pendidikanDropdown.style.display = "block";
            filterPendidikan();
        });
        
        pendidikanDropdown.addEventListener("click", (e) => {
            if (e.target.classList.contains("dropdown-item")) {
                pendidikanInput.value = e.target.getAttribute("data-text");
                pendidikanHidden.value = e.target.getAttribute("data-value");
                pendidikanDropdown.style.display = "none";
            }
        });
    }

    // Autocomplete Provinsi
    const provincesInput = document.getElementById("provinces_text");
    const provincesDropdown = document.getElementById("provinces_dropdown");
    const provincesHidden = document.getElementById("provinces_id");
    
    if (provincesInput && provincesDropdown && provincesHidden) {
        function filterProvinces() {
            const filter = provincesInput.value.toLowerCase();
            const items = provincesDropdown.getElementsByClassName("dropdown-item");
            for (let i = 0; i < items.length; i++) {
                const text = items[i].getAttribute("data-text").toLowerCase();
                items[i].style.display = text.includes(filter) ? "block" : "none";
            }
        }
        
        provincesInput.addEventListener("focus", () => {
            closeAllDropdowns("provinces_dropdown");
            provincesDropdown.style.display = "block";
            filterProvinces();
        });
        
        provincesInput.addEventListener("input", () => {
            closeAllDropdowns("provinces_dropdown");
            provincesDropdown.style.display = "block";
            filterProvinces();
            if (provincesInput.value.trim() === "") {
                provincesHidden.value = "";
                provincesInput.removeAttribute("data-province-code");
                document.getElementById("regencies_text").value = "";
                document.getElementById("regencies_id").value = "";
            }
        });
        
        provincesDropdown.addEventListener("click", (e) => {
            if (e.target.classList.contains("dropdown-item")) {
                const oldCode = provincesInput.getAttribute("data-province-code");
                const newCode = e.target.getAttribute("data-province-code");
                provincesInput.value = e.target.getAttribute("data-text");
                provincesHidden.value = e.target.getAttribute("data-value");
                provincesInput.setAttribute("data-province-code", newCode);
                provincesDropdown.style.display = "none";
                if (oldCode !== newCode) {
                    document.getElementById("regencies_text").value = "";
                    document.getElementById("regencies_id").value = "";
                }
            }
        });
    }

    // Autocomplete Kabupaten/Kota
    const regenciesInput = document.getElementById("regencies_text");
    const regenciesDropdown = document.getElementById("regencies_dropdown");
    const regenciesHidden = document.getElementById("regencies_id");
    
    // Simpan original HTML untuk restore jika perlu
    let originalRegenciesHTML = null;
    if (regenciesDropdown) {
        originalRegenciesHTML = regenciesDropdown.innerHTML;
    }
    
    if (regenciesInput && regenciesDropdown && regenciesHidden) {
        function filterRegencies() {
            const filter = regenciesInput.value.toLowerCase();
            const provinceCode = provincesInput ? provincesInput.getAttribute("data-province-code") : "";
            
            if (!provinceCode) {
                // Restore original HTML jika ada
                if (originalRegenciesHTML) {
                    regenciesDropdown.innerHTML = originalRegenciesHTML;
                }
                // Tampilkan pesan
                const items = regenciesDropdown.getElementsByClassName("dropdown-item");
                for (let i = 0; i < items.length; i++) {
                    items[i].style.display = "none";
                }
                // Tambahkan pesan jika belum ada
                let messageExists = false;
                for (let i = 0; i < items.length; i++) {
                    if (items[i].textContent.includes("Silakan pilih provinsi")) {
                        messageExists = true;
                        items[i].style.display = "block";
                        break;
                    }
                }
                if (!messageExists && originalRegenciesHTML) {
                    const msgDiv = document.createElement("div");
                    msgDiv.className = "dropdown-item";
                    msgDiv.style.cssText = "color: #999; padding: 10px;";
                    msgDiv.textContent = "Silakan pilih provinsi terlebih dahulu";
                    regenciesDropdown.appendChild(msgDiv);
                }
                return;
            }
            
            // Restore original HTML jika sudah diubah
            if (regenciesDropdown.innerHTML !== originalRegenciesHTML) {
                regenciesDropdown.innerHTML = originalRegenciesHTML;
            }
            
            const items = regenciesDropdown.getElementsByClassName("dropdown-item");
            for (let i = 0; i < items.length; i++) {
                const text = items[i].getAttribute("data-text");
                if (!text) continue; // Skip jika bukan item valid
                const textLower = text.toLowerCase();
                const itemProvinceCode = items[i].getAttribute("data-province-code");
                items[i].style.display = (itemProvinceCode === provinceCode && textLower.includes(filter)) ? "block" : "none";
            }
        }
        
        regenciesInput.addEventListener("focus", () => {
            const provinceCode = provincesInput ? provincesInput.getAttribute("data-province-code") : "";
            closeAllDropdowns("regencies_dropdown");
            regenciesDropdown.style.display = "block";
            filterRegencies();
        });
        
        regenciesInput.addEventListener("input", () => {
            const provinceCode = provincesInput ? provincesInput.getAttribute("data-province-code") : "";
            closeAllDropdowns("regencies_dropdown");
            regenciesDropdown.style.display = "block";
            filterRegencies();
        });
        
        regenciesDropdown.addEventListener("click", (e) => {
            if (e.target.classList.contains("dropdown-item") && e.target.getAttribute("data-value")) {
                regenciesInput.value = e.target.getAttribute("data-text");
                regenciesHidden.value = e.target.getAttribute("data-value");
                regenciesDropdown.style.display = "none";
            }
        });
    }

    // Close dropdowns saat click di luar
    document.addEventListener("click", (e) => {
        if (!e.target.closest('.autocomplete-container')) {
            closeAllDropdowns();
        }
    });
});
</script>
