<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Menu_Page_OES_Settings')) :

    class Menu_Page_OES_Settings extends Menu_Page
    {
        protected function set_page_parameters()
        {
            $args = [
                'page_title' => 'Settings',
                'menu_title' => 'OES Settings'
            ];
            $this->pageParameters = $args;
        }

        function html(){
            //overwritten by page-information
        }
    }

    /* initialize */
    OES()->menuPage['OES_settings'] = new Menu_Page_OES_Settings();
endif;
?>