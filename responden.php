<?php
session_start();
ob_start();

require_once 'db.php';
$db = new DB();

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

$survei = $db->getITEM("SELECT * FROM survei WHERE id = 4");
if (!$survei) {
    die("Survey tidak ditemukan.");
}

$survei['judul'] = "Formulir Seleksi Responden Survei";
$survey_description = "Kami sedang melaksanakan survei untuk mengumpulkan insight dari berbagai kalangan. Silakan isi data berikut untuk membantu kami menentukan kesesuaian Anda sebagai responden. Semua informasi akan dijaga kerahasiaannya sesuai dengan kebijakan privasi kami.";

$content = '<form id="respondenForm" method="POST" action="process_responden.php">';
$content .= '<input type="hidden" name="kesediaan_menjadi_responden" value="1">';

$content .= '
<div id="part1">
    <div class="section-title" style="border-bottom: 2px solid #1A4A72; padding-bottom: 10px; margin-bottom: 10px;">👤 INFORMASI PRIBADI</div>
    <div style="color: #666; font-size: 14px; margin-bottom: 25px; padding: 0 20px;">
        Mohon lengkapi data diri di bawah ini dengan jujur.
    </div>';

$content .= '
    <div class="question-card">
        <div class="question-title">Nama Lengkap</div>
        <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" maxlength="100" pattern="[A-Za-z\s]+">
    </div>';

$content .= '
    <div class="question-card">
        <div class="question-title">Usia <span class="required">*</span></div>
        <input type="number" id="usia" name="usia" placeholder="Masukkan usia Anda" min="1" max="100" maxlength="3" required>
    </div>';

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

$content .= '
    <div class="question-card">
        <div class="question-title">Nomor Telepon <span class="required">*</span></div>
        <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon Anda" minlength="10" maxlength="13" pattern="[0-9]+" required>
        <div id="nomor_telepon_error" style="color: #d32f2f; font-size: 12px; margin-top: 5px; display: none;"></div>
    </div>';

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

$content .= '
    <div style="text-align: center; margin-top: 20px;">
        <button type="button" class="submit-btn" id="submitRespondenBtn">Kirim Data</button>
    </div>
</div>';

$content .= '</form>';

$content .= '
<div id="successMessageResponden" class="success-message" style="display: none;">
    <div class="success-icon">✓</div>
    <h2>Data Berhasil Terkirim!</h2>
    <p>Terima kasih telah mengisi data diri Anda. Data yang Anda berikan telah berhasil disimpan.</p>
</div>';

include 'layout.php';
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const namaInput = document.getElementById("nama");
    if (namaInput) {
        namaInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, "");
            if (this.value.length > 100) {
                this.value = this.value.substring(0, 100);
            }
        });
    }

    const usiaInput = document.getElementById("usia");
    if (usiaInput) {
        usiaInput.setAttribute("max", "100");
        usiaInput.setAttribute("maxlength", "3");
        
        usiaInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, "");
            const usiaValue = parseInt(this.value) || 0;
            if (usiaValue > 100) {
                this.value = 100;
            }
        });
        
        usiaInput.addEventListener("change", function() {
            const usiaValue = parseInt(this.value) || 0;
            if (usiaValue > 100) {
                this.value = 100;
            } else if (usiaValue < 1 && this.value !== "") {
                this.value = 1;
            }
        });
        
        usiaInput.addEventListener("blur", function() {
            const usiaValue = parseInt(this.value) || 0;
            if (usiaValue > 100) {
                this.value = 100;
            } else if (usiaValue < 1 && this.value !== "") {
                this.value = 1;
            }
        });
        
        usiaInput.addEventListener("keydown", function(e) {
            const currentValue = this.value;
            const key = e.key;
            if (key === "Backspace" || key === "Delete" || key === "Tab" || 
                key.indexOf("Arrow") === 0 || key === "Home" || key === "End" || 
                e.ctrlKey || e.metaKey) {
                return;
            }
            if (parseInt(currentValue) >= 100 && /[0-9]/.test(key)) {
                e.preventDefault();
                return;
            }
            if (/[0-9]/.test(key)) {
                const newValue = parseInt(currentValue + key) || 0;
                if (newValue > 100) {
                    e.preventDefault();
                }
            }
        });
        
        usiaInput.addEventListener("paste", function(e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData("text");
            const numericData = pastedData.replace(/[^0-9]/g, "");
            const numericValue = parseInt(numericData) || 0;
            if (numericValue > 100) {
                this.value = 100;
            } else {
                this.value = numericValue;
            }
        });
    }

    const nomorTeleponInput = document.getElementById("nomor_telepon");
    const nomorTeleponError = document.getElementById("nomor_telepon_error");
    let phoneCheckTimeout = null;

    if (nomorTeleponInput && nomorTeleponError) {
        nomorTeleponInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, "");
            if (this.value.length > 13) {
                this.value = this.value.substring(0, 13);
            }
        });
        
        nomorTeleponInput.addEventListener("input", function() {
            clearTimeout(phoneCheckTimeout);
            const phoneValue = this.value.trim();
            
            nomorTeleponError.style.display = "none";
            nomorTeleponError.textContent = "";
            nomorTeleponInput.style.borderColor = "";

            if (phoneValue.length > 0 && phoneValue.length < 10) {
                nomorTeleponError.textContent = "Nomor telepon minimal 10 angka";
                nomorTeleponError.style.display = "block";
                nomorTeleponInput.style.borderColor = "#d32f2f";
                nomorTeleponInput.setCustomValidity("Nomor telepon minimal 10 angka");
                return;
            }

            if (phoneValue.length === 0 || phoneValue.length < 10) {
                return;
            }

            phoneCheckTimeout = setTimeout(function() {
                checkPhoneNumber(phoneValue);
            }, 500);
        });

        nomorTeleponInput.addEventListener("blur", function() {
            const phoneValue = this.value.trim();
            if (phoneValue.length > 0 && phoneValue.length < 10) {
                nomorTeleponError.textContent = "Nomor telepon minimal 10 angka";
                nomorTeleponError.style.display = "block";
                nomorTeleponInput.style.borderColor = "#d32f2f";
                nomorTeleponInput.setCustomValidity("Nomor telepon minimal 10 angka");
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
        
        const respondenIdInput = document.querySelector("input[name=\"responden_id\"]");
        if (respondenIdInput && respondenIdInput.value) {
            formData.append("responden_id", respondenIdInput.value);
        }

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
            } else if (data.status === "available") {
                nomorTeleponError.style.display = "none";
                nomorTeleponInput.style.borderColor = "";
                nomorTeleponInput.setCustomValidity("");
            }
        })
        .catch(error => {
            console.error("Error checking phone number:", error);
        });
    }

    const submitBtn = document.getElementById("submitRespondenBtn");
    if (submitBtn) {
        submitBtn.addEventListener("click", function() {
            const form = document.getElementById("respondenForm");
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (nomorTeleponInput && nomorTeleponInput.value.trim() !== '') {
                if (!nomorTeleponInput.checkValidity()) {
                    alert(nomorTeleponError.textContent || "Nomor telepon tidak valid");
                    nomorTeleponInput.focus();
                    return;
                }
            }

            generateCaptcha();
            document.getElementById('captchaModal').style.display = 'block';
        });
    }

    const existingVerifyBtn = document.getElementById('verifyAndSubmitBtn');
    if (existingVerifyBtn) {
        const respondenForm = document.getElementById('respondenForm');
        if (respondenForm) {
            existingVerifyBtn.onclick = function() {
                const userAnswer = parseInt(document.getElementById('captchaAnswer').value);
                
                if (typeof captchaAnswer === 'undefined' || isNaN(userAnswer) || userAnswer !== captchaAnswer) {
                    document.getElementById('captchaError').classList.add('show');
                    return;
                }

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

    function closeAllDropdowns(exceptId = null) {
        const dropdowns = document.querySelectorAll(".autocomplete-dropdown");
        dropdowns.forEach(function(d) {
            if (d.id !== exceptId) {
                d.style.display = "none";
            }
        });
    }

    const input = document.getElementById("pendidikan_text");
    const dropdown = document.getElementById("pendidikan_dropdown");
    const hiddenInput = document.getElementById("pendidikan_id");

    function showPendidikanDropdown() {
        if (!input || !dropdown) return;
        closeAllDropdowns("pendidikan_dropdown");
        const items = dropdown.getElementsByClassName("dropdown-item");
        for (let i = 0; i < items.length; i++) {
            items[i].style.display = "block";
        }
        dropdown.style.display = "block";
        filterOptions();
    }

    if (input && dropdown && hiddenInput) {
        input.addEventListener("focus", function(e) {
            e.stopPropagation();
            setTimeout(showPendidikanDropdown, 10);
        });

        input.addEventListener("click", function(e) {
            e.stopPropagation();
            setTimeout(showPendidikanDropdown, 10);
        });
        
        input.addEventListener("input", function() {
            showPendidikanDropdown();
        });

        document.addEventListener("click", function(e) {
            const target = e.target;
            if (input && dropdown) {
                if (!input.contains(target) && !dropdown.contains(target)) {
                    dropdown.style.display = "none";
                }
            }
        });
        
        dropdown.addEventListener("click", function(e) {
            e.stopPropagation();
            if (e.target.classList.contains("dropdown-item")) {
                input.value = e.target.getAttribute("data-text");
                hiddenInput.value = e.target.getAttribute("data-value");
                dropdown.style.display = "none";
            }
        });
    }
    
    function filterOptions() {
        const input = document.getElementById("pendidikan_text");
        const dropdown = document.getElementById("pendidikan_dropdown");

        if (!input || !dropdown) return;

        const filterValue = input.value.toLowerCase();
        const items = dropdown.getElementsByClassName("dropdown-item");

        if (!filterValue || filterValue.trim() === "") {
            for (let i = 0; i < items.length; i++) {
                items[i].style.display = "block";
            }
            return;
        }

        for (let i = 0; i < items.length; i++) {
            const text = items[i].getAttribute("data-text").toLowerCase();
            if (text.includes(filterValue)) {
                items[i].style.display = "block";
            } else {
                items[i].style.display = "none";
            }
        }
    }

    const provincesInput = document.getElementById("provinces_text");
    const provincesDropdown = document.getElementById("provinces_dropdown");
    const provincesHiddenInput = document.getElementById("provinces_id");

    if (provincesInput && provincesDropdown && provincesHiddenInput) {
        provincesInput.addEventListener("focus", function(e) {
            e.stopPropagation();
            closeAllDropdowns("provinces_dropdown");
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
                const regenciesInput = document.getElementById("regencies_text");
                const regenciesHiddenInput = document.getElementById("regencies_id");
                if (regenciesInput && regenciesHiddenInput) {
                    regenciesInput.value = "";
                    regenciesHiddenInput.value = "";
                }
            }
            closeAllDropdowns("provinces_dropdown");
            const items = provincesDropdown.getElementsByClassName("dropdown-item");
            for (let i = 0; i < items.length; i++) {
                items[i].style.display = "block";
            }
            provincesDropdown.style.display = "block";
            filterProvincesOptions();
        });

        let clickOutsideHandler = function(e) {
            const target = e.target;
            if (provincesInput && provincesDropdown) {
                if (!provincesInput.contains(target) && !provincesDropdown.contains(target)) {
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
                
                if (oldProvinceCode !== newProvinceCode) {
                    const regenciesInput = document.getElementById("regencies_text");
                    const regenciesHiddenInput = document.getElementById("regencies_id");
                    if (regenciesInput && regenciesHiddenInput) {
                        regenciesInput.value = "";
                        regenciesHiddenInput.value = "";
                    }
                }
            }
        });
    }

    function filterProvincesOptions() {
        const provincesInput = document.getElementById("provinces_text");
        const provincesDropdown = document.getElementById("provinces_dropdown");
        
        if (!provincesInput || !provincesDropdown) return;
        
        const filterValue = provincesInput.value.toLowerCase();
        const items = provincesDropdown.getElementsByClassName("dropdown-item");
        
        if (!filterValue || filterValue.trim() === "") {
            for (let i = 0; i < items.length; i++) {
                items[i].style.display = "block";
            }
            return;
        }
        
        for (let i = 0; i < items.length; i++) {
            const text = items[i].getAttribute("data-text").toLowerCase();
            if (text.includes(filterValue)) {
                items[i].style.display = "block";
            } else {
                items[i].style.display = "none";
            }
        }
    }

    const regenciesInput = document.getElementById("regencies_text");
    const regenciesDropdown = document.getElementById("regencies_dropdown");
    const regenciesHiddenInput = document.getElementById("regencies_id");

    if (regenciesInput && regenciesDropdown && regenciesHiddenInput) {
        regenciesInput.addEventListener("focus", function(e) {
            e.stopPropagation();
            
            const provincesInput = document.getElementById("provinces_text");
            const provinceCode = provincesInput.getAttribute("data-province-code");
            
            if (!provinceCode || provinceCode === "") {
                regenciesDropdown.innerHTML = "<div class=\"dropdown-item\" style=\"color: #999; padding: 10px;\">Silakan pilih provinsi terlebih dahulu</div>";
                regenciesDropdown.style.display = "block";
                return;
            }
            
            closeAllDropdowns("regencies_dropdown");
            const items = regenciesDropdown.getElementsByClassName("dropdown-item");
            for (let i = 0; i < items.length; i++) {
                items[i].style.display = "block";
            }
            regenciesDropdown.style.display = "block";
            filterRegenciesOptions();
        });

        regenciesInput.addEventListener("input", function(e) {
            e.stopPropagation();
            
            const provincesInput = document.getElementById("provinces_text");
            const provinceCode = provincesInput.getAttribute("data-province-code");
            
            if (!provinceCode || provinceCode === "") {
                regenciesDropdown.innerHTML = "<div class=\"dropdown-item\" style=\"color: #999; padding: 10px;\">Silakan pilih provinsi terlebih dahulu</div>";
                regenciesDropdown.style.display = "block";
                return;
            }
            
            if (regenciesInput.value.trim() === "") {
                regenciesHiddenInput.value = "";
            }
            closeAllDropdowns("regencies_dropdown");
            const items = regenciesDropdown.getElementsByClassName("dropdown-item");
            for (let i = 0; i < items.length; i++) {
                items[i].style.display = "block";
            }
            regenciesDropdown.style.display = "block";
            filterRegenciesOptions();
        });

        regenciesInput.addEventListener("click", function(e) {
            e.stopPropagation();
            
            const provincesInput = document.getElementById("provinces_text");
            const provinceCode = provincesInput.getAttribute("data-province-code");
            
            if (!provinceCode || provinceCode === "") {
                regenciesDropdown.innerHTML = "<div class=\"dropdown-item\" style=\"color: #999; padding: 10px;\">Silakan pilih provinsi terlebih dahulu</div>";
                regenciesDropdown.style.display = "block";
                return;
            }
            
            closeAllDropdowns("regencies_dropdown");
            const items = regenciesDropdown.getElementsByClassName("dropdown-item");
            for (let i = 0; i < items.length; i++) {
                items[i].style.display = "block";
            }
            regenciesDropdown.style.display = "block";
            filterRegenciesOptions();
        });

        let clickOutsideRegenciesHandler = function(e) {
            const target = e.target;
            if (regenciesInput && regenciesDropdown) {
                if (!regenciesInput.contains(target) && !regenciesDropdown.contains(target)) {
                    setTimeout(function() {
                        if (regenciesDropdown && regenciesDropdown.style.display === "block") {
                            regenciesDropdown.style.display = "none";
                        }
                    }, 50);
                }
            }
        };
        document.addEventListener("click", clickOutsideRegenciesHandler, true);
        
        regenciesDropdown.addEventListener("click", function(e) {
            e.stopPropagation();
            if (e.target.classList.contains("dropdown-item")) {
                regenciesInput.value = e.target.getAttribute("data-text");
                regenciesHiddenInput.value = e.target.getAttribute("data-value");
                regenciesDropdown.style.display = "none";
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
        
        if (!provinceCode || provinceCode === "") {
            regenciesDropdown.innerHTML = "<div class=\"dropdown-item\" style=\"color: #999; padding: 10px;\">Silakan pilih provinsi terlebih dahulu</div>";
            return;
        }
        
        let items = regenciesDropdown.getElementsByClassName("dropdown-item");
        
        if (items.length === 0) {
            return;
        }
        
        for (let i = 0; i < items.length; i++) {
            const text = items[i].getAttribute("data-text").toLowerCase();
            const itemProvinceCode = items[i].getAttribute("data-province-code");
            
            if (itemProvinceCode === provinceCode && (!filterValue || filterValue.trim() === "" || text.includes(filterValue))) {
                items[i].style.display = "block";
            } else {
                items[i].style.display = "none";
            }
        }
    }
});
</script>

