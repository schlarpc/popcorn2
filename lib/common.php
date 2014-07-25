<?php

class PopcornItem {
    public $name;
    public $type;
    public $href;
    public $description;
    public $images;
    
    function __construct() {
        $this->images = array();
    }
    
    function toArray() {
        $resp = array();
        foreach (get_object_vars($this) as $prop => $value) {
            if ($value !== NULL && $value !== array()) {
                $resp[$prop] = $value;
            }
        }
        return $resp;
    }
}

class PopcornDirectory extends PopcornItem {
    public $resources;
    
    function __construct() {
        $this->resources = array();
        $this->type = "directory";
        parent::__construct();
    }
}

class PopcornVideo extends PopcornItem {
    public $path;
    public $duration;
        
    function __construct() {
        $this->type = "video";
        parent::__construct();
    }
}

class PopcornAddon extends PopcornItem {
    public $category;
    public $videos;
    public $search;
    public $download;
        
    function __construct() {
        $this->type = "addon";
        parent::__construct();
    }
}


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
