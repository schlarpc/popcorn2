<?php

class HomeHandler {
    function get() {
        echo "hi";
        include("templates/home.html");
    }
}

class APIHandler {
    function get() {
        include("templates/api.html");
    }
}
