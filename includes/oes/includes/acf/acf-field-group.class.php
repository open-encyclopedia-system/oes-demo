<?php

namespace OES\ACF;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Field_Group')) :


    /**
     * Class Field_Group
     *
     * Prepare a field group with fields for the acf function acf_add_local_field_group.
     * This will prepare the fields displayed inside the post type.
     */
    class Field_Group
    {


        /** @var array An array containing the field group data. */
        public $fieldGroup = [];


        /** Constant containing all field group parameters and defaults. */
        const FIELD_GROUP = [
            'key' => ['key', 'key_missing'],
            'title' => ['title', 'Title missing'],
            'fields' => ['fields', []],
            'location' => ['location', []],
            'menu_order' => ['menu_order', 0],
            'position' => ['position', 'normal'],
            'style' => ['style', 'default'],
            'label_placement' => ['label_placement', 'top'],
            'instruction_placement' => ['instruction_placement', 'label'],
            'hide_on_screen' => ['hide_on_screen', '']
        ];


        /**
         * Field_Group constructor.
         * @param $fieldGroupList
         * @param $fieldsList
         * @param string $keyPrefix
         */
        public function __construct($fieldGroupList, $fieldsList, $keyPrefix = '')
        {
            /* buffer fields */
            $bufferedFields = self::buffer_fields($fieldsList, $keyPrefix);
            $bufferedFieldGroup = self::buffer_field_group($fieldGroupList);
            $bufferedFieldGroup['fields'] = $bufferedFields;

            /* validate and set as class parameters */
            $this->set_field_group($bufferedFieldGroup);
            $this->validate_field_group();
        }


        /**
         * Call acf function acf_add_local_field_group with class field group.
         */
        public function oes_acf_add_local_field_group()
        {
            acf_add_local_field_group($this->fieldGroup);
        }


        /**
         * Buffer fields with defaults and modify keys if necessary.
         *
         * @param array $fieldsList An array containing fields.
         * @param string $keyPrefix A string containing a key prefix.
         * @param false $parentFieldKey A boolean identifying if field has a parent field key.
         * @return array Return array with buffered fields.
         */
        public static function buffer_fields($fieldsList, $keyPrefix = '', $parentFieldKey = false)
        {
            $bufferedFields = [];

            if (!empty($fieldsList)) {
                foreach ($fieldsList as $key => $field) {

                    /* get required key field parameters */
                    $name = isset($field['name']) ? $field['name'] : null;
                    $label = isset($field['label']) ? $field['label'] : null;
                    $type = isset($field['type']) ? $field['type'] : null;
                    $keyTemp = isset($field['key']) ? $field['key'] : null;

                    /* buffer key field parameters for empty parameters */
                    if (!empty($keyTemp)) $key = $keyTemp;
                    else if (is_numeric($key)) $key = $name;
                    if (empty($name)) $name = $key;
                    if (empty($label)) $label = ucfirst($name);
                    if (empty($type)) $type = 'text';//TODO @2.0 Roadmap : warning or skip field if type not recognized?

                    /* modify key */
                    if ($parentFieldKey) {
                        $key = $parentFieldKey . '_' . $key;
                        $field['key'] = $key;
                    } else {
                        $key = $keyPrefix . $key;
                    }

                    /* buffer field parameters with defaults */
                    $args = ['key' => $key, 'label' => $label, 'name' => $name, 'type' => $type];
                    $fieldDefaultParameters = self::field_defaults($args);


                    /* add additional parameters from input and merge with defaults */
                    $singleBufferedField = array_merge($fieldDefaultParameters, $field);

                    /* modify keys for conditional logic */
                    if (isset($singleBufferedField['conditional_logic'])) {
                        if (is_array($singleBufferedField['conditional_logic'])) {
                            foreach ($singleBufferedField['conditional_logic'] as $conditionKey => $condition) {
                                if (isset($condition[0]['field'])) {
                                    $singleBufferedField['conditional_logic'][$conditionKey][0]['field'] =
                                        $keyPrefix . $condition[0]['field'];
                                }
                            }
                        }
                    }

                    /* ACF PRO : loop through sub fields */
                    if (isset($singleBufferedField['sub_fields'])) {
                        $subFieldsList = $singleBufferedField['sub_fields'];
                        $subFieldsList = self::buffer_fields($subFieldsList, $keyPrefix, $key);
                        $singleBufferedField['sub_fields'] = $subFieldsList;
                    }

                    /* loop through layouts */
                    if (isset($singleBufferedField['layouts'])) {
                        $layouts = $singleBufferedField['layouts'];
                        $layouts = self::buffer_fields($layouts, $keyPrefix, $key);
                        $singleBufferedField['layout'] = $layouts;
                    }

                    /* add to array */
                    $bufferedFields[$key] = $singleBufferedField;
                }
            }
            return $bufferedFields;
        }


        /**
         * Set field type default parameters for oes fields.
         *
         * TODO @2.0 Roadmap : prepare function for setting defaults for custom fields
         *
         * @param array $args An array containing the field parameters.
         * @return mixed Return array containing field parameters.
         */
        public static function field_defaults($args)
        {
            /* set type default parameters for oes fields */
            switch ($args['type']) {
                case 'oes_custom_field':
                    break;

            }

            return $args;
        }


        /**
         * Set field group defaults for field group.
         *
         * @param array $fieldGroup An array containing the field group parameters.
         * @return array Return buffered field group.
         */
        public static function buffer_field_group($fieldGroup)
        {
            $bufferedFieldGroup = [];

            /* set custom field or get default */
            foreach (self::FIELD_GROUP as $key => [$custLabel, $default]) {
                $bufferedFieldGroup[$key] = isset($fieldGroup[$custLabel]) ? $fieldGroup[$custLabel] : $default;
            }

            return $bufferedFieldGroup;
        }


        /**
         * Validate and set class parameters.
         *
         * @param array $fieldGroup An array containing the field group parameters.
         */
        private function set_field_group($fieldGroup)
        {
            $this->fieldGroup = $fieldGroup;

            /* set field group fields */
            foreach ($fieldGroup['fields'] as $key => $field) {
                $this->fieldGroup['fields'][$key] = $field;
            }

            $this->validate_field_group();
        }


        /**
         * Create field group defaults from key and title and set as class parameter.
         *
         * @param string $fieldGroupKey A string containing the field group key.
         * @param string $fieldGroupTitle A string containing the field group title.
         * @return array Return field group.
         */
        public static function field_group_defaults($fieldGroupKey, $fieldGroupTitle)
        {
            $fieldGroup['key'] = $fieldGroupKey;
            $fieldGroup['title'] = $fieldGroupTitle;
            $fieldGroup = self::buffer_field_group($fieldGroup);
            return $fieldGroup;
        }


        /**
         * Buffer field group location parameters.
         *
         * @param string $param A string containing the location parameter.
         * @param string $value A string containing the location value.
         * @return array|array[][] Return location array.
         */
        public static function buffer_location($param, $value)
        {

            $returnArray = [];

            if (isset($value)) {

                /* multiple values */
                if (is_array($value)) {
                    foreach ($value as $singleValue) {
                        $returnArray[] = [[
                            'param' => $param,
                            'operator' => '==',
                            'value' => $singleValue,
                        ],];
                    }
                } /* single value */
                else {
                    $returnArray[] = [[
                        'param' => $param,
                        'operator' => '==',
                        'value' => $value,
                    ],];
                }

            } else $returnArray = [[[]]];

            return $returnArray;
        }


        /**
         * Validate field group parameter.
         */
        private function validate_field_group()
        {

            /* key : check if key already exists */
            $this->fieldGroup['key'] =
                isset($this->fieldGroup['key']) ? $this->fieldGroup['key'] : 'field_group_key_missing';

            /* title */
            $this->fieldGroup['title'] =
                isset($this->fieldGroup['title']) ? $this->fieldGroup['title'] : 'field group title still missing';

            /* fields */
            foreach ($this->fieldGroup['fields'] as $key => $field) {

                $defaults = [
                    'name' => 'name is missing',
                    'label' => 'label is missing',
                    'text' => 'text'
                ];

                /* merge with defaults */
                $this->fieldGroup['fields'][$key] = array_merge($defaults, $this->fieldGroup['fields'][$key]);

            }
        }
    }

endif;
