<?php

/**
 * Open Encyclopedia System DEMO
 *
 * @wordpress-plugin
 * Plugin Name:        OES Demo
 * Plugin URI:         https://www.open-encyclopedia-system.org/
 * Description:        Demonstration plugin implementing and extending the OES Core plugin.
 * Version:            3.0.0
 * Author:             Maren Welterlich-Strobl, Freie Universität Berlin, FUB-IT, Digitale Forschungsinfrastrukturen
 * Author URI:         https://www.fu-berlin.de/
 * Requires at least: 6.5
 * Tested up to:       7.1
 * Requires PHP:       8.1
 * License:            GPLv2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/** --------------------------------------------------------------------------------------------------------------------
 * This function will initialize the OES Demo Plugin and link it to the OES Core Plugin.
 * ---------------------------------------------------------------------------------------------------------------------
 * @throws Exception
 */
add_action('oes/plugins_loaded', function () {
    OES(__DIR__);
});
