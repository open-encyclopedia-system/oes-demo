<?php

add_filter('oes/post_index_get_index_connections', 'oes_demo_post_index_get_index_connections');
add_action('after_setup_theme', 'oes_demo_after_setup_theme');
add_filter('oes_timeline/start_date', 'oes_demo_timeline_start_date', 10, 2);
add_filter('oes_timeline/end_date', 'oes_demo_timeline_end_date', 10, 2);


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


/**
 * Set container class.
 *
 * @return void.
 */
function oes_demo_after_setup_theme(): void
{
    global $oes_archive_alphabet_initial, $oes_container_class;
    $oes_archive_alphabet_initial = true;
    $oes_container_class = 'container';
}


/**
 * Filter the start date.
 *
 * @param string $startLabel The value.
 * @param string $start The start value.
 * @return string
 */
function oes_demo_timeline_start_date(string $startLabel, string $start): string
{
    return empty($startLabel) ?
        str_replace('/', '.', $start):
        $startLabel;
}


/**
 * Filter the end date.
 *
 * @param string $endLabel The value.
 * @param string $end The start value.
 * @return string
 */
function oes_demo_timeline_end_date(string $endLabel, string $end): string
{
    return empty($endLabel) ?
        str_replace('/', '.', $end):
        $endLabel;
}