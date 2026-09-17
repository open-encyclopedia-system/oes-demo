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
 * Requires at least:  6.5
 * Tested up to:       7.1
 * Requires PHP:       8.1
 * Requires plugins:   oes-core
 * Tags:               oes, demo, example, encyclopedia, open-access, digital-humanities, academic, wiki, lexicon, education
 * License:            GPLv2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
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
