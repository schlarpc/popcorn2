<?php

require_once "inc/common.inc.php";
require_once "inc/stream.inc.php";

if (!check_params(["path", "time"])) invalid_request();

header("Content-type: image/jpeg");
echo get_thumbnail($_GET["path"], $_GET["time"]);
