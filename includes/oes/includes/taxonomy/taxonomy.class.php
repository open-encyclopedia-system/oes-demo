<?php

namespace OES\Taxonomy;

use OES\ACF as ACF;
use OES\Config as C;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Taxonomy')) :

    /**
     * Class Taxonomy
     *
     * creating or modifying a taxonomy object based on the parameters given
     */
    class Taxonomy
    {

        /** @var string|bool String containing taxonomy name */
        var $taxonomy = null;

        /** @var array Array of object types with which the taxonomy should be associated */
        var $objectType = [];

        /** @var array Array containing taxonomy parameters */
        var $args = [];

        /** @var array Array containing field group for taxonomy */
        var $fieldGroup = [];

        /** @var array Array containing fields for taxonomy */
        var $fields = [];


        /**
         * Taxonomy constructor.
         *
         * @param string $configFile An array containing the config file with configurations for taxonomy.
         * @throws Exception
         */
        function __construct($configFile)
        {
            /* get configuration from option page --------------------------------------------------------------------*/
            $source = get_option(C\Option::POST_TYPE_GENERAL);
            if (isset($source['data_source'])) {
                if ($source['data_source'] == 'panel') $this->overwriteConfigFile = true;
            }

            /* include file and get parameters -----------------------------------------------------------------------*/
            oes_validate_file($configFile);
            include($configFile);

            /* get config parameter */
            $config = isset($config) ? $config : [];

            /* set key -----------------------------------------------------------------------------------------------*/
            $taxonomyKeyTemp = $config[C\Taxonomy::TAXONOMY];

            /* TODO @2.0 Roadmap : bail early if taxonomy already exists */
            if (taxonomy_exists($taxonomyKeyTemp)) {
            } else {

                /* taxonomy must start with 'oes_' */
                if (!oes_starts_with($taxonomyKeyTemp, 'oes_')) $taxonomyKeyTemp = 'oes_' . $taxonomyKeyTemp;

                /* taxonomy can not be longer than 32 characters */
                if (strlen($taxonomyKeyTemp) > 32) $taxonomyKeyTemp = substr($taxonomyKeyTemp, 0, 32);

            }
            $this->taxonomy = $taxonomyKeyTemp;

            /* set taxonomy args -------------------------------------------------------------------------------------*/

            /* general parameters */
            foreach (C\Taxonomy::ARGS as $key => [$custLabel, $default]) {
                $args[$key] = isset($config[$custLabel]) ? $config[$custLabel] : $default;
            }

            /* labels */
            foreach (C\Taxonomy::ARGS_LABELS as $key => [$custLabel, $default]) {
                $args['labels'][$key] = isset($config['labels'][$custLabel]) ? $config['labels'][$custLabel] : $default;
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
                    (isset($config['labels']['singular_name']) ? $config['labels']['singular_name'] : 'name missing');

            /* configure display, overwrites config values */
            //TODO @2.0 Roadmap : more options for master
            if ($this->overwriteConfigFile) {
                $optionGroup = get_option(C\Option::POST_TYPE_X_TAXONOMY);
                if (isset($optionGroup)) {

                    /* change labels */
                    if (isset($optionGroup[$this->taxonomy . '_new_label'])) {

                        if ($optionGroup[$this->taxonomy . '_new_label'] != C\Option::TEXT_FIELD_DUMMY &&
                            !empty($optionGroup[$this->taxonomy . '_new_label'])) {
                            $labels = explode(';', $optionGroup[$this->taxonomy . '_new_label']);
                            if ($labels) {
                                $args['labels']['singular_name'] = isset($labels[1]) ? $labels[1] : $labels[0];
                                $args['labels']['name'] = $labels[0];
                                $args['labels']['menu_name'] = $labels[0];
                            }
                        }
                    }
                }
            }

            /* rewrite */
            if (isset($config['rewrite'])) {
                foreach (['slug', 'with_front', 'hierarchical', 'ep_mask'] as $key) {
                    if (isset($config['rewrite'][$key])) $args['rewrite'][$key] = $config['rewrite'][$key];
                }
            }

            /* capabilities */
            $argsCapabilities = ['manage_terms' => 'manage_categories', 'edit_terms' =>'manage_categories',
                'delete_terms' => 'read',
                'assign_terms' => 'edit_posts'];

            foreach ($argsCapabilities as $key => $default) {
                $args['capabilities'][$key] =
                    isset($config['capabilities'][$key]) ? $config['capabilities'][$key] : $default;
            }

            $this->args = array_merge([], $args);


            /* ---- set parameters for acf_add_local_field_group -----------------------------------------------------*/


            /* ---- set acf fields -----------------------------------------------------------------------------------*/
            if (isset($config[C\ACF::FIELD_GROUP_FIELDS])) {
                $this->fields = oes_cast_to_array($config[C\ACF::FIELD_GROUP_FIELDS]);
            }
            $this->fields = array_merge([], $this->fields);


            /* ---- set field group settings -------------------------------------------------------------------------*/

            /* key */
            $this->fieldGroup['key'] =
                isset($config[C\ACF::FORM_ID]) ? $config[C\ACF::FORM_ID] : 'oes_' . $this->taxonomy;

            /* title */
            $this->fieldGroup['title'] =
                isset($config[C\ACF::FIELD_GROUP_TITLE]) ?
                    isset($config[C\ACF::FIELD_GROUP_TITLE]) :
                    (isset($this->args['labels']['singular_name']) ?
                        $this->args['labels']['singular_name'] :
                        'oes missing args');

            /* get defaults */
            $this->fieldGroup =
                ACF\Field_Group::field_group_defaults($this->fieldGroup['key'], $this->fieldGroup['title']);

            /* group location */
            if (isset($config[C\ACF::FIELD_GROUP_LOCATION])) {
                $this->fieldGroup['location'] = $config[C\ACF::FIELD_GROUP_LOCATION];
            }

            /* menu order */
            if (isset($config[C\ACF::FIELD_GROUP_MENU_ORDER])) {
                $this->fieldGroup['menu_order'] = $config[C\ACF::FIELD_GROUP_MENU_ORDER];
            }

            self::oes_validate_location();

        }


        /**
         * Validate location and set class variable location for given field.
         */
        private function oes_validate_location()
        {
            if (empty($this->fieldGroup['location'])) {
                $this->fieldGroup['location'] = ACF\Field_Group::buffer_location('taxonomy', $this->taxonomy);
            }
        }


        /**
         * Add post type to object type class variable.
         *
         * @param string $postType A string containing post type name.
         */
        public function oes_add_post_type($postType)
        {
            $this->objectType[] = $postType;
        }


        /**
         * Register taxonomy and add acf fields.
         *
         * @throws Exception
         */
        public function oes_register_taxonomy()
        {
            try {
                register_taxonomy($this->taxonomy, $this->objectType, $this->args);

                $taxonomyBuilder = new ACF\Field_Group($this->fieldGroup, $this->fields);
                $taxonomyBuilder->oes_acf_add_local_field_group();

            } catch (Exception $e) {
                throw new Exception($e);
            }
        }

    }
endif;