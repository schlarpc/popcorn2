<?php

require("handlers/addons/shoebox/lib/shoebox.php");

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
        usort($search_results, function ($a, $b) { return intval($b['rating']) - intval($a['rating']); });
        
        $resp = array("resources" => array());
        foreach (array_slice($search_results, 0, 50) as $result) {
            $resp["resources"][] = array(
                "name" => $result["title"],
                "href" => "/api/addons/shoebox/videos/" . $result["type"] . "/" . $result["id"],
                "type" => $result["type"] === "movies" ? "video" : "directory",
            );
        }
        json_response($resp);
    }
}

class ShoeboxVideosHandler extends AuthenticationRequired {
    function get() {
        $resp = array("resources" => array(
            array(
                "name" => "Movies",
                "href" => "/api/addons/shoebox/videos/movies",
                "type" => "directory",
            ),
            array(
                "name" => "TV Shows",
                "href" => "/api/addons/shoebox/videos/shows",
                "type" => "directory",
            ),
        ));
        json_response($resp);
    }
}

class ShoeboxMoviesListHandler extends AuthenticationRequired {
    function get() {
        $sb = new Shoebox();
        $resp = array("resources" => array());
        
        foreach(array_slice($sb->getMovieList(), 0, 50) as $idx => $item) {
            $resp["resources"][] = array(
                "name" => $item["title"],
                "href" => "/api/addons/shoebox/videos/movies/" . $item["id"],
                "type" => "video",
            );
        }
        json_response($resp);
    }
}

class ShoeboxShowsListHandler extends AuthenticationRequired {
    function get() {
        $sb = new Shoebox();
        $resp = array("resources" => array());
        
        foreach(array_slice($sb->getTVList(), 0, 50) as $idx => $item) {
            $resp["resources"][] = array(
                "name" => $item["title"],
                "href" => "/api/addons/shoebox/videos/shows/" . $item["id"],
                "type" => "directory",
            );
        }
        json_response($resp);
    }
}

class ShoeboxMoviesItemHandler extends AuthenticationRequired {
    function get($id) {
        $sb = new Shoebox();
        $data = $sb->getMovieData($id, TRUE);
        $resp = array(
            "name"        => $data["title"],
            "description" => $data["description"],
            "path"        => $data["langs"][0]["stream"],
            "image"       => $data["poster"],
            "type"        => "video",
            "duration"    => get_duration($data["langs"][0]["stream"]),
        );
        json_response($resp);
    }
}

class ShoeboxShowsItemHandler extends AuthenticationRequired {
    function get($id) {
        $sb = new Shoebox();
        $resp = array("resources" => array());
        
        $data = $sb->getTVData($id);
        foreach ($data["season_info"] as $idx => $item) {
            $resp["resources"][] = array(
                "name" => "Season " . $idx,
                "href" => "/api/addons/shoebox/videos/shows/" . $id . "/" . $idx,
                "type" => "directory",
            );
        }
        json_response($resp);
    }
}


class ShoeboxShowsSeasonHandler extends AuthenticationRequired {
    function get($id, $season) {
        $sb = new Shoebox();
        $resp = array("resources" => array());
        
        $data = $sb->getTVData($id);
        foreach ($data["season_info"][$season]["titles"] as $idx => $item) {
            $resp["resources"][] = array(
                "name" => "Episode " . $idx . ($item === "" ? "" : " ($item)"),
                "href" => "/api/addons/shoebox/videos/shows/" . $id . "/" . $season . "/" . $idx,
                "type" => "video",
            );
        }
        json_response($resp);
    }
}

class ShoeboxShowsEpisodeHandler extends AuthenticationRequired {
    function get($id, $season, $episode) {
        $sb = new Shoebox();
        $data = $sb->getEpisodeData($id, $season, $episode, TRUE);
        $title = $data["title"] . "- Season $season, Episode $episode";
        if ($data["episode_title"] !== "") {
            $title .= " ({$data["episode_title"]})";
        }
        $resp = array(
            "name"        => $title,
            "description" => $data["description"],
            "path"        => $data["langs"][0]["stream"],
            "image"       => $data["thumb"],
            "type"        => "video",
            "duration"    => get_duration($data["langs"][0]["stream"]),
        );
        json_response($resp);
    }
}

$addon_routes = array(
    "/api/addons/shoebox"                                      => "ShoeboxHandler",
    "/api/addons/shoebox/search"                               => "ShoeboxSearchHandler",
    "/api/addons/shoebox/videos"                               => "ShoeboxVideosHandler",
    "/api/addons/shoebox/videos/movies"                        => "ShoeboxMoviesListHandler",
    "/api/addons/shoebox/videos/movies/:number"                => "ShoeboxMoviesItemHandler",
    "/api/addons/shoebox/videos/shows"                         => "ShoeboxShowsListHandler",
    "/api/addons/shoebox/videos/shows/:number"                 => "ShoeboxShowsItemHandler",
    "/api/addons/shoebox/videos/shows/:number/:number"         => "ShoeboxShowsSeasonHandler",
    "/api/addons/shoebox/videos/shows/:number/:number/:number" => "ShoeboxShowsEpisodeHandler",
);
