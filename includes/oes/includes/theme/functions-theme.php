<?php

use OES\Config\Option;


/**
 * Add favicon inside <head></head> on page.
 *
 * @param string $href A string containing the link to the image which is to be used as favicon.
 * Recommended size is 16x16 px.
 * @param string $imgSize TODO @2.0 Roadmap : test sizes
 */
function oes_theme_add_favicon($href, $imgSize = "16x16")
{
    add_action('wp_head', function () use ($href, $imgSize) {
        ?>
        <link rel="icon" href="<?php echo $href; ?>" size="<?php echo $imgSize; ?>">
        <?php
    });
}


/**
 * Modify the WordPress search to scan also post meta data.
 * TODO @2.0 Roadmap : add more filter options
 */
function oes_theme_modify_search()
{
    //add_action('pre_get_posts', function () use ($query) {});
    //add_action('__before_loop', function () {});
    //add_action('__after_loop', function () {});
    add_filter('posts_join', 'oes_search_join');
    add_filter('posts_where', 'oes_modify_search_where_statement');
    //add_filter('posts_orderby', function () {});
    add_filter('posts_distinct', 'oes_search_distinct');
}


/**
 * Add search in post meta table.
 *
 * @param string $join The sql join statement is passed by WordPress search.
 * @return string Returns modified join string.
 */
function oes_search_join($join)
{
    global $wpdb;

    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) {
        $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}


/**
 * Extend search in post meta value.
 *
 * @param string $where The sql where statement is passed by WordPress search.
 * @return string|string[]|null Returns modified where string.
 */
function oes_modify_search_where_statement($where)
{
    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) return oes_modify_search_where_statement_frontend($where);

    return $where;
}


/**
 * Modify search for frontend.
 *
 * @param string $where The sql where statement is passed by WordPress search.
 * @return string|string[]|null Returns modified where string.
 */
function oes_modify_search_where_statement_frontend($where)
{

    /* get global search variable */
    global $wpdb;

    /* prepare filter */
    $filterSearch = '';
    $filterTitle = '';
    $includePostMeta = false;

    /* loop through post types */
    foreach (OES_Project_Config::POST_TYPE_ALL as $postType) {

        /* get options for post type */
        $themeOptions = get_option(Option::THEME . '-' . $postType);

        /* skip if no theme options exist */
        //TODO @2.0 Roadmap : check if field and field belongs to certain post type. (Field names can be used for multiple post types).
        if (!$themeOptions) continue;

        /* loop through fields */
        foreach ($themeOptions as $optionKey => $option) {

            /* field should be [field name]-[parameter] */
            preg_match('/(.+)-(.+)/', $optionKey, $explodedOptionKey);

            /* skip if option key does not match pattern */
            if (empty($explodedOptionKey)) continue;

            /* validate exploded option key */

            /* skip if option parameter is not 'include in search' */
            if ($explodedOptionKey[2] != 'include_in_search') continue;

            /* check if title is included */
            if ($explodedOptionKey[1] == 'wp_title') {
                if (!empty($filterTitle)) $filterTitle .= " OR ";
                $filterTitle .= $wpdb->posts . ".post_type = '" . $postType . "'";
            } else {

                /* first part should be field, skip if no field  */
                //TODO @2.0 Roadmap : validate field. check if field exists
                if (!empty($filterSearch)) {
                    $filterSearch .= ", ";
                } else {
                    /* include search in post meta data to be able to search in post meta keys */
                    $includePostMeta = true;
                }

                //TODO @2.0 Roadmap : check if field and field belongs to certain post type. (Field names can be used for multiple post types).
                $filterSearch .= "'" . $explodedOptionKey[1] . "'";
            }
        }
    }


    /* include title */
    $prepareStatement = $filterTitle ? $wpdb->posts . ".post_title LIKE $1 AND (" . $filterTitle . ") " : "";

    /* include filtered key values */
    if ($includePostMeta) {

        /* check if first replacement */
        if (!empty($prepareStatement)) $prepareStatement .= "OR ";

        /* include search in meta value, exclude search in post meta with meta keys starting with '_' */
        $prepareStatement .= "(" .
            "(" . $wpdb->postmeta . ".meta_value LIKE $1) " .
            "AND (" . $wpdb->postmeta . ".meta_key NOT LIKE '" . '^_%' . "' ESCAPE '" . '^' . "') ";
        $prepareStatement .= $filterSearch ? "AND " . $wpdb->postmeta . ".meta_key IN (" . $filterSearch . ")" : ""; //include filtered key values
        $prepareStatement .= ")";
    }


    if (!empty($prepareStatement)) {

        /** $where syntax :
         * AND ((
         *          (wp_posts.post_title LIKE '...')
         *      OR  (wp_posts.post_excerpt LIKE '...')
         *      OR  (wp_posts.post_content LIKE '...')
         * ))
         *
         * AND wp_posts.post_type IN ('post', 'page', 'attachment', 'oes_demo_article', ...)
         *
         * AND (    wp_posts.post_status = 'publish'
         *      OR  wp_posts.post_status = 'acf-disabled'
         *      OR  wp_posts.post_author = 1
         *      AND wp_posts.post_status = 'private')
         */
        $where = preg_replace(
            "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/", /* hook search into existing search (title is arbitrary) */
            "(" . $prepareStatement .
            ")",
            $where);
    }

    return $where;
}


/**
 * Modify search for frontend.
 *
 * @param string $where The sql where statement is passed by WordPress search.
 * @return string|string[]|null Returns modified where string.
 */
function oes_modify_search_where_statement_backend($where)
{

    /* get global search variable */
    global $wpdb;

    /* prepare filter */
    $filterSearch = '';
    $filterTitle = '';
    $includePostMeta = false;

    /* include title */
    $prepareStatement = $filterTitle ? $wpdb->posts . ".post_title LIKE $1 AND (" . $filterTitle . ") " : "";

    /* include filtered key values */
    if ($includePostMeta) {

        /* check if first replacement */
        if (!empty($prepareStatement)) $prepareStatement .= "OR ";

        /* include search in meta value, exclude search in post meta with meta keys starting with '_' */
        $prepareStatement .= "(" .
            "(" . $wpdb->postmeta . ".meta_value LIKE $1) " .
            "AND (" . $wpdb->postmeta . ".meta_key NOT LIKE '" . '^_%' . "' ESCAPE '" . '^' . "') ";
        $prepareStatement .= $filterSearch ? "AND " . $wpdb->postmeta . ".meta_key IN (" . $filterSearch . ")" : ""; //include filtered key values
        $prepareStatement .= ")";
    }


    if (!empty($prepareStatement)) {

        /** $where syntax :
         * AND ((
         *          (wp_posts.post_title LIKE '...')
         *      OR  (wp_posts.post_excerpt LIKE '...')
         *      OR  (wp_posts.post_content LIKE '...')
         * ))
         *
         * AND wp_posts.post_type IN ('post', 'page', 'attachment', 'oes_demo_article', ...)
         *
         * AND (    wp_posts.post_status = 'publish'
         *      OR  wp_posts.post_status = 'acf-disabled'
         *      OR  wp_posts.post_author = 1
         *      AND wp_posts.post_status = 'private')
         */
        $where = preg_replace(
            "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/", /* hook search into existing search (title is arbitrary) */
            "(" . $prepareStatement .
            ")",
            $where);
    }

    return $where;
}


/**
 * Prevent duplicates in sql where statement.
 *
 * @param string $where The sql where statement is passed by WordPress search.
 * @return string Returns modified where string.
 */
function oes_search_distinct($where)
{
    global $wpdb;

    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) return "DISTINCT";

    return $where;
}