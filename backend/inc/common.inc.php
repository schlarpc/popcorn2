<?php

require_once "config.inc.php";

function authenticate() {
    global $config;
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        if ($_SERVER['PHP_AUTH_USER'] === $config['admin_user'] && $_SERVER['PHP_AUTH_PW'] === $config['admin_pass']) {
            return TRUE;
        }
    }
    header('WWW-Authenticate: Basic realm="Popcorn Admin"', true, 401);
    die();
}

function invalid_request() {
    http_response_code(400);
    die();
}

function check_params($params) {
    foreach ($params as $param) {
        if (!isset($_GET[$param])) {
            return FALSE;
        }
    }
    return TRUE;
}

function create_data() {
    @mkdir("/tmp/popcorn");
    @mkdir("/tmp/popcorn/thumbs");
    @mkdir("/tmp/popcorn/videos");
    touch("/tmp/popcorn/current_video");
    touch("/tmp/popcorn/duration_cache");
}

create_data();
