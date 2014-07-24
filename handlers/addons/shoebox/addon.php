<?php

class ShoeboxHandler extends AuthenticationRequired {
    function get() {
        echo "shoebox here";
    }
}

$addon_routes = array(
    "/api/addons/shoebox" => "ShoeboxHandler",
)
