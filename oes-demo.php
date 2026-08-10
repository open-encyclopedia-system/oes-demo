<?php

/**
 * Open Encyclopedia System DEMO
 *
 * @wordpress-plugin
 * Plugin Name:        OES Demo
 * Plugin URI:         https://www.open-encyclopedia-system.org/
 * Description:        Demonstration plugin implementing and extending the OES Core plugin.
 * Version:            2.3.1
 * Author:             Maren Welterlich-Strobl, Freie Universität Berlin, FUB-IT
 * Author URI:         https://www.it.fu-berlin.de/die-fub-it/mitarbeitende/mstrobl.html
 * Requires at least:  6.5
 * Tested up to:       6.8.2
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

    /** Initialize OES ---------------------------------------------------------------------------------------------
     * This will initialize the OES Core Plugin functionalities and returns the global OES variable. -------------*/
    OES(__DIR__);

    /** Include theme classes --------------------------------------------------------------------------------------
     * Include classes that prepare the objects inside this encyclopaedia for the frontend display. This classes
     * will be included for any theme and will be executed if the theme calls 'the_content()'. -------------------*/
    include_once __DIR__ . '/includes/theme/post-types/class-demo_post.php';
    include_once __DIR__ . '/includes/theme/post-types/class-demo_article.php';
    include_once __DIR__ . '/includes/theme/post-types/class-demo_contributor.php';
    include_once __DIR__ . '/includes/theme/post-types/class-demo_glossary_entry.php';
    include_once __DIR__ . '/includes/theme/post-types/class-demo_person.php';
    include_once __DIR__ . '/includes/theme/post-types/class-demo_institution.php';
    include_once __DIR__ . '/includes/theme/post-types/class-demo_place.php';
    include_once __DIR__ . '/includes/theme/post-types/class-demo_event.php';
    include_once __DIR__ . '/includes/theme/taxonomies/class-demo_term.php';
    include_once __DIR__ . '/includes/theme/taxonomies/class-t_demo_subject.php';


    add_shortcode('oes_breadcumbs', 'oes_breadcumbs');
    add_shortcode('oes_terms_2', 'oes_terms_2');

    /** Hide the WordPress update notifications and obsolete menu structure --------------------------------------*/
    oes_hide_obsolete_menu_structure();

});


/* Add timeline modification -----------------------------------------------------------------------------------------*/
add_action('oes/timeline_plugin_loaded', function () {
    include_once __DIR__ . '/includes/theme/class-demo_timeline_event.php';
});


function oes_breadcumbs(array $args = []): string {

   $breadcrumbs[] = oes_get_page_title(['is_link' => true]);


    if(!empty($args['taxonomy'] ?? '')){
        oes_get_terms_2($breadcrumbs, $args['taxonomy']);
    }

    return implode('  ›  ', $breadcrumbs);
}

function oes_get_terms_2(array &$breadcrumbs, string $taxonomy): void {

    global $oes_post;

    if(empty($taxonomy)){
        return;
    }

    //TODO split?

    $category = oes_get_terms($oes_post->parent_ID, [$taxonomy]);
    if(empty($category[$taxonomy])){
        return;
    }

    foreach($category[$taxonomy] as $term){
        $breadcrumbs[] = $term;
    }
}

function oes_terms_2(array $args = []): string {

    global $oes_post;

    if(empty($args['taxonomy'] ?? '')){
        return '';
    }

    $taxonomy = $args['taxonomy'];

    $category = oes_get_terms($oes_post->parent_ID, [$taxonomy]);

    if(empty($category[$taxonomy])){
        return '';
    }


    return '<span class="oes-terms-2">' . implode('', $category[$taxonomy]) . '</span>';
}