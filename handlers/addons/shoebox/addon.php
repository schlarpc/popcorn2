<?php

require("/handlers/addons/shoebox/lib/shoebox.php");

function shoebox_sort_by_rating(&$arr) {
    usort($arr, function ($a, $b) { return intval($b['rating']) - intval($a['rating']); });
    return $arr;
}


class ShoeboxHandler extends AuthenticationRequired {
    function get() {
        $resp = array(
            "name" => "Shoebox",
            "type" => "stream",
            "search" => "/api/addons/shoebox/search",
            "videos" => "/api/addons/shoebox/videos",
        );
        json_response($resp);
    }
}

class ShoeboxSearchHandler extends AuthenticationRequired {
    function get() {
        if (!isset($_GET["q"])) {
            invalid_request();
        }
        $query = $_GET["q"];
        
        $sb = new Shoebox();
        $search_results = array();
        foreach (array("shows" => $sb->getTVList(), "movies" => $sb->getMovieList()) as $type => $list) {
            foreach ($list as $idx => $item) {
                if (stristr($item['title'], $query) !== FALSE) {    
                    $item["type"] = $type;
                    $search_results[] = $item;
                }
            }
        }
        shoebox_sort_by_rating($search_results);
        
        $resp = array("resources" => array());
        foreach ($result as array_slice($search_results, 0, 50)) {
            $resp["resources"][] = array(
                "name" => $result["title"],
                "href" => "/api/addons/shoebox/videos/" . $result["type"] . "/" . $result["id"],
            );
        }
        
        json_response($resp);
    }
}



$addon_routes = array(
    "/api/addons/shoebox"        => "ShoeboxHandler",
    "/api/addons/shoebox/search" => "ShoeboxSearchHandler",
    //"/api/addons/shoebox/videos" => "ShoeboxVideosHandler",
);
