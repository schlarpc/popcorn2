<?php

class VideosListHandler extends AuthenticationRequired {
    function get() {
        $videos = array();
        foreach (Config::get('video_dirs') as $video_dir) {
            $videos = array_merge($videos, find_video_files($video_dir));
        }
        
        $resp = array("resources" => array());
        foreach ($videos as $video) {
            $resp["resources"][] = array(
                "name" => path_to_friendly_name($video),
                "href" => "/api/videos/" . slug_hash($video),
                "type" => "video",
            );
        }
        json_response($resp);
    }
}

class VideosInfoHandler extends AuthenticationRequired {
    function delete() {
        $video = slug_to_video($video_hash);
        if ($video === FALSE) {
            ToroHook::fire("404");
        }
        
        unlink($video);
    }

    function get($video_hash) {
        $video = slug_to_video($video_hash);
        if ($video === FALSE) {
            ToroHook::fire("404");
        }
        
        $resp = array(
            "name"        => path_to_friendly_name($video),
            "description" => NULL,
            "path"        => $video,
            "image"       => "/api/videos/" . $video_hash . "/thumbnail?time=" . (int) (get_duration($video) * .1),
            "type"        => "video",
            "duration"    => get_duration($video),
        );
        json_response($resp);
    }
}

class VideosThumbnailHandler {
    function get($video_hash) {
        $video = slug_to_video($video_hash);
        if ($video === FALSE) {
            ToroHook::fire("404");
        }
        
        header("Content-type: image/jpeg");
        
        $time = 0;
        if (isset($_GET['time'])) {
            $time = (int) $_GET['time'];
        }
        
        if ($time >= 0 && $time <= get_duration($video)) {
            $thumb = get_thumbnail($video, (int) $_GET["time"]);
            if ($thumb !== FALSE) {
                echo $thumb;
                return;
            }
        }
        
        http_response_code(400);
        
        $width = Config::get('thumbnail_width');
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
}
