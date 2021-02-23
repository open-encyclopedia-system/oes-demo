<?php

namespace OES\Admin\Tools;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Import_Default_Options')) :

    /**
     * Class Import_Default_Options
     *
     * Import options.
     */
    class Import_Default_Options extends Tools
    {

        /**
         * Initialize class parameters
         */
        function initialize_parameters()
        {
            $this->name = 'import-default-options';
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
            submit_button(__('Import Default Options', 'oes'));
        }


        /**
         * Runs when admin post request for the given action.
         */
        function admin_post_tool_action()
        {
            /* get path */
            if ($_POST['config_file_location']) {
                $filePath = OES_PATH_TEMP . '/includes/config/';
            } else {
                $filePath = WP_CONTENT_DIR . '/uploads/';
            }

            /* get file */
            $fileAsString = file_get_contents($filePath . 'option-defaults.json');

            /* exit if file not found or empty TODO @2.0 Roadmap : oes_notice display message */
            if (!$fileAsString || empty($fileAsString)) return;

            /* get options from file */
            $optionArray = json_decode($fileAsString, true);

            /* loop through all existing options and delete them if they do not exist in the file */
            foreach (oes_get_all_oes_options() as $key => $option) {
                if (!$optionArray[$key]) delete_option($key);
            }

            /* loop through option from file and update options */
            foreach ($optionArray as $optionSectionKey => $optionSection) {
                update_option($optionSectionKey, $optionSection);
            }

        }

    }

// initialize
    oes_register_admin_tool('\OES\Admin\Tools\Import_Default_Options');

endif;