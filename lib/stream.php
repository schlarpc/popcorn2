<?php

/**
 * returns true if given path is a supported video file within the allowed paths
 */
function is_video_file($path) {
    // handle network paths
    // TODO: more validation? is there any benefit?
    if (strstr($path, "http://") !== FALSE || strstr($path, "https://") !== FALSE) {
        return TRUE;
    }
    // handle filesystem paths
    $path = realpath($path);
    if (!is_file($path)) {
        return FALSE;
    } elseif (!in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), Config::get('valid_exts'))) {
        return FALSE;
    } else {
        $dirname = pathinfo($path, PATHINFO_DIRNAME);
        foreach (Config::get('video_dirs') as $video_dir) {
            if (strpos($dirname, realpath($video_dir)) === 0) {
                return TRUE;
            }
        }
        return FALSE;
    }
}


/**
 * returns an array of video files in the directory specified (recursive)
 */
function find_video_files($dir) {
    $entries = array();
    if ($handle = opendir($dir)) {
        while (FALSE !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $full_path = $dir . '/' . $entry;
                if (is_dir($full_path)) {
                    $entries = array_merge($entries, find_video_files($full_path));
                } elseif (is_video_file($full_path)) {
                    $entries[] = realpath($full_path);
                }
            }
        }
        closedir($handle);
    }
    return $entries;
}


/**
 * returns true if a video stream is currently running
 */
function is_streaming() {
    return exec("ps -u $(whoami) | grep ffmpeg") !== "";
}


/**
 * gets total duration of a video; FALSE on failure
 */
function get_duration($path, $use_cache=TRUE) {
    if (is_video_file($path)) {
        // we maintain a cache mainly for http usage
        // ffprobe takes ~10 seconds for a vk video which is mostly unusable
        $duration_cache = json_decode(file_get_contents(popcorn_temp() . "/duration_cache"), TRUE);
        if ($duration_cache === NULL) {
            $duration_cache = array();
        }
    
        if (array_key_exists($path, $duration_cache) && $use_cache) {
            return $duration_cache[$path];
        } else {
            $clean_path = escapeshellarg($path);
            $result = exec("ffprobe -i $clean_path -show_format -v quiet | sed -n 's/duration=//p'");
            $duration_cache[$path] = (int) $result;
            file_put_contents(popcorn_temp() . "/duration_cache", json_encode($duration_cache));
            return $duration_cache[$path];
        }
    }
    return FALSE;
}


/**
 * returns path of current video; FALSE on failure
 */
function get_current_video() {
    $path = file_get_contents(popcorn_temp() . "/current_video");
    if (is_video_file($path)) {
        return $path;
    }
    return FALSE;
}


/**
 * returns seconds elapsed of current video; FALSE on failure
 */
function time_elapsed() {
    $log = @file_get_contents(popcorn_temp() . "/ffmpeg_log");
    $last_pause = (int) file_get_contents(popcorn_temp() . "/last_pause");
    
    $time_pos = strrpos($log, "time=");
    if (get_current_video() !== FALSE && $time_pos !== FALSE) {
        $time_code = substr($log, $time_pos + 5, 8);
        $hours   = (int) substr($time_code, 0, 2);
        $minutes = (int) substr($time_code, 3, 2);
        $seconds = (int) substr($time_code, 6, 2);
        $total_seconds = ((($hours * 60) + $minutes) * 60) + $seconds;
        return $total_seconds + $last_pause;
    }
    return FALSE;
}


/**
 * starts a video stream; TRUE on success; FALSE on failure
 */
function play_video($path, $start = 0) {
    if (is_video_file($path)) {
        stop_video();
        
        if ($start < 0 || $start > get_duration($path)) {
            return FALSE;
        }
    
        $clean_path = escapeshellarg($path);
        $video_bitrate = (int) Config::get('video_bitrate');
        $audio_bitrate = (int) Config::get('audio_bitrate');
        $fps = (int) Config::get('fps');
        
        if (Config::get('keyframe_interval') === "auto") {
            $keyframe = ceil(2000 / ($video_bitrate + $audio_bitrate) * $fps);
        } else {
            $keyframe = (int) Config::get('keyframe_interval');
        }
        $stream_url = "http://" . Config::get('stream_host') . ":" . Config::get('stream_port')
            . "/publish/" . Config::get('stream_name') . "?password=" . Config::get('stream_password');
            
        file_put_contents(popcorn_temp() . "/current_video", $path);
        file_put_contents(popcorn_temp() . "/last_pause", $start);
        exec("unbuffer ffmpeg -re -ss $start -i $clean_path "
            . "-c:v libvpx -b:v {$video_bitrate}k -r $fps -g $keyframe "
            . "-cpu-used 4 -deadline realtime -threads 2 "
            . "-c:a libvorbis -b:a {$audio_bitrate}k -ac 2 -ar 44100 "
            . "-f webm $stream_url "
            . "> " . popcorn_temp() . "/ffmpeg_log 2>&1 &");
        return TRUE;
    }
    return FALSE;
}


function get_thumbnail($path, $time = 0) {
    if (is_video_file($path)) {
        $time = (int) $time;
        if ($time < 0 || $time > get_duration($path)) {
            return FALSE;
        }
    
        $width = Config::get('thumbnail_width');
        $fname = tempnam(popcorn_temp() . "/thumbs", "thumb");
        $clean_fname = escapeshellarg($fname);
        $clean_path = escapeshellarg($path);
        exec("ffmpeg -ss $time -i $clean_path "
            . "-vf \"scale={$width}:-1\" -vframes 1 "
            . "-y -f image2 $clean_fname"
            . "> /dev/null 2>&1");            
        $thumb = file_get_contents($fname);
        unlink($fname);
        return $thumb;
    }
    return FALSE;
}


/**
 * stops the current stream; TRUE on success; FALSE on failure
 */
function stop_video() {
    if (is_streaming()) {
        exec("killall ffmpeg");
        file_put_contents(popcorn_temp() . "/last_pause", "");
        file_put_contents(popcorn_temp() . "/current_video", "");
        unlink(popcorn_temp() . "/ffmpeg_log");
        return TRUE;
    }
    return FALSE;
}


/**
 * pauses the current stream; TRUE on success; FALSE on failure
 */
function pause_video() {
    if (is_streaming()) {
        exec("killall ffmpeg");
        return TRUE;
    }
    return FALSE;
}


/**
 * resumes the current stream after pause; TRUE on success; FALSE on failure
 */
function resume_video() {
    $path = get_current_video();
    if ($path && !is_streaming()) {
        play_video($path, max(0, time_elapsed() - 5));
        return TRUE;
    }
    return FALSE;
}


function path_to_friendly_name($path) {
    return pathinfo($path, PATHINFO_FILENAME);
}


function slug_hash($data) {
    return hash('sha1', $data);
}


function slug_to_video($slug) {
    foreach (Config::get('video_dirs') as $video_dir) {
        foreach (find_video_files($video_dir) as $video) {
            if (slug_hash($video) === $slug) {
                return $video;
            }
            $hashes[slug_hash($video)] = $video;
        }
    }
    return FALSE;
}
