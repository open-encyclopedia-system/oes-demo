<?php

use function OES\Versioning\get_master_post_type;

/**
 * Containing functions for processing post and post data.
 */


/**
 * Get the value of a global parameter for a OES post types.
 *
 * @param string $postType A string containing the OES post type name.
 * @param string $setting A string containing the settings key.
 * @param boolean $returnFirstValue A boolean identifying if only returning the first value of the result array.
 * Default is false.
 * @return string Return global parameter value.
 */
function oes_get_global_post_type_settings($postType, $setting, $returnFirstValue = false){

    global $oes;

    if(!is_string($postType) || !is_string($setting)){
        return false;
    }

    if($oes->postTypes[$postType][$setting]){
        if($returnFirstValue) return $oes->postTypes[$postType][$setting][0];
        else return $oes->postTypes[$postType][$setting];
    }
    return false;
}


/**
 * Get posts from database with WP_Query.
 *
 * @param array $args An array containing query parameter. Valid parameter are:
 *  'post_type'
 *  'post_status'
 *  'post_per_page'
 *  'meta_key'
 *  'meta_value'
 *  'meta_compare'.
 * See WordPress Documentation for more information for WP_Query.
 *
 * @return array Return results from WP_Query.
 */
function oes_get_wp_query_posts($args){

    /* prepare query */
    $queryArgs = [];
    if(isset($args['post_type'])) $queryArgs['post_type'] = $args['post_type'];
    if(isset($args['post_status'])) $queryArgs['post_status'] = $args['post_status'];
    $queryArgs['post_per_page'] = $args['posts_per_page'] ? $args['posts_per_page'] : -1;
    if(isset($args['meta_key'])){
        $value = $args['meta_value'] ? $args['meta_value'] : '';
        $compare = $args['meta_compare'] ? $args['meta_compare'] : '=';
        $queryArgs['meta_query'] = [['key' => $args['meta_key'], 'value' => $value, 'compare' => $compare]];
    }

    $query = new WP_Query($queryArgs);

    return $query->posts;
}


/**
 * Get post meta data for post.
 *
 * @param string $postID A string containing the post ID.
 * @param string $meta_key A string containing the meta key.
 * @param bool $single Whether to return a single value. This parameter has no effect if $key is not specified.
 *
 * @return mixed Return database value of meta key for the post ID.
 */
function oes_get_post_meta($postID, $meta_key, $single = false){
    return get_post_meta($postID, $meta_key, $single);
}


/**
 * This function will add or update post meta data.
 *
 * @param string|int $postID An int containing the post ID.
 * @param string $fieldName A string containing the name of the post meta field.
 * @param string $value A string containing the value for the post meta field.
 * TODO @2.0 Roadmap : include option to not replace value if already existing
 * @param string $delimiter A string containing an array delimiter if $value can be an array. If false, the value is
 * never split (eg text fields)
 */
function oes_update_post_meta($postID, $fieldName, $value = '', $delimiter = ",")
{
    /* delete if value is empty */
    if (empty($value) or !$value) delete_post_meta($postID, $fieldName);

    /* field does not yet exist */
    elseif (!get_post_meta($postID, $fieldName)) {
        $valueArray = is_array($value) ? $value : ($delimiter ? explode($delimiter, $value) : $value);
        if (is_array($valueArray)) {
            if (sizeof($valueArray) > 1) add_post_meta($postID, $fieldName, $valueArray);
            else add_post_meta($postID, $fieldName, $value);
        }
        else add_post_meta($postID, $fieldName, $value);
    } /* field already exists, update */
    else {
        $valueArray = is_array($value) ? $value : ($delimiter ? explode($delimiter, $value) : $value);
        if (is_array($valueArray)) {
            if (sizeof($valueArray) > 1) update_post_meta($postID, $fieldName, $valueArray);
            else update_post_meta($postID, $fieldName, $value);
        }
        else update_post_meta($postID, $fieldName, $value);
    }
}


/**
 * Get the display title for post. This depends on the option parameter from OES settings, the display title can be
 * different from the WordPress post title, e.g. any text acf field of the post type.
 *
 * @param false $postID A string containing the post ID.
 * @return string Returns a string containing the post title.
 */
function oes_get_display_title($postID = false){

    if(!$postID) $postID = get_the_ID();

    $title = null;

    /* get post type */
    $postType = get_post_type($postID);

    /* check if option is set */
    $themeOptions = get_option(\OES\Config\Option::THEME_TITLE . '-' . $postType . '-title');
    if($themeOptions && $themeOptions['post_title'] != 'wp_title'){
        $title = \OES\ACF\get_acf_field($themeOptions['post_title'], $postID);
    }

    return empty($title) ? get_the_title($postID) : $title;
}


/**
 * Get the list title for post. This depends on the option parameter from OES settings, the list title can be
 * different from the WordPress post title, e.g. any text acf field of the post type.
 *
 * @param false $postID A string containing the post ID.
 * @return string Returns a string containing the list title.
 */
function oes_get_list_title($postID = false){

    if(!$postID) $postID = get_the_ID();

    $title = null;

    /* get post type */
    $postType = get_post_type($postID);

    /* check if option is set */
    $themeOptions = get_option(\OES\Config\Option::THEME_TITLE . '-' . $postType . '-title');

    if($themeOptions){

        switch($themeOptions['list_title']){

            case 'wp_title':
                $title = get_the_title($postID);
                break;

            case 'default' :
                $title = oes_get_display_title($postID);
                break;

            default:
                $title = \OES\ACF\get_acf_field($themeOptions['list_title'], $postID);
                break;
        }
    }

    return empty($title) ? get_the_title($postID) : $title;
}


/**
 * Get the sorting title for post. This depends on the option parameter from OES settings, the display title can be
 * different from the WordPress post title, e.g. any text acf field of the post type.
 *
 * @param false $postID A string containing the post ID.
 * @return string Returns a string containing the sorting title.
 */
function oes_get_sorting_title($postID = false){

    if(!$postID) $postID = get_the_ID();

    $title = null;

    /* get post type */
    $postType = get_post_type($postID);

    /* check if option is set */
    $themeOptions = get_option(\OES\Config\Option::THEME_TITLE . '-' . $postType . '-title');

    if($themeOptions){

        switch($themeOptions['list_title_sorting']){

            case 'wp_title':
                $title = get_the_title($postID);
                break;

            case 'default' :
                $title = oes_get_display_title($postID);
                break;

            default:
                $title = \OES\ACF\get_acf_field($themeOptions['list_title_sorting'], $postID);
                break;
        }
    }

    return empty($title) ? get_the_title($postID) : $title;
}

/**
 * Sort array of post or terms by ascending title.
 *
 * @param array $postsArray An array containing the post or terms to be sorted.
 * @return array Returns sorted array.
 */
function oes_sort_post_array_by_title($postsArray)
{
    $sortedArray = [];
    if ($postsArray) {

        /* loop through array and store with title as key in sorted array */
        foreach ($postsArray as $post) {

            if ($post instanceof WP_Term) $postTitle = $post->name;
            else $postTitle = get_the_title($post->ID);

            $sortedArray[strtoupper($postTitle)] = $post;
        }

        /* sort by title */
        ksort($sortedArray);
    }

    return $sortedArray;
}


/**
 * Get html ul representation of array containing posts or terms.
 *
 * @param array $inputArray An array containing the list items.
 * @param boolean|string $id Optional string containing the list css id.
 * @param boolean|string $class Optional string containing the list css class.
 * @param boolean $permalink Optional boolean indicating if list item is link.
 * @param boolean $sort Optional boolean indicating if list should be sorted alphabetically by title.
 * @return string Return string containing a html ul list.
 */
function oes_display_post_array_as_list($inputArray, $id = false, $class = false, $permalink = true, $sort = true)
{
    /* bail if input array empty */
    if(!$inputArray) return '';

    /* prepare parameters for list display */
    $listItems = [];
    $sortedArray = [];

    /* sort array */
    if($sort){
        if (is_array($inputArray)) $sortedArray = oes_sort_post_array_by_title($inputArray);
        else $sortedArray = [$inputArray];
    }

    /* prepare items */
    foreach($sortedArray as $item){
        $title = ($item instanceof WP_Term) ? $item->name : get_the_title($item->ID);
        $itemText = $permalink ? oes_get_html_anchor($title, get_permalink($item->ID)) : $title;
        $listItems[] = $itemText;
    }

    /* return html representation */
    return oes_get_html_array_list($listItems, $id, $class);
}


/**
 * Get all OES post type including master post types.
 *
 * @return array Returns an array containing all OES post type objects.
 */
function oes_get_post_types(){

    $returnPostTypes = [];

    foreach(OES_Project_Config::POST_TYPE_ALL as $postType){

        /* add post type */
        $returnPostTypes[$postType] = get_post_type_object($postType);

        /* check for master */
        $masterPostType = get_master_post_type($postType);
        if($masterPostType){
            $returnPostTypes[$masterPostType] = get_post_type_object($masterPostType);
        }
    }

    return $returnPostTypes;
}


/**
 * Delete or trash a post.
 *
 * @param string|int $postID A string containing the post ID.
 * @param boolean|string $forceDelete A boolean indication if post is to be deleted and not trashed. Default is false.
 *
 * @return string|boolean|WP_Error|WP_Post Return error string or operation result.
 */
function oes_delete_post($postID, $forceDelete = false)
{
    /* check if post exists*/
    if (!get_post($postID)) return sprintf(__('Post ID (%s) is not found.', 'oes'), $postID);

    /* try to delete or trash post */
    return $forceDelete ? wp_delete_post($postID) : wp_trash_post($postID);
}


/**
 * Insert a post.
 *
 * @param array $parameters An array containing post arguments for wp_insert_post.
 * @param boolean $update A boolean identifying if a post will be updated if a post with the post ID parameter
 * already exist. Default is true.
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_post($parameters, $update = true)
{

    /* Validate post id ----------------------------------------------------------------------------------------------*/
    if ($parameters['ID'] && get_post($parameters['ID']) && !$update)
        return sprintf(__('The post with post ID (%s) already exists.'), $parameters['ID']);

    /* Validate post type --------------------------------------------------------------------------------------------*/

    /* exit early if no post type */
    if (empty($parameters['post_type'])) {
        return __('Post type  argument "post_type" is missing.', 'oes');
    } /* exit early if post type does not exist */
    else if (!post_type_exists($parameters['post_type'])) {
        return sprintf(__('Post Type (%s) is not registered or inactive.'), $parameters['post_type']);
    }

    /* validate parameters -------------------------------------------------------------------------------------------*/
    $wrongParameter = [];
    $args = [];
    foreach ($parameters as $key => $parameter) {

        /* check if parameter is argument for wp_insert_post */
        if (!in_array($key, ['ID', 'post_type', 'post_title', 'post_status', 'post_author', 'post_date',
            'post_date_gmt', 'post_content', 'post_content_filtered', 'post_excerpt', 'comment_status',
            'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
            'post_parent', 'menu_order', 'post_mime_type', 'guid', 'post_category', 'tags_input', 'tax_input',
            'meta_input'])) $wrongParameter[] = $key;

        /* add filter for post_parent */
        else if ($key == 'post_parent') {
            $args[$key] = apply_filters('oes_insert_post_parent', $parameter, $parameters);
        } else $args[$key] = $parameter;
    }

    return ['post' => wp_insert_post($args, true), 'wrong_parameter' => $wrongParameter];
}


/**
 * Update post meta data like ACF fields.
 *
 * @param string|int $postID A string containing the post ID.
 * @param array $parameters An array containing post arguments for update_field.
 * @param bool $add A boolean indicating if new value should be added to old value (relationship field).
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_post_meta($postID, $parameters, $add = false)
{
    /* Validate post id ----------------------------------------------------------------------------------------------*/
    if (!get_post($postID)) return sprintf(__('The post with post ID (%s) does not exists.', 'oes'), $postID);
    if (empty($parameters)) return sprintf(__('Parameters missing for post ID %s.', 'oes'), $postID);

    /* Insert parameter ----------------------------------------------------------------------------------------------*/
    $resultArray = [];
    $importedFields = 0;
    foreach ($parameters as $field => $parameter) {

        /* check if acf field */
        $fieldObject = get_field_object($field);
        if (!$fieldObject) {

            /* update post meta data */
            $resultArray['update_result'][$field] = oes_update_post_meta($postID, $field, $parameter, false);
        } /* update acf field */
        else {

            /* prepare and validate new value */
            $newValue = null;
            switch ($fieldObject['type']) {

                case 'relationship' :

                    /* turn value into array */
                    $parameterArray = is_array($parameter) ? $parameter : explode(',', $parameter);

                    /* check if value is added to old value */
                    if ($add) {
                        $oldValue = oes_get_post_meta($postID, $field);
                        if ($oldValue && isset($oldValue[0]) && is_array($oldValue[0])) {
                            foreach ($oldValue[0] as $singleOldValue) $parameterArray[] = $singleOldValue;
                        }
                    }

                    /* add filter to modify parameter values */
                    //TODO @2.0 Roadmap : document filter
                    $parameterArray = apply_filters('oes_import_relationship_field', $parameterArray, $field, $postID);

                    /* remove duplicates and empty entries */
                    $parameterArray = array_unique($parameterArray);
                    $parameterArray = array_filter($parameterArray);

                    /* check if values */
                    if (!array($parameterArray) || empty($parameterArray)) break;
                    if (count($parameterArray) == 1 && empty($parameterArray[0])) break;

                    /* prepare each value */
                    foreach ($parameterArray as $singleValue) {

                        if (get_post($singleValue)) $newValue[] = get_post($singleValue);

                        /* Track values that don't meet criteria*/
                        else $resultArray['error'][$field][] = $singleValue;
                    }
                    break;

                case 'taxonomy' :

                    /* add filter to modify parameter values */
                    //TODO @2.0 Roadmap : document filter
                    $newValue = apply_filters('oes_import_taxonomy_field', $parameter, $field, $postID);

                    break;

                case 'link' :

                    /* turn value into array */
                    $parameterArray = explode(',', $parameter);

                    /* add filter to modify parameter values */
                    //TODO @2.0 Roadmap : document filter
                    $parameterArray = apply_filters('oes_import_link_field', $parameterArray, $field, $postID);

                    $url = !empty($parameterArray[0]) ? $parameterArray[0] : '';
                    $newValue = [
                        'url' => $url,
                        'title' => !empty($parameterArray[1]) ? $parameterArray[1] : $url,
                        'target' => !empty($parameterArray[2]) ? $parameterArray[2] : ''
                    ];

                    break;

                default :
                    $newValue = $parameter;
                    break;

            }

            /* update */
            if ($newValue) $resultArray['update_result'][$field] = update_field($field, $newValue, $postID);
        }

        /* track results */
        $importedFields++;

    }

    $resultArray['imported_fields'] = $importedFields;

    return $resultArray;
}


/**
 * Insert a term.
 *
 * @param array $parameters An array containing term arguments for wp_insert_term.
 * @param bool $update A boolean indicating if term is to updated instead of inserted. Default is false.
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_term($parameters, $update = false)
{
    /* Validate term name for insert ---------------------------------------------------------------------------------*/
    if (!$parameters['term'] && !$update)
        return __('The term is missing a term name for insert.', 'oes');

    /* Validate term id for update -----------------------------------------------------------------------------------*/
//TODO    if ($parameters['term_id'] && get_term($parameters['term_id']) && $update)
//        return sprintf(__('The term with the ID (%s) already exists.', 'oes'), $parameters['term_id']);

    /* Validate taxonomy ---------------------------------------------------------------------------------------------*/

    /* exit early if no taxonomy */
    if (empty($parameters['taxonomy'])) {
        return __('Taxonomy argument "taxonomy" is missing.', 'oes');
    } /* exit early if taxonomy does not exist */
    else if (!taxonomy_exists($parameters['taxonomy'])) {
        return sprintf(__('Taxonomy (%s) is not registered or inactive.'), $parameters['taxonomy']);
    }

    /* validate parameters -------------------------------------------------------------------------------------------*/
    $wrongParameter = [];
    $args = [];
    foreach ($parameters as $key => $parameter) {

        /* check if parameter is argument for wp_insert_term or wp_update_term */
        if (!in_array($key, ['alias_of', 'description', 'parent', 'slug', 'args', 'term', 'taxonomy']))
            $wrongParameter[] = $key;

        /* exclude term, taxonomy */
        elseif (!in_array($key, ['term', 'taxonomy'])) $args[$key] = $parameter;
    }

    /* insert or update term */
    $operationSuccessful = $update ?
        wp_update_term($parameters['term_id'], $parameters['taxonomy'], $args) :
        wp_insert_term($parameters['term'], $parameters['taxonomy'], $args);

    return ['term' => $operationSuccessful, 'wrong_parameter' => $wrongParameter];
}

/**
 * Update term meta data like ACF fields.
 *
 * @param string|int $termID A string containing the term ID.
 * @param string $taxonomy A string containing the term taxonomy.
 * @param array $parameters An array containing post arguments for update_field.
 *
 * @return string|array Return array with error string or operation result.
 */
function oes_insert_term_meta($termID, $taxonomy, $parameters)
{
    /* Validate post id ----------------------------------------------------------------------------------------------*/
    if (!get_term($termID)) return sprintf(__('The term with term ID (%s) does not exists.', 'oes'), $termID);

    /* Insert parameter ----------------------------------------------------------------------------------------------*/
    $resultArray = [];
    $importedFields = 0;
    foreach ($parameters as $field => $parameter) {

        /* check if acf field */
        $fieldObject = get_field_object($field);
        if (!$fieldObject) {

            /* update post meta data */
            $resultArray['update_result'][$field] = update_term_meta($termID, $field, $parameter);
        } /* update acf field */
        else {

            /* TODO 2.0 Roadmap : differentiate between field types see post meta */

            /* update */
            $resultArray['update_result'][$field] = update_field($field, $parameter, $taxonomy . '_' . $termID);
        }

        /* track results */
        $importedFields++;

    }

    $resultArray['imported_fields'] = $importedFields;

    return $resultArray;

}
