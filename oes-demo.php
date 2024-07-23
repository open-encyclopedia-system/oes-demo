<?php

/**
 * Plugin Name: OES Demo
 * Plugin URI: http://www.open-encyclopedia-system.org/
 * Description: Plugin to implement the OES Core Plugin.
 * Version: 2.3.0
 * Author: Maren Welterlich-Strobl, Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 * Author URI: https://www.cedis.fu-berlin.de/cedis/mitarbeiter/beschaeftigte/mstrobl.html
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly

/** --------------------------------------------------------------------------------------------------------------------
 * This function will initialize the OES Demo Plugin and link it to the OES Core Plugin.
 * ---------------------------------------------------------------------------------------------------------------------
 * @throws Exception
 */
add_action('oes/plugins_loaded', function () {

    /* check if OES Core Plugin is activated */
    if (!function_exists('OES')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning is-dismissible"><p>' .
                __('The OES Core Plugin is not active.', 'oes-demo') . '</p></div>';
        });
    } else {


        /** Initialize OES ---------------------------------------------------------------------------------------------
         * This will initialize the OES Core Plugin functionalities and returns the global OES variable. -------------*/
        $oes = OES(__DIR__);

        /* exit early if OES Plugin was not completely initialized */
        if (!$oes->initialized) return;


        /** Prepare the project ----------------------------------------------------------------------------------------
         * This will initialize the project by building the data model and the admin configurations. -----------------*/

        /* add language options to page */
        oes_add_fields_to_page();


        /** Include theme classes --------------------------------------------------------------------------------------
         * Include classes that prepare the objects inside this encyclopaedia for the frontend display. This classes
         * will be included for any theme and will be executed if the theme calls 'the_content()'. -------------------*/
        oes_include_project('theme/post-types/class-demo_post.php');
        oes_include_project('theme/post-types/class-demo_article.php');
        oes_include_project('theme/post-types/class-demo_contributor.php');
        oes_include_project('theme/post-types/class-demo_glossary_entry.php');
        oes_include_project('theme/post-types/class-demo_person.php');
        oes_include_project('theme/post-types/class-demo_institution.php');
        oes_include_project('theme/post-types/class-demo_place.php');
        oes_include_project('theme/post-types/class-demo_event.php');
        oes_include_project('theme/taxonomies/class-demo_taxonomy.php');
        oes_include_project('theme/taxonomies/class-t_demo_subject.php');


        /** Hide the WordPress update notifications and obsolete menu structure --------------------------------------*/
        oes_hide_obsolete_menu_structure();


        /** Modify timeline ------------------------------------------------------------------------------------------*/
        oes_include_project('theme/timeline.php');
        add_filter('oes_timeline/start_date', 'oes_demo__timeline_date', 10, 2);
        add_filter('oes_timeline/end_date', 'oes_demo__timeline_date', 10, 2);


        /* Initialize the project ------------------------------------------------------------------------------------*/
        try {
            $oes->initialize_project();
        } catch (Exception $e) {
            add_action('admin_notices', function () use ($e) {
                echo '<div class="notice notice-warning is-dismissible"><p>' .
                    __('The OES Core Plugin could not be initialized.', 'oes-demo') . '</p>' .
                    $e->getMessage() .
                    '</div>';
            });
        }
    }
});