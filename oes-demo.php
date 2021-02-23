<?php

/**
 * Plugin Name: OES Demo
 * Plugin URI: http://www.open-encyclopedia-system.org/
 * Description: Demo plugin of implementing an OES project plugin.
 * Version: 0.1
 * Author: Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
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


use OES\Admin\OES_Admin_Initialize_Assets;
use OES\Config\Post_Type;
use function OES\Admin\oes_initialize_single_page;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* define global constants ------------------------------------------------------------------------------------------ */
define('OES_DEMO_PATH', __DIR__);
define('OES_DEMO_PATH_RELATIVE', '/wp-content/plugins/oes-demo');


/** --------------------------------------------------------------------------------------------------------------------
 * This function will initialize the OES Demo Plugin and link it to the OES Core Plugin.
 *
 * TODO @afterRefactoring : This OES Demo Plugin will be published before the OES Core Plugin is refactored. To
 * implement features and functionalities that will be part of the OES Core Plugin after refactoring but are not part
 * of the currently published OES Core Plugin this OES Demo Plugin contains OES Core Plugin processing inside the
 * /includes/oes directory. We recommend to not modify any of the files inside the above directory.
 * ------------------------------------------------------------------------------------------------------------------
 * @throws Exception
 */
function initialize_oes_demo_plugin()
{

    /** Include plugin dependencies ------------------------------------------------------------------------------------
     * This includes plugin dependencies like third party sources.
     *
     * TODO @beforePublishing : This is optional depending on the pdf-Export option in the frontend. Requires composer
     * on instance to work properly.
     */
    require_once(OES_DEMO_PATH . '/vendor/autoload.php');


    /** Include file to match to pre-refactored OES Core Plugin --------------------------------------------------------
     *
     * TODO @afterRefactoring : This OES Demo Plugin will be published before the OES Core Plugin is refactored. To
     * implement features and functionalities that will be part of the OES Core Plugin after refactoring but are not
     * part of the currently published OES Core Plugin we need to create a separate instance of the OES Core Plugin.
     * After refactoring this instance will merge with the OES Core Plugin instance.
     * Until then the OES Demo Plugin class oes.php::OES_Plugin_Match which extends Oes_Plugin_Bootstrap will
     * include features like settings configuration inside the editorial layer, import tools etc..
     * This will be removed as the oes.php will be part of the OES Core Plugin.
     */
    require(OES_DEMO_PATH . '/includes/oes/oes.php');


    /** Initialize OES -------------------------------------------------------------------------------------------------
     * This will initialize project specific processing that is not part of the OES Core Plugin like the definition of
     * custom post types and taxonomies.
     *
     * TODO @afterRefactoring : After refactoring this will not create a separate instance but add to the existing
     * OES Core Plugin instance.
     */
    $oes = OES();


    /** Include plugin config ------------------------------------------------------------------------------------------
     * This will include global constants throughout the project like post type names and post field values.
     * TODO @afterRefactoring : replace Oes_General_Config
     */
    oes_include('/includes/project.class.php', OES_DEMO_PATH);
    Oes_General_Config::$PROJECT_CONFIG = new OES_Project_Config(OES_DEMO_PATH);


    /** Initialize the project -----------------------------------------------------------------------------------------
     * This will initialize the project by registering post types, taxonomies and custom post status.
     */

    /* include taxonomies */
    $oes->taxonomyConfigs = [
        OES_DEMO_PATH . '/includes/data/taxonomies/topic.config.php',
        OES_DEMO_PATH . '/includes/data/taxonomies/article-category.config.php'
    ];

    /* include post types */
    $oes->postTypeConfigs = [
        OES_DEMO_PATH . '/includes/data/post_types/article.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/glossary.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/contributor.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/bibliography.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/index-person.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/index-institute.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/index-place.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/index-subject.config.php',
        OES_DEMO_PATH . '/includes/data/post_types/index-time.config.php'
    ];


    /* include custom post status */
    $oes->post_status['oes-locked'] = [
        'label' => _x('OES Locked', 'post status', 'oes'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('OES Locked <span class="count">(%s)</span>',
            'OES Locked <span class="count">(%s)</span>', 'oes'),
    ];

    /* initialize project */
    $oes->init();


    /** Initialize settings page for plugins ---------------------------------------------------------------------------
     * This will include all css and js needed inside the editorial layer for this OES Demo Plugin. (Css and js for the
     * OES Core Plugin are defined inside the OES Core Plugin.)
     */
    $oes->assets = new OES_Admin_Initialize_Assets();
    $oes->assets->add_style('oes-demo', OES_DEMO_PATH_RELATIVE . '/assets/css/oes-demo.css', [], oes_get_version(), 'all');
    $oes->assets->add_script('oes-demo', OES_DEMO_PATH_RELATIVE . '/assets/js/oes-demo.js', 'jquery', oes_get_version(), true);
    oes_include('/includes/admin/pages/page-configuration-project.php', OES_DEMO_PATH);


    /** Include demo pages ---------------------------------------------------------------------------------------------
     * This will generate demo pages that are used in the OES Demo theme. These pages are not needed inside the
     * editorial layer and can be excluded. If using the OES Demo theme you can edit the pages and fill them with
     * content.
     */

    /* Index page ----------------------------------------------------------------------------------------------------*/
    $args['index'] = ['post_title' => 'OES Demo Index',
        'post_type' => 'page',
        'post_name' => 'oes-demo-index',
        'post_content' => 'This is the OES Demo Index page. No content needed.',
        'post_status' => 'publish',
        'guid' => site_url() . '/oes-demo-index'];

    /* About us ------------------------------------------------------------------------------------------------------*/
    $args['about-us'] = ['post_title' => 'OES Demo About OES',
        'post_type' => 'page',
        'post_name' => 'oes-demo-about',
        'post_content' => 'This is a OES Demo page. Import Content or edit here.',
        'post_status' => 'publish',
        'guid' => site_url() . '/oes-about-oes'];

    /* Front page ----------------------------------------------------------------------------------------------------*/
    $args['frontpage'] = ['post_title' => 'OES Demo Front Page (Left)',
        'post_type' => 'page',
        'post_name' => 'oes-demo-front-page',
        'post_content' => '<h3>Title</h3>The content of the "OES Demo Front Page" will be displayed on the front page 
        on the left side.',
        'post_status' => 'publish',
        'guid' => site_url() . '/oes-demo-front-page'];

    $args['use-cases'] = ['post_title' => 'OES Demo Front Page (Right)',
        'post_type' => 'page',
        'post_name' => 'oes-demo-use-cases',
        'post_content' => '<h3>Title</h3>The content of the "OES Demo Front Page" will be displayed on the front page 
        on the right side.',
        'post_status' => 'publish',
        'guid' => site_url() . '/oes-demo-use-cases'];


    /* initialize pages */
    foreach ($args as $pageArgs) oes_initialize_single_page($pageArgs);


    /** Include filter for list views ----------------------------------------------------------------------------------
     * This will initialize filter for the list view of a post type inside the editorial layer.
     */
    $oes->postTypes['oes_demo_article']['list_columns'] = [
        'cb' => true,
        'title' => true,
        'oes_demo_article_title' => [
            'label' => __('Display Title', 'oes-demo'),
            'type' => 'acf_field',
            'filter' => true
        ],
        'master' => [
            'label' => __('Master Article', 'oes-demo'),
            'type' => 'master'
        ],
        Post_Type::FIELD_EDITING_STATUS => [
            'label' => __('Post status', 'oes-demo'),
            'type' => 'acf_field_select',
            'filter' => true
        ],
        'date' => true
    ];

    $oes->filter['any'] = true;
    $oes->filter['custom'] = true;
    new OES\Admin\Column_Filter();


    /** Add restricted user role ---------------------------------------------------------------------------------------
     * For the purpose of a demo application add a "read only" user role.
     */
    oes_include('/includes/admin/restrict-user.php', OES_DEMO_PATH);
}

add_action('plugins_loaded', 'initialize_oes_demo_plugin');


/**
 * Add favicon to WordPress admin pages.
 *
 * TODO @afterRefactoring : path to favicon will change after refactoring.
 */
function oes_favicon_for_admin()
{
    echo '<link rel="icon" type="image/x-icon" href="' . plugin_dir_url(__FILE__) .
        'includes/oes/assets/images/favicon.ico" />';
}

add_action('admin_head', 'oes_favicon_for_admin');