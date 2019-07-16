<?php

/**
 * Variables that are not allowed to use in html templates
 */
$CRITICAL_VAR = array(
    '$DATABASE_SERVER',
    '$DATABASE_PORT',
    '$DATABASE_USER',
    '$DATABASE_NAME',
    '$DATABASE_PASSWORD',
);
$LASTERROR = 0;

$IS_BANNED = false;
$redirect_to = "";
$redirect_delay = 0;


$current_user = array(
    'uid' => 0,
    'permissions' => array(),
    'name' => '',
    'color' => '',
    'is_founder' => false
);


function get_relative_root_directory()
{
    $path_components = explode("/", $_SERVER["SCRIPT_NAME"]);
    $current_directory = $path_components[count($path_components) - 2];
    if ($current_directory == "site" || $current_directory == "lib") {
        $root_dir = "..";
    } else {
        $root_dir = ".";
    }
    return $root_dir;
}

$root_dir = get_relative_root_directory();

$library_path = $root_dir . "/lib";
$template_directory = $root_dir . "/theme/" . $site_settings['template'];


$form_path = array('FORMS' => $template_directory . "/forms");

$GLOBALS_DEFINED = true;