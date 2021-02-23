<?php

namespace OES\Admin;


use OES\Config\Admin;
use OES\Config\Option;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Menu_Page')) :

    /**
     * Class Menu_Page
     *
     * Create pages and subpages inside the editorial layer to store information and settings.
     * This class calls the WordPress functions add_menu_page and add_submenu_page.
     */
    abstract class Menu_Page
    {

        /** @var array Array containing the page parameters.
         * Further information for valid parameters see
         * https://developer.wordpress.org/reference/functions/add_menu_page/
         * and
         * https://developer.wordpress.org/reference/functions/add_submenu_page/
         */
        var $pageParameters = [];

        /** @var string A string containing the page hook. */
        var $pagehook = '';

        /** @var bool Boolean identifying the page as subpage. If true, the page is a subpage. */
        var $subPage = false;

        /** @var string String containing the main slug for the page. Default is the option page main slug. */
        var $mainSlug = Option::ADMIN_PAGE_MAIN_SLUG;


        /**
         * OES_Includes_Admin_Page constructor.
         */
        function __construct()
        {
            $this->set_page_parameters();
            $this->validate_parameters();

            add_action('admin_menu', [$this, 'admin_menu']);
            add_action('admin_init', [$this, 'admin_init']);
        }


        /**
         * Function to set the class parameters $pageParameters and $subPage.
         */
        abstract protected function set_page_parameters();


        /**
         * The function validates the page parameters and sets the page parameters class variable.
         */
        private function validate_parameters()
        {
            if ($this->subPage) {
                $param = wp_parse_args($this->pageParameters, [
                    'parent_slug' => $this->mainSlug,
                    'page_title' => 'Page Title',
                    'menu_title' => 'Menu Title',
                    'capability' => 'manage_options_oes',
                    'menu_slug' => $this->mainSlug . '_subpage',
                    'function' => [$this, 'html'],
                    'position' => 0
                ]);

            } else {
                $param = wp_parse_args($this->pageParameters, [
                    'page_title' => 'Page Title',
                    'menu_title' => 'Menu title',
                    'capability' => 'manage_options_oes',
                    'menu_slug' => $this->mainSlug,
                    'function' => [$this, 'html'],
                    'icon_url' =>  plugins_url(Admin::CUSTOM_MENU_ICON_PATH),
                    'position' => '80.1'
                ]);
            }
            $this->pageParameters = $param;
        }


        /**
         * The function generates a menu page and hooks the load function.
         *
         * @return string Returns the generated page.
         */
        function admin_menu()
        {
            if($this->subPage){
                $page = add_submenu_page(
                    $this->pageParameters['parent_slug'],
                    $this->pageParameters['page_title'],
                    $this->pageParameters['menu_title'],
                    $this->pageParameters['capability'],
                    $this->pageParameters['menu_slug'],
                    $this->pageParameters['function'],
                    $this->pageParameters['position']
                );
            }
            else{
                $page = add_menu_page(
                    $this->pageParameters['page_title'],
                    $this->pageParameters['menu_title'],
                    $this->pageParameters['capability'],
                    $this->pageParameters['menu_slug'],
                    $this->pageParameters['function'],
                    $this->pageParameters['icon_url'],
                    $this->pageParameters['position']
                );
                do_action('oes_after_admin_menu');
            }

            /* set page hook */
            $this->pagehook = $page;

            /* add load page action */
            add_action('load-' . $page, [$this, 'load']);

            /* add capabilities to admin */
            get_role('administrator')->add_cap($this->pageParameters['capability']);

            return $page;
        }


        /**
         * Determines processing when the admin screen or script is being initialized.
         */
        function admin_init()
        {
        }


        /**
         * Callback function for the generated page. Supplies the html for the generated page.
         */
        function html()
        {
        }


        /**
         * Runs when generated page is loaded.
         */
        function load()
        {
            /* enqueue scripts for postboxes */
            wp_enqueue_script('postbox');
        }

    }
endif;


/**
 * Add a single page. Display warning if the page already exists but is not published.
 *
 * @param array $args Return page from wp_insert_post if newly created.
 */
function oes_initialize_single_page($args = [])
{
    $pageGuid = $args['guid'];
    $page = get_post(oes_get_page_ID_from_GUID($pageGuid));

    if ($page) {
        if ($page->post_status != 'publish') {
            add_action('admin_notices', function () use ($page) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><?php printf(
                            __('The page "%s" exists but is not published. Check also draft and trash.', 'oes'),
                            $page->post_title); ?></p>
                </div>
                <?php
            });
        }
    } else {
        wp_insert_post($args, false);
    }
}


/**
 * Get the page id from guid.
 *
 * @param string $guid The page guid.
 * @return mixed Return the page id.
 */
function oes_get_page_ID_from_GUID($guid)
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid=%s", $guid));
}