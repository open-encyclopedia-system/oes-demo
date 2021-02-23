<?php

namespace OES\Post_Type;

use OES\ACF as ACF;
use OES\Config as C;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Post_Type')) :

    /**
     * Class Post_Type
     *
     * Class creates a new post type and registers post type.
     */
    class Post_Type
    {
        /** @var bool Flag identifying if post type should be registered */
        var $registerPostType = true;

        /** @var bool Flag identifying if versioning should be included */
        var $versioning = false;

        /** @var string|bool String containing post type name */
        var $postType = null;

        /** @var string|bool String containing post type name for master post type */
        var $postTypeMaster = null;

        /** @var array Array containing post type parameters */
        var $args = [];

        /** @var array Array containing master post type parameters */
        var $argsMaster = [];

        /** @var array Array containing field group for post type */
        var $fieldGroup = [];

        /** @var array Array containing field group for master post type */
        var $fieldGroupMaster = [];

        /** @var array Array containing fields for post type */
        var $fields = [];

        /** @var array Array containing fields for master post type */
        var $fieldsMaster = [];

        /** @var array Array containing information which fields are to be inherited from master to child post */
        var $inheritFields = [];

        /** @var array Array containing information which terms are to be inherited from master to child post */
        var $inheritTerms = [];

        /** @var bool|string String containing dtm class for post type */
        var $dtmClass = false;

        /** @var bool Flag identifying if post type is attachment */
        var $isAttachment = false;

        /** @var bool Flag identifying if post type is image */
        var $isImage = false;

        /** @var bool Flag identifying if translating should be included */
        var $translating = false;

        /** @var array Array containing language labels and identifiers for translation */
        var $languages = [];

        /** @var bool Flag identifying if configuration from file should be overwritten by admin panel */
        var $overwriteConfigFile = false;


        /**
         * Post_Type constructor.
         *
         * @param string $configFile An array containing the config file with configurations for post types.
         * @throws Exception
         */
        public function __construct($configFile)
        {


            /* include file and get parameters -----------------------------------------------------------------------*/
            if (!oes_validate_file($configFile)) {
                throw new Exception(oes_validate_file($configFile));
            }
            include($configFile);

            /* get config parameter */
            $config = isset($config) ? $config : [];


            /* get configuration from option page --------------------------------------------------------------------*/
            $source = get_option(C\Option::POST_TYPE_GENERAL);
            if (isset($source['data_source'])) {
                if ($source['data_source'] == 'panel') $this->overwriteConfigFile = true;
            }


            /* ---- set versioning -----------------------------------------------------------------------------------*/
            $this->versioning = isset($config[C\Post_Type::ENABLE_VERSIONING]) ?
                $config[C\Post_Type::ENABLE_VERSIONING] : false;


            /* ---- set translating ----------------------------------------------------------------------------------*/
            $this->translating = isset($config[C\Post_Type::ENABLE_TRANSLATING]) ?
                $config[C\Post_Type::ENABLE_TRANSLATING] : false;

            /* set languages */
            if($this->translating){
                $this->languages = isset($config[C\Post_Type::TRANSLATING_LANGUAGES]) ?
                    $config[C\Post_Type::TRANSLATING_LANGUAGES] : false;
                $this->validate_translating_languages();
            }


            /* ---- set parameters for dtm ---------------------------------------------------------------------------*/
            $this->dtmClass = $config[C\Post_Type::DTM_CLASS];
            if (empty($this->dtmClass)) throw new Exception('dtm_class missing');


            /* ---- set post type for register_post_type -------------------------------------------------------------*/
            if (!isset($config[C\Post_Type::POST_TYPE])) {
                throw new Exception('Post type is missing.');
            } else {
                $postTypeTemp = $config[C\Post_Type::POST_TYPE];

                /* post type must start with 'oes_' */
                if (!oes_starts_with($postTypeTemp, OES_PREFIX . '_')) {
                    $postTypeTemp = OES_PREFIX . '_' . $postTypeTemp;
                }

                /* post type can not be longer than 20 characters */
                if (strlen($postTypeTemp) > 20) {
                    $postTypeTemp = substr($postTypeTemp, 0, 20);
                }

                $this->postType = $postTypeTemp;
            }


            /* ---- set master post type for register_post_type ------------------------------------------------------*/
            /* master post type name = [post type name] _m */
            if ($this->versioning) {

                /* post type can not be longer than 20 characters, so for master not longer than 18 */
                if (strlen($this->postType) > 18) {
                    $postTypeMasterTemp = substr($this->postType, 0, 18) . '_m';
                } else {
                    $postTypeMasterTemp = $this->postType . '_m';
                }

                /* master post type can not have the same name as post type */
                if ($this->postType == $postTypeMasterTemp) {
                    if (strlen($this->postType) > 17) {
                        $postTypeMasterTemp = substr($this->postType, 0, 17) . '_ma';
                    } else {
                        $postTypeMasterTemp = $this->postType . '_ma';
                    }
                }

                $this->postTypeMaster = $postTypeMasterTemp;
            }


            /* ---- set parameters for register_post_type ------------------------------------------------------------*/

            $this->isAttachment = ($this->postType == C\Post_Type::ATTACHMENT);

            $this->isImage = isset($config[C\Post_Type::IS_IMAGE]) ? $config[C\Post_Type::IS_IMAGE] : $this->isImage;

            $this->registerPostType = isset($config[C\Post_Type::DONT_ADD]) ?
                $config[C\Post_Type::DONT_ADD] : $this->registerPostType;

            if ($this->registerPostType && !$this->isAttachment) {

                /* parameters */
                foreach (C\Post_Type::ARGS as $key => [$custLabel, $default]) {
                    $args[$key] = isset($config[$custLabel]) ? $config[$custLabel] : $default;
                }

                /* overwrite parameter for master */
                if ($this->versioning) {
                    $argsMaster['description'] = 'Master post type to ' . $this->postType;
                    $argsMaster['has_archive'] = false;
                    $argsMaster['exclude_from_search'] = true;
                    $argsMaster['public'] = true;
                }

                /* overwrite menu icon default */
                if (!$args['menu_icon']) {
                    $customIconPath = OES_DEMO_PATH . '/../' . C\Admin::CUSTOM_MENU_ICON_PATH;

                    $args['menu_icon'] = 'dashicons-clipboard';
                    if(file_exists($customIconPath)){
                        if (getimagesize($customIconPath)) {
                            $args['menu_icon'] = plugins_url(C\Admin::CUSTOM_MENU_ICON_PATH);
                        }
                    }

                    if ($this->versioning) {
                        $customIconPathMaster = OES_DEMO_PATH . '/../' . C\Admin::CUSTOM_MENU_ICON_PATH_MASTER;

                        $argsMaster['menu_icon'] = 'dashicons-clipboard';
                        if(file_exists($customIconPathMaster)){
                            if (getimagesize($customIconPathMaster)) $argsMaster['menu_icon'] =
                                plugins_url(C\Admin::CUSTOM_MENU_ICON_PATH_MASTER);
                        }
                    }
                }

                /* overwrite menu icon default for secondary icon */
                if ($args['menu_icon'] && $args['menu_icon'] == 'secondary') {

                    $customIconPath = OES_DEMO_PATH . '/../' . C\Admin::CUSTOM_MENU_ICON_PATH_SECOND;

                    if(file_exists($customIconPath)){
                        if (getimagesize($customIconPath)) {
                            $args['menu_icon'] = plugins_url(C\Admin::CUSTOM_MENU_ICON_PATH_SECOND);
                        }
                    }

                    if ($this->versioning) {
                        $customIconPathMaster = OES_DEMO_PATH . '/../' . C\Admin::CUSTOM_MENU_ICON_PATH_MASTER;

                        if(file_exists($customIconPathMaster)){
                            if (getimagesize($customIconPathMaster)) $argsMaster['menu_icon'] =
                                plugins_url(C\Admin::CUSTOM_MENU_ICON_PATH_MASTER);
                        }
                    }
                }

                /* labels */
                foreach (C\Post_Type::ARGS_LABELS as $key => [$custLabel, $default]) {
                    $args['labels'][$key] =
                        isset($config['labels'][$custLabel]) ? $config['labels'][$custLabel] : $default;
                }

                /* singular_name = name if empty */
                $args['labels']['singular_name'] =
                    isset($config['labels']['singular_name']) ?
                        $config['labels']['singular_name'] :
                        (isset($config['labels']['name']) ? $config['labels']['name'] : 'singular name missing');

                /* name = singular_name if empty */
                $args['labels']['name'] =
                    isset($config['labels']['name']) ?
                        $config['labels']['name'] :
                        (isset($config['labels']['singular_name']) ?
                            $config['labels']['singular_name'] :
                            'name missing');

                /* modify menu name */
                $args['labels']['menu_name'] =
                    isset($config['labels']['menu_name'])
                        ? $config['labels']['menu_name'] :
                        (isset($config['labels'][C\Post_Type::LABELS_PLURAL])
                            ? $config['labels'][C\Post_Type::LABELS_PLURAL] :
                            'menu name missing');

                /* modify for master post type */
                if ($this->versioning) {
                    $argsMaster['labels']['singular_name'] = $args['labels']['singular_name'] . ' Master';
                    $argsMaster['labels']['name'] = $args['labels']['name'] . ' Master';
                    $argsMaster['labels']['menu_name'] = $args['labels']['menu_name'] . ' Master';
                }

                /* configure display, overwrites config values */
                //TODO @2.0 Roadmap : more for master
                if ($this->overwriteConfigFile) {
                    $optionGroup = get_option(C\Option::POST_TYPE);
                    if (isset($optionGroup)) {

                        /* change labels */
                        if (isset($optionGroup[$this->postType . '_new_label'])) {

                            if ($optionGroup[$this->postType . '_new_label'] != C\Option::TEXT_FIELD_DUMMY &&
                                !empty($optionGroup[$this->postType . '_new_label'])) {
                                $labels = explode(';', $optionGroup[$this->postType . '_new_label']);
                                if ($labels) {
                                    $args['labels']['singular_name'] = isset($labels[1]) ? $labels[1] : $labels[0];
                                    $args['labels']['name'] = $labels[0];
                                    $args['labels']['menu_name'] = $labels[0];
                                }
                            }
                        }

                        /* only register, do not display in menu */
                        $args['show_in_menu'] = isset($optionGroup[$this->postType . '_show_in_menu']);

                        /* display in navigation menus */
                        $args['show_in_nav_menus'] = isset($optionGroup[$this->postType . '_show_in_nav_menu']);

                        /* make post type hierarchical */
                        $args['hierarchical'] = isset($optionGroup[$this->postType . '_hierarchical']);
                        if(isset($optionGroup[$this->postType . '_hierarchical'])) $args['show_ui'] = true;

                        /* post type has archive */
                        $args['has_archive'] = isset($optionGroup[$this->postType . '_has_archive']);
                    }
                }

                /* supports */
                if (isset($config['supports'])) {
                    $args['supports'] = oes_cast_to_array($config['supports']);
                } else {
                    $args['supports'] = $args['hierarchical'] ? ['title', 'page-attributes'] : ['title'];
                }

                /* taxonomies */
                $args['taxonomies'] = isset($config['taxonomies']) ? oes_cast_to_array($config['taxonomies']) : [];

                /* configure added taxonomy, overwrites config values */
                if ($this->overwriteConfigFile) {
                    $optionGroup = get_option(C\Option::POST_TYPE_X_TAXONOMY);
                    if (isset($optionGroup)) {

                        $args['taxonomies'] = [];

                        /* loop through registered taxonomies */
                        $allTaxonomies = get_taxonomies(['_builtin' => false], 'objects');
                        foreach ($allTaxonomies as $taxonomyID => $taxonomy) {

                            /* add to post */
                            if (isset($optionGroup[$this->postType . '_' . $taxonomyID])) {
                                $args['taxonomies'][] = $taxonomyID;
                            }
                        }
                    }
                }

                /* TODO @2.0 Roadmap : taxonomies for master posts */
                $argsMaster['taxonomies']= [];

                /* configure added taxonomy for Master, overwrites config values */
                if ($this->overwriteConfigFile) {
                    $optionGroup = get_option(C\Option::POST_TYPE_X_TAXONOMY);
                    if (isset($optionGroup)) {

                        $argsMaster['taxonomies'] = [];
                        /* loop through registered taxonomies */
                        $allTaxonomies = get_taxonomies(['_builtin' => false], 'objects');
                        foreach ($allTaxonomies as $taxonomyID => $taxonomy) {

                            /* add to post */
                            if (isset($optionGroup[$this->postTypeMaster . '_' . $taxonomyID])) {
                                $argsMaster['taxonomies'][] = $taxonomyID;
                            }
                        }
                    }
                }

                /* rewrite */
                if (isset($config['rewrite'])) {
                    foreach (['slug', 'with_front', 'feeds', 'pages', 'ep_mask'] as $key) {
                        if (isset($config['rewrite'][$key])) {
                            $args['rewrite'][$key] = $config['rewrite'][$key];
                        }
                    }
                }

                /* capabilities */
                $argsRewrite = ['read', 'publish_posts', 'edit_posts', 'edit_published_posts', 'delete_published_posts',
                    'edit_others_posts', 'delete_others_posts'];

                foreach ($argsRewrite as $key) {
                    $args['capabilities'][$key] =
                        isset($config['capabilities'][$key]) ? $config['capabilities'][$key] : $key;
                }

                $this->args = $args;
                $this->argsMaster = array_merge($args, $argsMaster);

            }


            /* ---- set acf fields -----------------------------------------------------------------------------------*/
            if (isset($config[C\ACF::FIELD_GROUP_FIELDS])) {
                $this->fields = oes_cast_to_array($config[C\ACF::FIELD_GROUP_FIELDS]);
            }
            $this->fields = array_merge([], $this->fields);


            /* ---- set acf fields for master post type --------------------------------------------------------------*/
            if ($this->versioning) {
                if (isset($config[C\ACF::FIELD_GROUP_FIELDS_MASTER])) {
                    $this->fieldsMaster = oes_cast_to_array($config[C\ACF::FIELD_GROUP_FIELDS_MASTER]);
                }
                $this->fieldsMaster = array_merge([], $this->fieldsMaster);
            }


            /* ---- set additional fields ----------------------------------------------------------------------------*/
            //Not needed.


            /* ---- set field group settings -------------------------------------------------------------------------*/

            /* key */
            $this->fieldGroup['key'] =
                isset($config[C\ACF::FORM_ID]) ? $config[C\ACF::FORM_ID] : 'oes_' . $this->postType;

            /* title */
            $this->fieldGroup['title'] =
                isset($config[C\ACF::FIELD_GROUP_TITLE]) ?
                    isset($config[C\ACF::FIELD_GROUP_TITLE]) :
                    (isset($this->args['labels']['singular_name']) ?
                        $this->args['labels']['singular_name'] :
                        'oes missing args');

            /* get defaults */
            $this->fieldGroup =
                ACF\Field_Group::field_group_defaults(
                    $this->fieldGroup['key'],
                    $this->fieldGroup['title']);

            /* group location */
            if (isset($config[C\ACF::FIELD_GROUP_LOCATION])) {
                $this->fieldGroup['location'] = $config[C\ACF::FIELD_GROUP_LOCATION];
            }

            /* menu order */
            if (isset($config[C\ACF::FIELD_GROUP_MENU_ORDER])) {
                $this->fieldGroup['menu_order'] = $config[C\ACF::FIELD_GROUP_MENU_ORDER];
            }

            /* hide on screen */
            if (isset($config[C\ACF::FIELD_GROUP_HIDE_ON_SCREEN])) {
                $this->fieldGroup['hide_on_screen'] = $config[C\ACF::FIELD_GROUP_HIDE_ON_SCREEN];
            }

            self::validate_location();
            self::validate_hide_on_screen();


            /* ---- set field group master settings ------------------------------------------------------------------*/
            if ($this->versioning) {

                $this->fieldGroupMaster['key'] = $this->fieldGroup['key'] . '_master';
                $this->fieldGroupMaster['title'] = $this->fieldGroup['title'] . ' Master';

                /* get defaults */
                $this->fieldGroupMaster =
                    ACF\Field_Group::field_group_defaults(
                        $this->fieldGroupMaster['key'],
                        $this->fieldGroupMaster['title']);

                $this->fieldGroupMaster['location'] = ACF\Field_Group::buffer_location('post_type', $this->postTypeMaster);

                $this->fieldGroupMaster['hide_on_screen'] = [0 => 'the_content'];
            }


            /* ---- set fields to be inherited from master to child --------------------------------------------------*/
            if ($this->versioning) {
                if (isset($config[C\Post_Type::VERSION_INHERIT_FIELDS])) {
                    $this->inheritFields = $config[C\Post_Type::VERSION_INHERIT_FIELDS];
                }
                if (isset($config[C\Post_Type::VERSION_INHERIT_TERMS])) {
                    $this->inheritTerms = $config[C\Post_Type::VERSION_INHERIT_TERMS];
                }
            }

        }


        /**
         * Validate location and set class variable location for given field.
         */
        private function validate_location()
        {
            if (empty($this->fieldGroup['location'])) {
                if ($this->isImage) $this->fieldGroup['location'] =
                    ACF\Field_Group::buffer_location('attachment', 'image');
                else if ($this->isAttachment) $this->fieldGroup['location'] =
                    ACF\Field_Group::buffer_location('attachment', ['application', 'text']);
                else $this->fieldGroup['location'] =
                    ACF\Field_Group::buffer_location('post_type', $this->postType);
            }
        }


        /**
         * Validate hide on screen flag and set class variable hide on screen for given field.
         */
        private function validate_hide_on_screen()
        {
            if (empty($this->fieldGroup['hide_on_screen'])) {
                if ($this->postType == 'page') $this->fieldGroup['hide_on_screen'] = [0 => ''];
                else $this->fieldGroup['hide_on_screen'] = [0 => 'the_content'];
            }
        }


        /**
         * Validate translating languages.
         * Each language should to be an array with at least one, preferable two items. The first item containing the
         * label for the language the second containing the language identifier.
         */
        private function validate_translating_languages()
        {
            if($this->translating){
                if($this->languages){

                    /* primary language */
                    if(!isset($this->languages['primary']) || !isset($this->languages['primary']['label'])){
                        $this->languages['primary'] = C\Post_Type::DEFAULT_PRIMARY_LANGUAGE;
                    }
                    elseif(!isset($this->languages['primary']['identifier'])){
                        $this->languages['primary']['identifier'] = $this->languages['primary']['label'];
                    }

                    /* secondary language */
                    if(!isset($this->languages['secondary']) || !isset($this->languages['secondary']['label'])){
                        $this->languages['secondary'] = C\Post_Type::DEFAULT_PRIMARY_LANGUAGE;
                    }
                    elseif(!isset($this->languages['secondary']['identifier'])){
                        $this->languages['secondary']['identifier'] = $this->languages['secondary']['label'];
                    }

                }
                else{
                    //TODO @2.0 add notice: language not defined
                }
            }
            else{
                //TODO @2.0 add notice: language defined but translating not enabled!
            }
            if (empty($this->fieldGroup['hide_on_screen'])) {
                if ($this->postType == 'page') $this->fieldGroup['hide_on_screen'] = [0 => ''];
                else $this->fieldGroup['hide_on_screen'] = [0 => 'the_content'];
            }
        }


        /**
         * Register post type and add acf fields.
         *
         * @throws Exception
         */
        public function oes_register_post_type()
        {
            global $oes;
            try {
                if ($this->registerPostType && !$this->isAttachment) {

                    register_post_type($this->postType, $this->args);

                    $builder = new ACF\Field_Group($this->fieldGroup, $this->fields);
                    $builder->oes_acf_add_local_field_group();

                    $oes->postTypes[$this->postType]['initialized'] = true;

                    /* include versioning */
                    if ($this->versioning) {

                        /* register master post type */
                        register_post_type($this->postTypeMaster, $this->argsMaster);

                        $builderMaster = new ACF\Field_Group($this->fieldGroupMaster, $this->fieldsMaster);
                        $builderMaster->oes_acf_add_local_field_group();

                        /* add post type to global variable */
                        if(!isset($oes->general_configurations['versioning'])){
                            $oes->general_configurations['versioning'] = true;
                        }
                        $oes->postTypes[$this->postType]['version_controlled_by'] = $this->postTypeMaster;
                        $oes->postTypes[$this->postTypeMaster]['initialized'] = true;
                        $oes->postTypes[$this->postTypeMaster]['version_controlling'][] = $this->postType;
                        $oes->postTypes[$this->postTypeMaster]['inherited_fields'] = $this->inheritFields;
                        $oes->postTypes[$this->postTypeMaster]['inherited_terms'] = $this->inheritTerms;

                    }

                    /* include translating */
                    if($this->translating){
                        if(!isset($oes->general_configurations['translating'])){
                            $oes->general_configurations['translating'] = true;
                        }
                        $oes->postTypes[$this->postType]['translating'] = true;
                        $oes->postTypes[$this->postType]['translating_languages'] = $this->languages;
                    }
                }
            } catch (Exception $e) {
                throw new Exception($e);
            }
        }
    }

endif;