<?php

require("lib/toro.php");
require("lib/common.php");
require("lib/config.php");
require("lib/stream.php");
require("handlers/static.php");
require("handlers/videos.php");
require("handlers/stream.php");
require("handlers/addons.php");

$addon_dir = "handlers/addons/";
if ($dh = opendir($addon_dir)) {
    while (($entry = readdir($dh)) !== false) {
        if ($entry != "." && $entry != ".." && is_dir($addon_dir . $entry)) {
            echo "$entry\n";
        }
    }
    closedir($dh);
}

ToroHook::add("404",  function() {
    http_response_code(404);
    exit;
});

Toro::serve(array(
    "/"                            => "HomeHandler",
    "/api"                         => "APIHandler",
    "/api/videos"                  => "VideosListHandler",
    "/api/videos/:alpha"           => "VideosInfoHandler",
    "/api/videos/:alpha/thumbnail" => "VideosThumbnailHandler",
    "/api/stream"                  => "StreamStatusHandler",
    "/api/stream/:alpha"           => "StreamCommandHandler",
    "/api/addons"                  => "AddonsListHandler",
));
