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
                    (!empty($language) ?
                        ('<span class="oes-demo-index-post-language">(' . $language . ')</span>') :
                        '');
    return $tableData;
}


/* Display all languages for demo archives */
add_action('oes/theme_redirect_index', 'oes_demo_theme_action_template_redirect', 10, 1);

/**
 * Add language before redirecting archive pages.
 *
 * @param string $currentURL The current url.
 */
function oes_demo_theme_action_template_redirect(string $currentURL)
{

    global $taxonomy, $term;
    if (!isset($_GET['t_demo_article_type']) &&
        $taxonomy === 't_demo_article_type' &&
        $termObject = get_term_by('slug', $term, $taxonomy))
        wp_redirect(get_post_type_archive_link('demo_article') . '?oesf_t_demo_article_type=' . $termObject->term_id);

    global $oes;
    if (!is_admin() &&
        (is_archive() || ($currentURL === get_site_url() . '/' . ($oes->theme_index['slug'] ?? 'index') . '/'))) {
        global $oes_language;
        $oes_language = 'all';
    }
}