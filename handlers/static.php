<?php

class HomeHandler {
    function get() {
        include("templates/home.html");
    }
}

class APIHandler {
    function get() {
        include("templates/api.html");
    }
}
