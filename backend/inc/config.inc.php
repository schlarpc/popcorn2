<?php

$config = array(
    // popcorn options
    "admin_user"        => "popcorn",
    "admin_pass"        => "buttery",
    "valid_exts"        => array("avc", "avi", "flv", "h264", "m2v", "m4v", "mkv", "mov", "mp4", "mpeg", "mpg", "ogm", "ogv", "ts", "vob", "webm", "wmv"),
    "video_dirs"        => array("/home/rtorrent/torrents", "/tmp/popcorn/videos"),
    "thumbnail_width"   => 320,
    // ffmpeg options
    "video_bitrate"     => 1500,
    "audio_bitrate"     => 80,
    "fps"               => 24,
    "keyframe_interval" => "auto",
    // stream.m options
    "stream_host"       => "127.0.0.1",
    "stream_port"       => "8001",
    "stream_name"       => "popcorn",
    "stream_password"   => "buttery",
);
