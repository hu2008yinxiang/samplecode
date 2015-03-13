<?php

function pic_normalize($path)
{
    $quality = 75;
    @trigger_error('--');
    $simg = imagecreatefromstring(file_get_contents($path));
    $e = error_get_last();
    if (! is_resource($simg) || $e['message'] != '--') {
        echo '[E]', $path, PHP_EOL;
        return;
    }
    $width = imagesx($simg);
    $height = imagesy($simg);
    $size = 155;
    if (($width == $height && $width < $size) || ($width == $size && $height == $size)) {
        imagedestroy($simg);
        return;
    }
    $dimg = imagecreatetruecolor($size, $size);
    imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $size, $size, $width, $height);
    imagedestroy($simg);
    imagejpeg($dimg, $path, $quality);
    imagedestroy($dimg);
}