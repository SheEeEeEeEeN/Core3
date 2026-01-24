<?php
// captcha.php
session_start();

// --- Configuration ---
$len   = 6;
$width = 220;
$height= 80;
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';

// Font file (siguraduhin meron kang .ttf sa /fonts folder)
$fontFile = __DIR__ . '/fonts/PTSerif-Regular.ttf';

// --- Generate random code ---
$code = '';
$max = strlen($chars) - 1;
for ($i = 0; $i < $len; $i++) $code .= $chars[rand(0, $max)];
$_SESSION['captcha_code'] = $code;

// --- Create base image ---
$im = imagecreatetruecolor($width, $height);
imageantialias($im, true);
imagesavealpha($im, true);

// plain background
$bg = imagecolorallocate($im, 245, 245, 245);
imagefilledrectangle($im, 0, 0, $width, $height, $bg);

// check if TTF available
$useTTF = function_exists('imagettftext') && file_exists($fontFile);

// --- Compute base font size ---
$size = 10;
if ($useTTF) {
    do {
        $size++;
        $bbox = imagettfbbox($size, 0, $fontFile, $code);
        $textHeight = abs($bbox[7] - $bbox[1]);
    } while ($textHeight < $height * 0.65 && $size < 150);

    // konting liit
    $size = intval($size * 0.85);
}

// --- Draw whole text para dikit-dikit ---
if ($useTTF) {
    // total bounding box para i-center
    $bbox = imagettfbbox($size, 0, $fontFile, $code);
    $textWidth  = abs($bbox[2] - $bbox[0]);
    $textHeight = abs($bbox[7] - $bbox[1]);

    // center coordinates
    $x = intval(($width - $textWidth) / 2);
    $y = intval(($height + $textHeight) / 2);

    // draw per character para may rotation pa rin
    $offsetX = $x;
    for ($i = 0; $i < $len; $i++) {
        $char = $code[$i];
        $angle = rand(-18, 18);

        $bbox = imagettfbbox($size, $angle, $fontFile, $char);
        $charWidth = abs($bbox[2] - $bbox[0]);

        $col = imagecolorallocate($im, rand(0,60), rand(0,60), rand(0,60));
        imagettftext($im, $size, $angle, $offsetX, $y, $col, $fontFile, $char);

        // sunod agad sa lapit ng char width (walang malaking spacing)
        $offsetX += $charWidth - 2;
    }
} else {
    imagestring($im, 5, 10, $height/2, $code, imagecolorallocate($im,0,0,0));
}

// konting wave distortion
$dist = imagecreatetruecolor($width, $height);
imagefill($dist, 0, 0, $bg);
for ($x1 = 0; $x1 < $width; $x1++) {
    for ($y1 = 0; $y1 < $height; $y1++) {
        $sx = intval($x1 + sin($y1 / 12.0) * 2);
        $sy = intval($y1 + cos($x1 / 18.0) * 2);
        if ($sx >= 0 && $sx < $width && $sy >= 0 && $sy < $height) {
            $color = imagecolorat($im, $sx, $sy);
            imagesetpixel($dist, $x1, $y1, $color);
        }
    }
}

// output
if (ob_get_length()) ob_end_clean();
header('Content-Type: image/png');
imagepng($dist);
imagedestroy($im);
imagedestroy($dist);
exit;
