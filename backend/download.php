<?php

require_once "inc/common.inc.php";

authenticate();
if (!check_params(["cmd"])) invalid_request();

function youtube_dl($url) {
    $clean_url = escapeshellarg($url);
    exec("youtube-dl --restrict-filenames -o /tmp/popcorn/videos/%\(title\)s-%\(id\)s.%\(ext\)s -f best $clean_url > /tmp/popcorn/ytdl 2>&1 &");
}


if ($_GET["cmd"] === "youtube" && isset($_GET["url"])) {
    echo youtube_dl($_GET["url"]);
}
