<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* add language to title in index display of single posts */
add_filter('oes/post_index_get_index_connections', 'oes_demo_post_index_get_index_connections');


/**
 * Modify the display of index connections (add language to title).
 *
 * @param array $tableData The table data.
 * @return array Returns the modified table data.
 */
function oes_demo_post_index_get_index_connections(array $tableData): array
{
    foreach ($tableData as $fieldKey => $posts)
        foreach ($posts as $parentPostID => $post)
            if ($language = oes_get_post_language_label($parentPostID) ?? false)
                $tableData[$fieldKey][$parentPostID]['title'] =
                    $tableData[$fieldKey][$parentPostID]['title'] .
                    (!empty($language) ? ('<span class="oes-demo-index-post-language">(' . $language . ')</span>') : '');
    return $tableData;
}


/* redirect single taxonomy (term) pages */
add_action('oes/theme_redirect_taxonomy', 'oes_demo_theme_redirect_taxonomy');


/**
 * Redirect for article types to article archive with active filter.
 *
 * @param string $taxonomy The taxonomy of the term.
 */
function oes_demo_theme_redirect_taxonomy($taxonomy){
    if ($taxonomy === 't_demo_article_type') {
        $link = get_post_type_archive_link('demo_article') . '?filter=' .
            get_queried_object()->term_id;
        wp_redirect($link);
    }
}


/* Display all languages for demo archives */
add_action('oes/theme_redirect_index', 'oes_demo_theme_action_template_redirect', 10, 3);

/**
 * Add language before redirecting archive pages.
 *
 * @param string $currentURL The current url.
 * @param string $language The current language.
 * @param bool $switch The language switch.
 */
function oes_demo_theme_action_template_redirect($currentURL, $language, $switch){
    global $oes;
    if (!is_admin() &&
        (is_archive() || ($currentURL === get_site_url() . '/' . ($oes->theme_index['slug'] ?? 'index') . '/'))){
        global $language;
        $language = 'all';
    }
}
