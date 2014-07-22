<?php

require_once "inc/common.inc.php";
require_once "inc/shoebox.inc.php";

authenticate();
if (!check_params(["cmd"])) invalid_request();

header("Content-type: application/json");

function sort_by_rating(&$arr) {
    usort($arr, function ($a, $b) { return intval($b['rating']) - intval($a['rating']); });
    return $arr;
}

function shoebox_search($query) {
    global $sb;
    
    foreach (array("tv" => $sb->getTVList(), "movie" => $sb->getMovieList()) as $type => $list) {
        foreach ($list as $idx => $item) {
            if (stristr($item['title'], $query) !== FALSE) {
                $item["type"] = $type;
                $results[] = $item;
            }
        }
    }
    sort_by_rating($results);
    return $results;
}


$sb = new Shoebox();

if ($_GET["cmd"] === "search") {
    if (!check_params(["q"])) invalid_request();
    echo json_encode(shoebox_search($_GET["q"]));

} elseif ($_GET["cmd"] === "movie") {
    if (!check_params(["mid"])) invalid_request();
    echo json_encode($sb->getMovieData((int) $_GET["mid"], true));

} elseif ($_GET["cmd"] == "tv") {
    if (!check_params(["sid"])) invalid_request();
    echo json_encode($sb->getTVData((int) $_GET["sid"]));
    
} elseif ($_GET["cmd"] == "episode") {
    if (!check_params(["sid", "season", "episode"])) invalid_request();
    echo json_encode($sb->getEpisodeData((int) $_GET["sid"], (int) $_GET["season"], (int) $_GET["episode"], true));
    
}
