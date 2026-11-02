<?php
// Genera derivados optimizados del logo y las fotos de producto.
// Uso: php tools/generar-assets-publicos.php

$root = dirname(__DIR__);
$pub  = $root . '/public';

function loadImage(string $path)
{
    $info = @getimagesize($path);
    if (!$info) {
        return null;
    }
    return match ($info[2]) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($path),
        IMAGETYPE_PNG  => imagecreatefrompng($path),
        IMAGETYPE_WEBP => imagecreatefromwebp($path),
        default        => null,
    };
}

/**
 * Recorta cubriendo el destino (object-fit: cover) y redimensiona.
 */
function coverCrop($src, int $tw, int $th)
{
    $sw = imagesx($src);
    $sh = imagesy($src);
    $scale = max($tw / $sw, $th / $sh);
    $nw = (int) ceil($sw * $scale);
    $nh = (int) ceil($sh * $scale);

    $tmp = imagecreatetruecolor($nw, $nh);
    imagealphablending($tmp, false);
    imagesavealpha($tmp, true);
    imagefill($tmp, 0, 0, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $nw, $nh, $sw, $sh);

    $dst = imagecreatetruecolor($tw, $th);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopy($dst, $tmp, 0, 0, (int) (($nw - $tw) / 2), (int) (($nh - $th) / 2), $tw, $th);

    imagedestroy($tmp);
    return $dst;
}

function saveWebp($img, string $path, int $quality = 84): void
{
    @mkdir(dirname($path), 0777, true);
    imagewebp($img, $path, $quality);
}

// ---------------------------------------------------------
// 1. Logo: version completa y marca recortada para navbar
// ---------------------------------------------------------
$logo = loadImage($pub . '/logo.png');
if ($logo) {
    $w = imagesx($logo);
    $h = imagesy($logo);

    // Logo completo (máx. 560 px de ancho)
    $full = imagescale($logo, 560);
    saveWebp($full, $pub . '/images/brand/logo-completo.webp', 88);
    imagedestroy($full);

    // Marca: recorte cuadrado de la parte del pingüino (sin wordmark)
    $markSide = (int) round($w * 0.80);
    $markX = (int) round(($w - $markSide) / 2);
    $markY = 0;
    $mark = imagecreatetruecolor($markSide, $markSide);
    imagealphablending($mark, false);
    imagesavealpha($mark, true);
    imagefill($mark, 0, 0, imagecolorallocatealpha($mark, 0, 0, 0, 127));
    imagecopy($mark, $logo, 0, 0, $markX, $markY, $markSide, $markSide);

    $markSmall = imagescale($mark, 240, 240);
    saveWebp($markSmall, $pub . '/images/brand/logo-marca.webp', 88);
    imagedestroy($markSmall);
    imagedestroy($mark);

    // Favicon 64 px a partir de la marca
    $fav = imagescale($mark, 64, 64);
    imagepng($fav, $pub . '/favicon.png');
    imagedestroy($fav);

    imagedestroy($logo);
    echo "Logo OK\n";
}

// ---------------------------------------------------------
// 2. Hero: recorte de las empanadas (sin logo Lider ni codigo)
// ---------------------------------------------------------
$empanadas = loadImage($pub . '/images/productos/1791667600_empanadas.jpg');
if ($empanadas) {
    $crop = imagecrop($empanadas, ['x' => 620, 'y' => 880, 'width' => 860, 'height' => 860]);
    if ($crop) {
        $hero = coverCrop($crop, 1200, 1050);
        saveWebp($hero, $pub . '/images/hero/empanadas.webp', 86);
        imagedestroy($hero);
        imagedestroy($crop);
    }
    imagedestroy($empanadas);
    echo "Hero OK\n";
}

// ---------------------------------------------------------
// 3. Productos: versiones 4:3 optimizadas en images/productos/opt
// ---------------------------------------------------------
$origen = $pub . '/images/productos';
$destino = $origen . '/opt';
@mkdir($destino, 0777, true);

foreach (glob($origen . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE) ?: [] as $file) {
    $img = loadImage($file);
    if (!$img) {
        continue;
    }
    $name = pathinfo($file, PATHINFO_FILENAME);
    $out = coverCrop($img, 900, 675);
    saveWebp($out, $destino . '/' . $name . '.webp', 82);
    imagedestroy($out);
    imagedestroy($img);
    echo "Opt: $name\n";
}
