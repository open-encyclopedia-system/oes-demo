<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Menu_Page_Project')) :

    /**
     * Class Menu_Page_Project
     *
     * Create a settings page inside the editorial layer to hold information about the OES DEMO theme.
     */
    class Menu_Page_Project extends Menu_Page
    {
        protected function set_page_parameters()
        {
            /* make page a sub page of OES Settings */
            $this->subPage = true;

            $this->pageParameters = [
                'page_title' => 'OES Theme',
                'menu_title' => 'OES Theme',
                'menu_slug' => $this->mainSlug . '_frontend',
                'position' => 2
            ];
        }

        function html()
        {
            oes_get_view('view-configuration-project', [], OES_DEMO_PATH);
        }

    }

// initialize
    OES()->menuPage['admin_demo'] = new Menu_Page_Project();

endif;