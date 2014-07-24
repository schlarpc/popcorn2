<?php

class StreamStatusHandler {
    function get() {
        $status = array(
            "streaming" => is_streaming(),
            "path"      => get_current_video(),
            "elapsed"   => time_elapsed(),
            "duration"  => get_duration(get_current_video()),
        );
        json_response($status);
    }
}

class StreamCommandHandler extends AuthenticationRequired  {
    function post($command) {
        if ($command === "play") {
            if (!isset($_POST['path'])) {
                invalid_request();
            }
            $resp = play_video($_POST['path'], isset($_POST['time']) ? (int) $_POST['time'] : 0);
            if ($resp === FALSE) {
                invalid_request();
            }
            no_response();
        }
        
        if ($command === "pause") {
            $resp = pause_video();
        } elseif ($command === "resume") {
            $resp = resume_video();
        } elseif ($command === "stop") {
            $resp = stop_video();
        } else {
            ToroHook::fire("404");
        }
        
        if ($resp === FALSE) {
            conflict_response();
        }
        no_response();
    }
}
