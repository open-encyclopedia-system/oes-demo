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