<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Menu_Page_Tools')) :

    /**
     * Class Menu_Page_Tools
     *
     * Class preparing the menu page 'Tools'.
     */
    class Menu_Page_Tools extends Menu_Page
    {

        /**
         * Set class parameters. Including page parameters.
         */
        protected function set_page_parameters()
        {
            $this->subPage = true;

            $args = [
                'page_title' => 'Tools',
                'menu_title' => 'Tools',
                'menu_slug' => $this->mainSlug . '_tools',
                'position' => 2
            ];
            $this->pageParameters = $args;
        }


        /**
         * Callback html function for page
         */
        function html()
        {
            oes_get_view('view-tools', [], OES_PATH_TEMP);
        }


        /**
         * Determines processing when the admin screen or script is being initialized.
         */
        function admin_init()
        {
            /* register tools */
            oes_include('/includes/admin/tools/tool.class.php', OES_PATH_TEMP);
            oes_include('/includes/admin/tools/tool-export.php', OES_PATH_TEMP);
            oes_include('/includes/admin/tools/tool-import.php', OES_PATH_TEMP);
            oes_include('/includes/admin/tools/tool-delete-posts.php', OES_PATH_TEMP);
        }

    }

// initialize
    OES()->menuPage['tools'] = new Menu_Page_Tools();

endif;