<?php

require("lib/toro.php");
require("lib/common.php");
require("lib/config.php");
require("lib/stream.php");
require("lib/shoebox.php");
require("handlers/static.php");
require("handlers/videos.php");
require("handlers/stream.php");
//require("handlers/addons.php");

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
));
