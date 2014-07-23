<?php

require_once "inc/config.inc.php";
require_once "inc/common.inc.php";
require_once "inc/stream.inc.php";

if (!check_params(["path", "time"])) invalid_request();

header("Content-type: image/jpeg");
$thumb = get_thumbnail($_GET["path"], $_GET["time"]);
if ($thumb !== FALSE) {
    return $thumb;
} else {
    $width = $config['thumbnail_width'];
    $height = $width * 3 / 4;
    $fg = imagecolorallocate($im, 0, 0, 0);
    $im = imagecreate($width, $height);
    imagestring($im, 4, 0, $height / 2, 'Thumbnail error', $fg);
    imagejpeg($im);
    imagedestroy($im);
}
