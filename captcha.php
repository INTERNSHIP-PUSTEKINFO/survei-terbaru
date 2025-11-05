<?php
session_start();

// Jika request untuk mendapatkan jawaban (JSON)
if (isset($_GET['action']) && $_GET['action'] === 'answer') {
    header('Content-Type: application/json');
    
    // Return jawaban captcha dari session
    if (isset($_SESSION['captcha_result'])) {
        echo json_encode([
            'answer' => (int)$_SESSION['captcha_result']
        ]);
    } else {
        // Jika tidak ada session, generate baru
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $operator = rand(0, 1) ? '+' : '-';
        $result = $operator == '+' ? $num1 + $num2 : $num1 - $num2;
        
        if ($result < 0) {
            $result = $num2 - $num1;
        }
        
        $_SESSION['captcha_result'] = $result;
        
        echo json_encode([
            'answer' => (int)$result
        ]);
    }
    exit;
}

// Default: Generate gambar captcha
// Generate random math captcha
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$operator = rand(0, 1) ? '+' : '-';
$result = $operator == '+' ? $num1 + $num2 : $num1 - $num2;

// Pastikan hasil tidak negatif
if ($result < 0) {
    $result = $num2 - $num1;
    $question = "$num2 - $num1";
} else {
    $question = "$num1 $operator $num2";
}

// Simpan hasil di session
$_SESSION['captcha_result'] = $result;

// Buat gambar captcha
$width = 200;
$height = 60;
$image = imagecreatetruecolor($width, $height);

// Warna background (gradient)
$bg_color1 = imagecolorallocate($image, rand(240, 255), rand(240, 255), rand(240, 255));
$bg_color2 = imagecolorallocate($image, rand(230, 250), rand(230, 250), rand(230, 250));
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color1);

// Buat gradient background
for ($i = 0; $i < $height; $i++) {
    $ratio = $i / $height;
    $r = (int)(($ratio * (($bg_color2 >> 16) & 0xFF)) + ((1 - $ratio) * (($bg_color1 >> 16) & 0xFF)));
    $g = (int)(($ratio * (($bg_color2 >> 8) & 0xFF)) + ((1 - $ratio) * (($bg_color1 >> 8) & 0xFF)));
    $b = (int)(($ratio * ($bg_color2 & 0xFF)) + ((1 - $ratio) * ($bg_color2 & 0xFF)));
    $color = imagecolorallocate($image, $r, $g, $b);
    imageline($image, 0, $i, $width, $i, $color);
}

// Tambahkan noise/pattern untuk keamanan
$noise_color = imagecolorallocate($image, rand(180, 220), rand(180, 220), rand(180, 220));
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Tambahkan garis-garis untuk gangguan visual
$line_color = imagecolorallocate($image, rand(200, 230), rand(200, 230), rand(200, 230));
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Warna text (lebih gelap agar lebih jelas)
$text_color = imagecolorallocate($image, rand(20, 50), rand(20, 50), rand(20, 50));

// Font size dan posisi
$font_size = 5; // Font built-in PHP
$x = 20;
$y = 35;

// Tulis text captcha
imagestring($image, $font_size, $x, $y, $question . " = ?", $text_color);

// Terapkan efek blur ringan (dikurangi agar tidak terlalu buram)
imagefilter($image, IMG_FILTER_SMOOTH, 1);

// Tambahkan sedikit distorsi dengan menggeser beberapa pixel (dikurangi)
for ($i = 0; $i < 20; $i++) {
    $x_offset = rand(-1, 1);
    $y_offset = rand(-1, 1);
    $px = rand(0, $width);
    $py = rand(0, $height);
    if ($px + $x_offset < $width && $py + $y_offset < $height && $px + $x_offset >= 0 && $py + $y_offset >= 0) {
        $pixel_color = imagecolorat($image, $px, $py);
        imagesetpixel($image, $px + $x_offset, $py + $y_offset, $pixel_color);
    }
}

// Set header untuk output gambar
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output gambar
imagepng($image);
imagedestroy($image);
?>

