<?php

namespace OES\ACF;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Bidirectional_Relationship')) :

    /**
     * Class Bidirectional_Relationship
     *
     * Creating or updating bidirectional relationships.
     * (Modified from open source WordPress plugin 'ACF Post-2-Post' by John A. Huebner,
     * https://github.com/Hube2/acf-post2post)
     */
    class Bidirectional_Relationship
    {

        /** @var array Containing rules for overwriting relationships */
        var $overwrite = [];

        /** @var array Containing self references for post type that will be evaluated after update */
        var $afterProcessing = [];


        /**
         * Bidirectional_Relationship constructor.
         */
        public function __construct()
        {
            add_action('after_setup_theme', [$this, 'acf_add_filter']);
        }


        /**
         * Add a overwrite rule to processing. Determines if values can be overwritten by bidirectional relationship.
         * Add a new rule by calling:
         * oes()->acf_birelationship->add_overwrite_rule($fieldKey, $overwrite, $type).
         *
         * @param string $fieldKey A string containing the field key.
         * @param bool $overwrite Optional boolean if values can be overwritten. Default is true.
         * @param string $type Optional string if first or last value should be overwritten. Valid options are 'first'
         * and 'last'. Default is 'first'.
         */
        public function add_overwrite_rule($fieldKey, $overwrite = true, $type = 'first')
        {
            if (!in_array($type, ['first', 'last'])) $type = 'first';
            $this->overwrite[$fieldKey] = [
                'overwrite' => $overwrite,
                'type' => $type
            ];
        }


        /**
         * Add callbacks to filter.
         */
        public function acf_add_filter()
        {
            add_filter('acf/update_value/type=relationship', [$this, 'update_relationship_field'], 11, 3);
            add_filter('acf/update_value/type=post_object', [$this, 'update_relationship_field'], 11, 3);

            /* force update of self reference fields of affected post */
            add_action('save_post', [$this, 'add_self_references']);
        }


        /**
         * Update post by evaluating the self reference fields stored in class variable.
         *
         * @param string $post_id A string containing the post id.
         */
        public function add_self_references($post_id){

            foreach($this->afterProcessing as $fieldID => $newValueArrays){

                /* get current value */
                $currentValues = get_acf_field($fieldID, $post_id);
                if(!is_array($currentValues)) $currentValues = [];

                /* prepare new values */
                $collectValues = [];
                foreach($currentValues as $singleValue) $collectValues[] = strval($singleValue->ID);

                /* remove values */
                $removeValues = isset($newValueArrays['remove']) ? $newValueArrays['remove'] : [];
                if(!empty($removeValues)){
                    $temp = [];
                    foreach($collectValues as $loopValue){
                        if (!in_array($loopValue, $removeValues)) $temp[] = $loopValue;
                    }
                    $collectValues = $temp;
                }

                /* add values */
                $addValues = isset($newValueArrays['add']) ? $newValueArrays['add'] : [];
                $collectValues = array_merge($collectValues, $addValues);

                /* update field */
                update_field($fieldID, $collectValues);
            }
        }


        /**
         * Updates a relationship field. Called by 'acf/update_value'
         *
         * @param array $value An array passed from filter containing the processed value for the post.
         * @param int $post_id An integer passed from filter containing the post id.
         * @param array $field An array passed from filter containing the processed field.
         * @return mixed Returns processed value if successful.
         */
        public function update_relationship_field($value, $post_id, $field)
        {

            /* 1. get the value after all hooked function have been applied, return if no value ----------------------*/

            /* 2. get current value ----------------------------------------------------------------------------------*/
            $fieldName = $field['key'];
            $currentValue = maybe_unserialize(get_post_meta($post_id, $fieldName, true));
            /* make sure that the value is an integer array */
            if ($currentValue === '') $currentValue = [];
            if (!is_array($currentValue)) $currentValue = [$currentValue];
            $currentValue = array_map('intval', $currentValue);

            /* 3. modify new value -----------------------------------------------------------------------------------*/
            $newValue = $value;
            if (!$newValue) $newValue = [];
            if (!is_array($newValue)) $newValue = [$newValue];

            /* 4. prepare for store updated post ---------------------------------------------------------------------*/

            /* 5. remove current obsolete relationships --------------------------------------------------------------*/
            if (count($currentValue)) {
                foreach ($currentValue as $relatedPostId) {

                    /* don't remove relationship if part of new value */
                    if (!in_array($relatedPostId, $newValue)) {

                        /* get matching fields in other post types */
                        $matchingFields = $this->get_matching_fields(get_post_type($post_id),
                            get_post_type($relatedPostId), $fieldName);

                        /* remove relationship for each matching field */
                        if ($matchingFields) {
                            foreach ($matchingFields as $matchingField) {
                                $this->remove_relationship($relatedPostId, $matchingField['key'], $post_id);
                            }
                        }

                        /* prepare self reference for after processing */
                        $this->prepare_self_reference(get_post_type($post_id),
                            get_post_type($relatedPostId), $fieldName, $relatedPostId, 'remove');
                    }
                }
            }

            /* 6. add new relationships ------------------------------------------------------------------------------*/
            if (count($newValue)) {

                foreach ($newValue as $relatedPostId) {

                    /* get matching fields in other post types */
                    $matchingFields = $this->get_matching_fields(get_post_type($post_id),
                        get_post_type($relatedPostId), $fieldName);

                    /* add relationship for each matching field */
                    if ($matchingFields) {
                        foreach ($matchingFields as $matchingField) {
                           $this->add_relationship($relatedPostId, $matchingField['key'], $post_id);
                        }
                    }

                    /* prepare self reference for after processing */
                    $this->prepare_self_reference(get_post_type($post_id),
                        get_post_type($relatedPostId), $fieldName, $relatedPostId, 'add');

                }
            }

            /* 7. return updated value -------------------------------------------------------------------------------*/
            return $value;
        }


        /**
         * Returns array with post type fields that are relationship fields and match the source post type.
         *
         * @param String $sourcePostType A string containing the source post type.
         * @param String $targetPostType A string containing the target post type.
         * @param String $sourceField A string containing the source field key.
         * @return array Returns an array of fields.
         */
        private function get_matching_fields($sourcePostType, $targetPostType, $sourceField)
        {
            $returnFields = [];

            /* get post type fields */
            $postFields = get_all_post_type_fields($targetPostType, false);

            /* loop through fields of target post type */
            foreach ($postFields as $field) {

                /* check for relationship field */
                if ($field['type'] == 'relationship') {

                    /* check if same post type */
                    if (in_array($sourcePostType, $field['post_type'])) {

                        /* get configuration from option page */
                        $optionName = $sourceField . '_x_' . $field['key'];
                        $relationships = get_option('oes_post_types_relations');

                        /* add field anyway if option does not exist */
                        if (!$relationships) $returnFields[] = $field;

                        if (isset($relationships[$optionName])) {
                            /* add field if option is checked */
                            if ($relationships[$optionName]) $returnFields[] = $field;
                        }
                    }
                }
            }

            return $returnFields;
        }


        /**
         * Prepare after processing by storing field connections inside the source post type that target the same
         * post type.
         *
         * @param String $sourcePostType A string containing the source post type.
         * @param String $targetPostType A string containing the target post type.
         * @param String $sourceField A string containing the source field key.
         * @param array $value An array containing the value for the source field.
         * @param String $operationType A string containing the operation type. Valid values are 'add', 'remove'.
         */
        private function prepare_self_reference($sourcePostType, $targetPostType, $sourceField, $value, $operationType)
        {

            /* get fields from source post type */
            $postFields = get_all_post_type_fields($sourcePostType, false);

            /* loop through fields of source post type */
            foreach ($postFields as $field) {

                /* check for relationship field exclude source field */
                if ($field['type'] == 'relationship' && $field['key'] != $sourceField) {

                    /* check if same post type as connected post type */
                    if (in_array($targetPostType, $field['post_type'])) {

                        /* get configuration from option page */
                        $optionName = $sourceField . '_x_' . $field['key'];
                        $relationships = get_option('oes_post_types_relations');

                        /* add field anyway if option does not exist */
                        if (!$relationships) $returnFields[] = $field;

                        if (isset($relationships[$optionName])) {
                            /* add field if option is checked */
                            if ($relationships[$optionName]) {
                                /* prepare after processing */
                                $this->afterProcessing[$field['key']][$operationType][] = $value;
                            }
                        }
                    }
                }
            }
        }


        /**
         * Remove a relationship from a post.
         *
         * @param int $targetPostId Int containing the post id to remove the relationship from.
         * @param string $fieldName A string containing the field name to be update.
         * @param int $postIdToBeRemoved An int of the post relationship to be removed.
         */
        private function remove_relationship($targetPostId, $fieldName, $postIdToBeRemoved)
        {

            /* 1. get current value of related post ------------------------------------------------------------------*/
            $field = $this->get_field($targetPostId, $fieldName);
            /* check if value is an array */
            $fieldValueIsArray = true;
            if (isset($field['type'])) {
                if ($field['type'] == 'post_object') {
                    if (!$field['multiple']) $fieldValueIsArray = false;
                }
            }

            /* make sure that current value is an integer array */
            $currentValues = maybe_unserialize(get_post_meta($targetPostId, $fieldName, true));
            if ($currentValues === '') $currentValues = [];
            if (!is_array($currentValues)) $currentValues = [$currentValues];
            if (!count($currentValues)) return; // nothing to delete
            $currentValues = array_map('intval', $currentValues);

            /* 2. get new value of related post and prepare for deletion ---------------------------------------------*/
            $newValues = [];
            foreach ($currentValues as $value) {
                /* prepare deletion of relationship if current value is not part of the related post id */
                if ($value != $postIdToBeRemoved) $newValues[] = $value;
            }

            /* make sure that value has the correct format */
            if (!count($newValues) && !$fieldValueIsArray) $newValues = '';
            elseif (!$fieldValueIsArray) $newValues = $newValues[0];
            elseif (count($newValues)) $newValues = array_map('strval', $newValues);

            /* 3. update the post meta data by deleting the relationship ---------------------------------------------*/
            update_post_meta($targetPostId, $fieldName, $newValues);
            update_post_meta($targetPostId, '_' . $fieldName, $field['key']);
        }


        /**
         * Add a new relationship to a post.
         *
         * @param int $targetPostId Int containing the post id to add the relationship to.
         * @param string $fieldName A string containing the field name to be update.
         * @param int $postIdToBeAdded An int of the post relationship to be added.
         */
        private function add_relationship($targetPostId, $fieldName, $postIdToBeAdded)
        {

            /* 1. get current value of related post ------------------------------------------------------------------*/
            $field = $this->get_field($targetPostId, $fieldName);
            if (!$field) return;// field not found attached to this post

            /* make sure that current value is an integer array */
            $value = maybe_unserialize(get_post_meta($targetPostId, $fieldName, true));
            if ($value == '') $value = [];
            if (!is_array($value)) $value = [$value];
            $value = array_map('intval', $value);

            /* 2. add relationship if maximum post amount not exceeded -----------------------------------------------*/
            $maxPosts = $this->get_max_post($field);
            $isArrayValue = $this->get_array_value($field);
            if (($maxPosts == 0 || count($value) < $maxPosts) && !in_array($postIdToBeAdded, $value)) {
                $value[] = $postIdToBeAdded;
            } /* 3. determine if relationships should be overwritten -------------------------------------------------*/
            elseif ($maxPosts > 0) {

                if (isset($this->overwrite[$fieldName])) {
                    if ($this->overwrite[$fieldName]['overwrite']) {

                        /* remove first entry */
                        if ($this->overwrite[$fieldName]['type'] == 'first') {
                            $remove = array_shift($value);
                        } /* remove last entry */
                        else $remove = array_pop($value);

                        /* remove this relationship from post */
                        $this->remove_relationship(intval($remove), $fieldName, $targetPostId);
                        $value[] = $postIdToBeAdded;
                    }
                }
            }

            /* make sure that current value is a string array */
            if (!$isArrayValue) $value = $value[0];
            else $value = array_map('strval', $value);

            /* 4. update the post meta data by deleting the relationship ---------------------------------------------*/
            update_post_meta($targetPostId, $fieldName, $value);
            update_post_meta($targetPostId, '_' . $fieldName, $field['key']);
        }


        /**
         * Return maximum post amount fo relationship field. The maximum post number for post objects is 1.
         *
         * @param array $field An array containing an acf field.
         * @return int Return maximum post number.
         */
        private function get_max_post($field)
        {
            if ($field['type'] == 'post_object') {
                if (!$field['multiple']) return 1;
            } elseif ($field['type'] == 'relationship') {
                if ($field['max']) return $field['max'];
            }

            /* return default*/
            return false;
        }


        /**
         * Return if field has array value for relationship field.
         *
         * @param array $field An array containing an acf field.
         * @return boolean Return if field has array value.
         */
        private function get_array_value($field)
        {
            if ($field['type'] == 'post_object') {
                if (!$field['multiple']) return false;
            }
            return true;
        }


        /**
         * Get acf field from post with post id and field name from cache. If it does not exist, add to cache.
         *
         * @param int $postId An integer containing the post id.
         * @param string $fieldKey A string containing the field key.
         * @return array Returns modified acf field as added to cache.
         */
        public function get_field($postId, $fieldKey)
        {

            /* 1. check if field already in cache --------------------------------------------------------------------*/
            $inCache = false;
            $cache_key = 'get_field-' . $postId . '-' . $fieldKey;
            $cache = wp_cache_get($cache_key, 'acf-bi-relationships', false, $inCache);
            if ($inCache) return $cache;

            /* 2. get field group ------------------------------------------------------------------------------------*/
            $field = false;
            $acfFieldGroups = $this->post_field_groups($postId);

            /* loop through field groups */
            foreach ($acfFieldGroups as $acfFieldGroup) {

                /* loop through fields */
                foreach ($acfFieldGroup['fields'] as $acfField) {

                    /* check if searched field and if relationship field */
                    if ($acfField['key'] == $fieldKey && in_array($acfField['type'], ['relationship', 'post_object'])) {
                        $field = $acfField;
                        break 2;
                    }
                }
            }

            /* 3. update cache ---------------------------------------------------------------------------------------*/
            wp_cache_set($cache_key, $field, 'acf-bi-relationships');

            /* 4. return field ---------------------------------------------------------------------------------------*/
            return $field;
        }


        /**
         * Get acf field group from post with post id from cache. If it does not exist, add to cache.
         *
         * @param int $postId An integer containing the post id.
         * @return array Returns modified acf field group as added to cache.
         */
        public function post_field_groups($postId)
        {

            /* 1. check if field already in cache --------------------------------------------------------------------*/
            $inCache = false;
            $cache = wp_cache_get('post_field_groups-' . $postId, 'acf-bi-relationships', false, $inCache);
            if ($inCache) return $cache;

            /* 2. prepare field group to add to cache-----------------------------------------------------------------*/
            $acfFieldGroups = acf_get_field_groups(['post_id' => $postId]);
            foreach ($acfFieldGroups as $key => $acfFieldGroup) {
                $acfFieldGroups[$key]['fields'] = acf_get_fields($acfFieldGroups[$key]['key']);
            }

            /* 3. update cache ---------------------------------------------------------------------------------------*/
            wp_cache_set('post_field_groups-' . $postId, $acfFieldGroups, 'acf-bi-relationships');

            /* 4. return field group ---------------------------------------------------------------------------------*/
            return $acfFieldGroups;
        }
    }


    /* instantiate */
    OES()->acfBiRelationship = new Bidirectional_Relationship();

endif;