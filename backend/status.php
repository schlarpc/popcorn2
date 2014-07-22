<?php

require_once "inc/stream.inc.php";

header("Content-type: application/json");

$status = array(
    "streaming" => is_streaming(),
    "current_video" => get_current_video(),
    "time_elapsed" => time_elapsed(),
    "duration" => get_duration(get_current_video()),
);
echo json_encode($status);
