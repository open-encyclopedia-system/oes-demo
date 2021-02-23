<?php


use OES\Post_Type\Post_Type;
use OES\Taxonomy\Taxonomy;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Plugin_Match')) :

    /**
     * Class OES_Plugin_Match
     *
     * TODO @afterRefactoring : This class will be moved to core plugin, merged with Oes_Plugin_Bootstrap and renamed to
     * OES_Plugin. This class currently extends oes-core.php/Oes_Plugin_Bootstrap but will replace functions after
     * refactoring.
     * This OES Demo Plugin will be published before the OES Core Plugin is refactored. To
     * implement features and functionalities that will be part of the OES Core Plugin after refactoring but are not
     * part of the currently published OES Core Plugin we need to create a separate instance of the OES Core Plugin.
     * After refactoring the both instances will be merged.
     */
    class OES_Plugin_Match extends Oes_Plugin_Bootstrap
    {

        /**
         * @var array An array containing the config files for post type to be registered.
         * TODO @afterRefactoring : This will replace the variable $registeredPostTypeConfigs.
         * It is renamed because the variable stores the configs of post types to be registered and not registered
         * post types.
         */
        var $postTypeConfigs = [];


        /**
         * @var array An array containing the config files for taxonomies to be registered.
         * TODO @afterRefactoring : This is a new variable and will be merged after refactoring to  the OES Core Plugin
         * class.
         */
        var $taxonomyConfigs = [];


        /**
         * @var string A string containing the plugin version.
         * TODO @afterRefactoring : This is a new variable and will be merged after refactoring to  the OES Core Plugin
         * class.
         */
        var $version = '1.1';


        /**
         * Initialize post type processing.
         *
         * TODO @afterRefactoring : This function will be merged with the parent function  init(). Currently the
         * processing of the parent function will be initialized with the OES Core Plugin instance which will be
         * merged with the OES Demo Plugin instance after refactoring.
         * This will replaces oes-core.php/Oes_Plugin_Bootstrap::init() by merging the contents.
         *
         * Overwriting because:
         * The OES Core Plugin function uses in oes-core.php add_action('init' .. Oes_Wf_Factory::registerWfPostTypes).
         * The main functionality is register_post_type to register the custom post types.
         * To add configuration options while registering post type use the refactored class OES_Post_Type.
         * Similar for taxonomies.
         * To use configurations for bidirectional inheritance include customized acf functionalities.
         *
         * @throws Exception
         */
        function init()
        {
            $this->initialize_acf();//TODO @afterRefactoring : replaces $this->init_acf();
            $this->initialize_taxonomies();//TODO @afterRefactoring : replaces $this->initRegisteredTaxonomies();
            $this->initialize_post_types();//TODO @afterRefactoring : replaces $this->initPostTypeConfigRegistration();
        }


        /**
         * Initializes acf functions.
         *
         * Including the generation of fields and field groups or the feature "Bidirectional Inheritance" for acf fields.
         * TODO @afterRefactoring : Merge with parent function to include OES specific fields etc. Before Refactoring
         * these will be included by the separate OES Core Plugin instance. After refactoring the OES Core Plugin
         * instance and the OES Demo Plugin instance will be merged.
         */
        function initialize_acf()
        {
            oes_include('/includes/acf/acf.php', OES_PATH_TEMP);
            oes_include('/includes/acf/acf-field-group.class.php', OES_PATH_TEMP);
            oes_include('/includes/acf/acf-bidirectional-relationship.php', OES_PATH_TEMP);
        }


        /**
         * Initialize post types.
         *
         * TODO @afterRefactoring : After refactoring this will replace the OES Core Plugin function
         * initPostTypeConfigRegistration, former alias init_post_types().
         *
         * Overwriting because :
         * The OES Core Plugin function uses in oes-core.php add_action('init' .. Oes_Wf_Factory::registerWfPostTypes).
         * The main functionality is register_post_type to register the custom post types.
         * To add configuration options while registering post type use the refactored class OES_Post_Type.
         */
        function initialize_post_types()
        {
            /* hook registration of post types to 'init' .*/
            add_action('init', function () {

                /* loop through post type config files. */
                foreach ($this->postTypeConfigs as $postTypeConfig) {

                    if (file_exists($postTypeConfig)) {

                        /* register new custom post type and add configuration options to the editorial layer. */
                        $postType = new Post_Type($postTypeConfig);
                        $postType->oes_register_post_type();

                    } else {
                        throw new Exception('Post type config file does not exist : ' . $postTypeConfig);
                    }
                }
            });
        }


        /**
         * Initialize taxonomies.
         *
         * TODO @afterRefactoring : After refactoring this will replace the OES Core Plugin function
         * initRegisteredTaxonomies().
         *
         * Overwriting because :
         * The OES Core Plugin function uses the wordpress function register_taxonomy().
         * To add configuration options while registering taxonomies use the refactored class OES_Taxonomy.
         */
        function initialize_taxonomies()
        {
            /* hook registration of post types to 'init' .*/
            add_action('init', function () {

                /* loop through taxonomy config files. */
                foreach ($this->taxonomyConfigs as $taxonomyConfigFile) {

                    /* register new taxonomy and add configuration options to the editorial layer. */
                    if (file_exists($taxonomyConfigFile)) {
                        $taxonomy = new Taxonomy($taxonomyConfigFile);
                        $taxonomy->oes_register_taxonomy();
                    } else {
                        throw new Exception('Taxonomy config file does not exist : ' . $taxonomyConfigFile);
                    }
                }
            });
        }


        /**
         * Returns the plugin version or null if doesn't exist.
         *
         * @return mixed Returns a setting or null.
         */
        function get_version()
        {
            return isset($this->version) ? $this->version : null;
        }


        /**
         * Define a global constants for this project.
         *
         * @param string $name A string containing the name of the global constant.
         * @param string|boolean $value A string containing the value of the global constant.
         */
        function define($name, $value = true)
        {
            if (!defined($name)) define($name, $value);
        }


        /**
         * OES Initializing of the OES Core Plugin functionalities and features.
         *
         * TODO @afterRefactoring : This will partly replaces init_libs_and_classes and init_spl.
         */
        function initialize_core()
        {

            /** 1. Set OES Core Plugin constants -----------------------------------------------------------------------
             * This will set constants that are used throughout the OES Core Plugin and the Project Plugin.
             */
            $this->define('OES_PATH', OES_PLUGIN_DIR);
            $this->define('OES_PREFIX', 'oes');

            /*TODO @afterRefactoring : This constant should should be = '/' . PLUGINDIR. '/' . basename(__DIR__); */
            $this->define('OES_PATH_RELATIVE', '/' . PLUGINDIR . '/' . basename(OES_DEMO_PATH) . '/includes/oes');

            /*TODO @afterRefactoring : This constant should be obsolete as after refactoring OES_PATH_TEMP = OES_PATH. */
            $this->define('OES_PATH_TEMP', OES_DEMO_PATH . '/includes/oes');
            $this->define('OES_PATH_RELATIVE_PART', basename(OES_DEMO_PATH) . '/includes/oes/');


            /** 2. Include functionalities for OES Core Plugin processing. ---------------------------------------------
             * This will include functions that are used throughout the OES Core Plugin and the Project Plugin.
             * Especially include the function 'oes_include'.
             */
            require(OES_DEMO_PATH . '/includes/oes/includes/functions-utility.php');
            oes_include('/includes/functions-text-processing.php', OES_PATH_TEMP);
            oes_include('/includes/functions-post.php', OES_PATH_TEMP);
            oes_include('/includes/functions-html.php', OES_PATH_TEMP);


            /** 3. Include OES Core Plugin config ----------------------------------------------------------------------
             * This will include global constants throughout the OES Core Plugin like field names or field attributes.
             */
            oes_include('/includes/config/config.class.php', OES_PATH_TEMP);


            /** 4. Include messaging to display admin notices in the editorial layer -----------------------------------
             * This will include messaging to display admin notices in the editorial layer.
             */
            oes_include('/includes/admin/notices.php', OES_PATH_TEMP);


            /** 5. Include css and js inside the editorial layer -------------------------------------------------------
             * This will include all css and js needed inside the editorial layer for this OES Core Plugin.
             */
            oes_include('/includes/admin/admin.php', OES_PATH_TEMP);


            /** 6. Include modification of columns for post types lists inside the editorial layer. --------------------
             * This will include modification of columns for post types lists inside the editorial layer.
             */
            oes_include('/includes/admin/column-filter.php', OES_PATH_TEMP);


            /** 7. Include admin pages inside the editorial layer ------------------------------------------------------
             * This will include admin pages inside the editorial layer for this OES Core Plugin and the functionalities
             * on this pages, eg. settings options inside the editorial layer.
             * Furthermore it initializes the feature "Admin Page".
             */

            /* Initialize the feature "Admin Page": add admin pages to the editorial layer. */
            oes_include('/includes/admin/pages/page.class.php', OES_PATH_TEMP);

            /* Include the generation of settings options. */
            oes_include('/includes/option/option.class.php', OES_PATH_TEMP);

            /* Include the specific OES Core Plugin option page and its subpages. */
            oes_include('/includes/admin/pages/page-oes_settings.php', OES_PATH_TEMP);
            oes_include('/includes/admin/pages/page-information.php', OES_PATH_TEMP);
            oes_include('/includes/admin/pages/page-configuration.php', OES_PATH_TEMP);
            oes_include('/includes/admin/pages/page-tools.php', OES_PATH_TEMP);


            /** 8. Include further features and functionalities for processing inside the OES Core Plugin --------------
             * This will include further features and functions that are used throughout the OES Core Plugin and the
             * Project Plugin.
             */

            /* Include project functionalities. TODO @afterRefactoring : The project.class.php will be merged with the
            Oes_Project_Config_Base class.*/
            oes_include('/includes/project/project.class.php', OES_PATH_TEMP);

            /* Include taxonomy functionalities for registering taxonomies and processing configuration settings from
            the editorial layer. */
            oes_include('/includes/taxonomy/taxonomy.class.php', OES_PATH_TEMP);

            /* Include post type functionalities for registering post types and processing configuration settings from
            the editorial layer. */
            oes_include('/includes/post_type/post_type.class.php', OES_PATH_TEMP);

            /* Include data transformations and processing for post types. Eg. the feature "Versioning".  */

            /* Versioning */
            oes_include('/includes/versioning/versioning.php', OES_PATH_TEMP);

            /* Custom Post Status */
            oes_include('/includes/admin/post-status.php', OES_PATH_TEMP);


            /** 9. Include footnotes -----------------------------------------------------------------------------------
             * This will include the feature 'footnotes' for the frontend.
             */
            oes_include('/includes/footnotes/footnotes.php', OES_PATH_TEMP);


            /** 9. Include theme options -------------------------------------------------------------------------------
             * This will include functionalities for the WordPress theme. Eg. advanced search functions or a class
             * preparing a post type for display in the frontend theme.
             *
             * TODO @2.0 Roadmap : call this optional from project plugin or theme.
             */
            oes_include('/includes/theme/post_type_theme.class.php', OES_PATH_TEMP);
            oes_include('/includes/theme/functions-theme.php', OES_PATH_TEMP);


            /* TODO @2.0 Roadmap : optional call from project plugin to add image license to media */
            oes_include('/includes/admin/image-license.php', OES_PATH_TEMP);


        }
    }
endif;


/**
 * The function returns the OES instance like a global variable everywhere inside the plugin.
 * It initializes the OES plugin if not yet initialized.
 *
 * @return OES_Plugin_Match Returns the OES plugin instance.
 *
 * TODO @afterRefactoring : This OES Demo Plugin will be published before the OES Core Plugin is refactored. To
 * implement features and functionalities that will be part of the OES Core Plugin after refactoring but are not part
 * of the currently published OES Core Plugin we need to create a separate instance of the OES Core Plugin. The
 * following code will move to the OES Core Plugin after the refactoring.
 */
if (!function_exists('OES')) {
    function OES()
    {
        global $oes;

        /* initialize */
        if (!isset($oes)) {
            $oes = new OES_Plugin_Match();
            $oes->initialize_core();
        }

        return $oes;
    }
}