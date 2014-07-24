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
            );
        }
        json_response($resp);
    }
}

class VideosInfoHandler extends AuthenticationRequired {
    function delete() {
        // TODO
    }

    function get($video_hash) {
        $video = slug_to_video($video_hash);
        if ($video === FALSE) {
            ToroHook::fire("404");
        }
        
        $resp = array(
            "type"        => "video",
            "name"        => path_to_friendly_name($video),
            "description" => NULL,
            "path"        => $video,
            "image"       => "/api/videos/" . $video_hash . "/thumbnail?time=120",
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
        if (!isset($_GET['time']) || (int) $_GET["time"] > get_duration($video) || (int) $_GET["time"] < 0) {
            invalid_request();
        }
        
        header("Content-type: image/jpeg");
        $thumb = get_thumbnail($_GET["path"], $_GET["time"]);

        if ($thumb !== FALSE) {
            echo $thumb;

        } else {
            http_status_code(400);
        
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

    }
}
