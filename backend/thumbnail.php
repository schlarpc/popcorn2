<?php

require_once "inc/config.inc.php";
require_once "inc/common.inc.php";
require_once "inc/stream.inc.php";

if (!check_params(["path", "time"])) invalid_request();

header("Content-type: image/jpeg");
$thumb = get_thumbnail($_GET["path"], $_GET["time"]);

if ($thumb !== FALSE) {
    echo $thumb;

} else {
    $width = $config['thumbnail_width'];
    $height = $width * 9 / 16;
    
    $font_size = 5;
    $error = 'Thumbnail error';
    
    $x = ($width / 2) - (imagefontwidth($font_size) * strlen($error) / 2);
    $y = ($height / 2) - (imagefontheight($font_size) / 2);
    
    $im = imagecreatetruecolor($width, $height);
    $fg = imagecolorallocate($im, 255, 255, 255);
    $bg = imagecolorallocate($im, 0, 0, 0);

    imagefill($im, 0, 0, $bg);
    imagestring($im, 5, $x, $y, $error, $fg);
    imagejpeg($im);
    imagedestroy($im);
}
