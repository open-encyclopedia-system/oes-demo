<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;

/**
 * Class Demo_Taxonomy
 */
if (class_exists('OES_Taxonomy')) :

    class Demo_Taxonomy extends OES_Taxonomy
    {

        /* Implement sidebar */
        function display_sidebar()
        {
            /* add navigation item to get to index page */
            $indexLink = '';
            if(!empty($this->part_of_index_pages)) {
                global $oes, $oes_language;
                $consideredLanguage = ($oes_language === 'all') ? $oes->main_language : $oes_language;
                $slug = $oes->theme_index_pages[$this->part_of_index_pages[0]]['slug'];
                if ($slug != 'hidden')
                    $indexLink = sprintf('<a href="%s" class="oes-index-filter-anchor">%s</a>',
                        get_site_url() . '/' . ($slug ?? 'index') . '/',
                        $oes->theme_labels['single__back_to_index'][$consideredLanguage] ?? 'See Index'
                    );
            }

            echo '<div class="oes-sidebar-list-wrapper">' .
                $this->get_breadcrumbs_html() .
                '<div>'.
                $indexLink .
                '</div>' .
                '</div>';
        }


        //Overwrite parent
        function get_index_connection_html(array $connectedPosts): string
        {
            /* loop through rows and check for additional data */
            $indexElements = [];
            if (!empty($connectedPosts))
                foreach ($connectedPosts as $fieldKey => $rows)
                    if (!empty($rows))
                        foreach ($rows as $row) {

                            $indexElements[$fieldKey][($row['title-sort'] ?? $row['title']) . $row['id']] =
                                sprintf('<div class="oes-demo-index-connection oes-post-filter-wrapper oes-post-%s">' .
                                    '<a href="#row%s" data-toggle="collapse" aria-expanded="false" class="oes-archive-plus oes-toggle-down-before"></a>' .
                                    '%s<div class="oes-archive-table-wrapper collapse" id="row%s">' .
                                    '<table class="oes-archive-table oes-simple-table">%s</table>' .
                                    '</div>' .
                                    '</div>',
                                    oes_get_post_language($row['id']) ?: 'all',
                                    $row['id'],
                                    $this->get_index_entry_title($row),
                                    $row['id'],
                                    $this->get_index_entry_preview($row)
                                );
                        }

            return empty($indexElements) ? '' : $this->get_index_entries_html($indexElements);
        }


        //Overwrite parent
        function get_index_connection_post_data($postID): array
        {
            /* Date */
            $date = oes_get_field('field_demo_article__published', $postID);
            if ($this->language === 'language1')
                setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

            /* Authors */
            $authors = [];
            if($authorIDs = oes_get_field('field_demo_article__article_author', $postID))
                foreach($authorIDs as $authorID) $authors[] = get_post($authorID);

            return [
                'date' => ($date ?
                    strftime("%e. %B %Y", strtotime(str_replace('/', '-', $date))) :
                    ''),
                'authors' => oes_display_post_array_as_list($authors, false, ['class' => 'oes-field-value-list']),
                'authors_amount' => empty($authors) ? 0 : sizeof($authors)
            ];
        }


        /**
         * Get title for index entry.
         *
         * @param array $row The index entry data.
         * @return string Return the index entry title.
         */
        function get_index_entry_title(array $row): string {
            return $row['title'] ?? $row['id'];
        }


        /**
         * Get preview info for index entry.
         *
         * @param array $row The index entry data.
         * @return string Return the index entry title.
         */
        function get_index_entry_preview(array $row): string{

            /* prepare preview */
            $previewTable = '';

            if (isset($row['data']['versions']) && !empty($row['data']['versions']))
                $previewTable .= '<tr><th>' .
                    ($this->theme_labels['single__table__version'][$this->language] ?? 'Version') .
                    '</th>' .
                    '<td>' . implode(', ', $row['data']['versions']) . '</td>' .
                    '</tr>';

            if (isset($row['data']['authors']) && !empty($row['data']['authors'])) {

                $label = (isset($row['data']['authors_amount']) && $row['data']['authors_amount'] > 1) ?
                    ($this->theme_labels['single__index__list_author__plural'][$this->language] ?? 'Authors'):
                    ($this->theme_labels['single__index__list_author__single'][$this->language] ?? 'Author');
                $previewTable .= '<tr><th>' . $label . '</th>' .
                    '<td>' . $row['data']['authors'] . '</td>' .
                    '</tr>';
            }

            return $previewTable;
        }


        /**
         * Get the list of index entries.
         *
         * @param array $indexElements The index entries.
         * @return string Return the html representation of the list of index entries.
         */
        function get_index_entries_html(array $indexElements): string {

            $header = empty($this->part_of_index_pages) ?
                ($this->theme_labels['single__list__header'][$this->language] ?? 'Articles') :
                $this->theme_labels['single__toc__index_inner'][$this->language] ?? 'Referred to in:';

            $collectIndexElements = [];
            foreach($indexElements as $singleIndex)
                foreach($singleIndex as $singleEntryKey => $singleEntry)
                    $collectIndexElements[$singleEntryKey] = $singleEntry;

            ksort($collectIndexElements);
            return '<div class="oes-archive-wrapper">' .
                '<div class="oes-archive-wrapper-header">' .
                $header .
                '</div>' .
                '<div class="oes-archive-wrapper">' .
                '<div class="oes-alphabet-container">' .
                implode('', $collectIndexElements) .
                '</div>' .
                '</div>' .
                '</div>';
        }
    }
endif;