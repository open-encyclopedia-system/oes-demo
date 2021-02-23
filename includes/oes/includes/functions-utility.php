<?php

/**
 * Functions for processing inside the plugin.
 */


/**
 * Include a file relative to plugin path.
 *
 * @param string $file A string containing the file name including the relative path to $root.
 * @param string $root Optional string containing the absolute path inside the plugin to the file. Default is OES_PATH.
 */
function oes_include($file, $root = OES_PATH)
{
    $path = oes_get_path($file, $root);
    if (file_exists($path)) include_once($path);
}


/**
 * Return the path to a file within the plugin.
 *
 * @param string $path A string containing the relative path.
 * @param string $root Optional string containing the absolute path inside the plugin. Default is OES_PATH.
 * @return string Returns the path within the plugin.
 */
function oes_get_path($path = '', $root = OES_PATH)
{
    return $root . $path;
}


/**
 * Validate file. Return true if file exists and is readable, return error message if not.
 *
 * @param string $file A string containing the file.
 * @return mixed Returns true or error message.
 */
function oes_validate_file($file)
{
    if (!file_exists($file)) return 'File not found ' . $file . '.';
    else if (!is_readable($file)) return'File not readable ' . $file . '.';

    return true;
}


/**
 * Get OES plugin version
 *
 * @return mixed Returns OES plugin version or null.
 */
function oes_get_version()
{
    return OES()->get_version();
}


/**
 * Include a file containing views for the admin layer.
 *
 * @param string $path Optional string containing the path relative to '/includes/admin/views/' to the view file.
 * @param array $args Optional array containing further arguments to be extracted from file.
 * @param string $root Optional string containing the absolute root path to '/includes/admin/views/'. Default is OES_PATH
 */
function oes_get_view($path = '', $args = [], $root = OES_PATH)
{
    /* allow view file name shortcut */
    if (substr($path, -4) !== '.php') {
        $path = oes_get_path("/includes/admin/views/{$path}.php", $root);
    }

    /* include file if existing */
    if (file_exists($path)) {
        extract($args);
        include($path);
    }
}


/**
 * Get the super global GET variable or return null.
 *
 * @param string $key Optional string containing the key of the super global variable.
 * @param string $default Optional string containing the return value if super global variable does not exist.
 * @return mixed|null Returns the super global variable or null.
 */
function oes_isset_GET($key = '', $default = null)
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}


/**
 * Get the super global POST variable or return null.
 *
 * @param string $key Optional string containing the key of the super global variable.
 * @param string $default Optional string containing the return value if super global variable does not exist.
 * @return mixed|null Returns the super global variable or null.
 */
function oes_isset_POST($key = '', $default = null)
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}


/**
 * Get all plugin options. Plugin options start with 'oes_' as key.
 *
 * @return array Return array with options.
 */
function oes_get_all_oes_options(){

    $allOptions = [];

    /* loop through all options */
    foreach(wp_load_alloptions() as $key => $option){

        /* skip if option does not start with 'oes_' */
        if(!oes_starts_with($key, 'oes_')) continue;
        $allOptions[$key] = $option;
    }

    return $allOptions;
}