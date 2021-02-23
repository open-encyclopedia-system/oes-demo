<?php

namespace OES\Admin\Tools;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Export_Default_Options')) :

    /**
     * Class Export_Default_Options
     *
     * Export options to json file.
     */
    class Export_Default_Options extends Tools
    {

        /**
         * Initialize class parameters
         */
        function initialize_parameters()
        {
            $this->name = 'export-default-options';
            $this->formAction = admin_url('admin-post.php');
            $this->formParameters = ' enctype="multipart/form-data"';
        }


        /**
         * Display the tools parameters for form.
         *
         * TODO @2.0 Roadmap: switch for different location for config file
         */
        function html()
        {
            ?>
            <div id="tools" style="padding-bottom:15px;display:none">
                <input type="checkbox" id="config_file_location" name="config_file_location">
                <label for="config_file_location"><?php
                    _e('Use include/config directory instead of assets directory.',
                        'oes'); ?></label>
            </div>
            <?php
            submit_button(__('Export Default Options', 'oes'));
        }


        /**
         * Runs when admin post request for the given action.
         */
        function admin_post_tool_action()
        {

            /* get all oes options */
            $options = [];
            foreach (oes_get_all_oes_options() as $key => $option) {
                $options[$key] = get_option($key);
            }

            /* get path */
            if ($_POST['config_file_location']) {
                $filePath = OES_PATH_TEMP . '/includes/config/';
            }
            else {
                $filePath = WP_CONTENT_DIR . '/uploads/';
            }

            /* write json file */
            $fp = fopen($filePath . 'option-defaults.json', 'w');
            fwrite($fp, json_encode($options));
            fclose($fp);

        }

    }

    // initialize
    oes_register_admin_tool('\OES\Admin\Tools\Export_Default_Options');

endif;