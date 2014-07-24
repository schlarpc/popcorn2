<?php

class AuthenticationRequired {
    function __construct() {
        ToroHook::add("before_handler", "authenticate");
    }
}

function authenticate() {
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        if ($_SERVER['PHP_AUTH_USER'] === Config::get('admin_user') && $_SERVER['PHP_AUTH_PW'] === Config::get('admin_pass')) {
            return TRUE;
        }
    }
    header('WWW-Authenticate: Basic realm="Popcorn Admin"', true, 401);
    die();
}


function json_response($arr) {
    header('Content-type: application/json');
    echo json_encode($arr);
    die();
}

function invalid_request() {
    http_response_code(400);
    die();
}

function no_response() {
    http_response_code(204);
    die();
}

function conflict_response() {
    http_response_code(409);
    die();
}

function popcorn_temp() {
    return sys_get_temp_dir() . "/popcorn";
}

function create_data() {
    $temp = popcorn_temp();
    @mkdir($temp);
    @mkdir($temp . "/thumbs");
    @mkdir($temp . "/videos");
    touch($temp . "/current_video");
    touch($temp . "/duration_cache");
    touch($temp . "/last_pause");
}

create_data();
