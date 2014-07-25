<?php

class AddonsListHandler extends AuthenticationRequired  {
    function get() {
        $dir = new PopcornDirectory();
        $dir->name = "Popcorn Addons";
        $dir->type = "directory";
        $dir->href = "/api/addons";
    
        $addon_dir = "handlers/addons/";
        $addon_list = array();
        if ($dh = opendir($addon_dir)) {
            while (($entry = readdir($dh)) !== false) {
                if ($entry != "." && $entry != ".." && is_dir($addon_dir . $entry)) {
                    $addon = new PopcornAddon();
                    $addon->name = $entry;
                    $addon->type = "addon";
                    $addon->href = "/api/addons/" . $entry;
                    $dir->resources[] = $addon->toArray();
                }
            }
            closedir($dh);
        }
        
        json_response($dir->toArray());
    }
}
