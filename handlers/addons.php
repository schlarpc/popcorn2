<?php

class AddonsListHandler extends AuthenticationRequired  {
    function get() {
        $addon_dir = "handlers/addons/";
        $addon_list = array();
        if ($dh = opendir($addon_dir)) {
            while (($entry = readdir($dh)) !== false) {
                if ($entry != "." && $entry != ".." && is_dir($addon_dir . $entry)) {
                    $addon_list[] = "/api/addons/" . $entry;
                }
            }
            closedir($dh);
        }
        
        json_response(array("addons" => $addon_list));
    }
}
