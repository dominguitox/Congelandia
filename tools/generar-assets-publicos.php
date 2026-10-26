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
$logo = loadImage($pub . '/images/brand/logo-original.png');
if ($logo) {
    // Logo completo (max. 560 px de ancho)
    $full = imagescale($logo, 560);
    saveWebp($full, $pub . '/images/brand/logo-completo.webp', 90);
    imagedestroy($full);

    // Marca: recorte de la cabeza del pingueino (sin wordmark)
    $mark = imagecrop($logo, ['x' => 120, 'y' => 20, 'width' => 330, 'height' => 330]);
    if ($mark) {
        $markSmall = imagescale($mark, 280, 280);
        saveWebp($markSmall, $pub . '/images/brand/logo-marca.webp', 90);
        imagedestroy($markSmall);

        // Favicon 64 px a partir de la marca
        $fav = imagescale($mark, 64, 64);
        imagepng($fav, $pub . '/favicon.png');
        imagedestroy($fav);
        imagedestroy($mark);
    }

    imagedestroy($logo);
    echo "Logo OK\n";
}

// ---------------------------------------------------------
// 2. Hero: detalle de empanadas (sin logo Lider ni codigo)
// ---------------------------------------------------------
$empanadas = loadImage($pub . '/images/productos/1791667600_empanadas.jpg');
if ($empanadas) {
    $crop = imagecrop($empanadas, ['x' => 700, 'y' => 1080, 'width' => 430, 'height' => 560]);
    if ($crop) {
        $hero = coverCrop($crop, 860, 1120);
        saveWebp($hero, $pub . '/images/hero/empanadas.webp', 86);
        imagedestroy($hero);
        imagedestroy($crop);
    }
    imagedestroy($empanadas);
    echo "Hero OK\n";
}

// ---------------------------------------------------------
// 2b. Hero principal: tomates en 4:5
// ---------------------------------------------------------
$tomates = loadImage($pub . '/images/productos/1792982817_tomates_pera_en_bowl_de_madera_19_11zon-974297909.jpg');
if ($tomates) {
    $hero = coverCrop($tomates, 1000, 1250);
    saveWebp($hero, $pub . '/images/hero/tomates.webp', 86);
    imagedestroy($hero);
    imagedestroy($tomates);
    echo "Hero tomates OK\n";
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
