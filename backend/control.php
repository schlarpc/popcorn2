<?php

require_once "inc/common.inc.php";
require_once "inc/config.inc.php";
require_once "inc/stream.inc.php";

authenticate();
if (!check_params(["cmd"])) invalid_request();

header("Content-type: application/json");

$videos = array();
foreach ($config['video_dirs'] as $video_dir) {
    $videos = array_merge($videos, find_video_files($video_dir));
}

if ($_GET['cmd'] === 'videos') {
    echo json_encode($videos, JSON_PRETTY_PRINT);
    
} elseif ($_GET['cmd'] === 'play') {
    $time = 0;
    if (check_params(["time"])) $time = (int) $_GET["time"];
    echo json_encode(array("result" => play_video($_GET["path"], $time)));
    
} elseif ($_GET['cmd'] === 'pause') {
    echo json_encode(array("result" => pause_video()));

} elseif ($_GET['cmd'] === 'resume') {
    echo json_encode(array("result" => resume_video()));

} elseif ($_GET['cmd'] === 'stop') {
    echo json_encode(array("result" => stop_video()));
}
