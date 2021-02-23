<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Menu_Page_Information')) :

    class Menu_Page_Information extends Menu_Page
    {
        protected function set_page_parameters()
        {
            $this->subPage = true;

            $args = [
                'page_title' => 'Information',
                'menu_title' => 'Information',
                'menu_slug' => $this->mainSlug
            ];
            $this->pageParameters = $args;

        }

        function html()
        {
            oes_get_view('view-information', [], OES_PATH_TEMP);
        }

    }

// initialize
    OES()->menuPage['information']  = new Menu_Page_Information();

endif;