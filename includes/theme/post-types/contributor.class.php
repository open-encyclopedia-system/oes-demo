<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;


/**
 * Class Demo_Contributor
 */
if (class_exists('OES_Post')) :

    class Demo_Contributor extends OES_Post
    {

        //Overwrite parent
        function modify_content(array $contentArray): array
        {

            /** Structure:
             * Vita
             * Publications (will be rendered by index)
             * Further Publications
             * Metadata
             */

            /* get vita ----------------------------------------------------------------------------------------------*/
            $contentArray['050_vita'] = '<div class="oes-demo-author-vita">' .
                $this->fields['field_demo_contributor__vita']['value-display'] . '</div>';

            /* add index information single__toc__index */
            $contentArray['051_index'] = $this->get_index_connections();


            /* get further publications ------------------------------------------------------------------------------*/
            $publications = $this->fields['field_demo_contributor__publications_other']['value-display'] ?? '';
            if (!empty($publications))
                $contentArray['410_further_publication'] = '<div class="oes-demo-author-publications">' .
                    '<h1 class="oes-content-table-header1">' .
                    ($this->theme_labels['single__header_further_publications'][$this->language] ?? 'Further Publications') .
                    '</h1>' . $publications . '</div>';

            return $contentArray;
        }


        //Overwrite parent
        function get_index_connection_post_data($postID): array
        {
            /* Date */
            $date = oes_get_field('field_demo_article__published', $postID);
            if ($this->language === 'language1')
                setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

            /* Co-Authors */
            $authors = oes_get_field('field_demo_article__article_author', $postID);
            $coAuthors = [];
            if ($authors)
                foreach ($authors as $author)
                    if ($author->ID != $this->object_ID) $coAuthors[] = $author;

            return [
                'date' => ($date ?
                    strftime("%e. %B %Y", strtotime(str_replace('/', '-', $date))) :
                    ''),
                'co-authors' => oes_display_post_array_as_list($coAuthors, false, ['class' => 'oes-field-value-list'])
            ];
        }


        //Overwrite parent
        function get_index_connection_html(array $connectedPosts): string
        {

            $indexString = '';
            if (!empty($connectedPosts))
                foreach ($connectedPosts as $fieldKey => $rows)
                    if (!empty($rows)) {

                        if ($fieldKey === 'field_demo_contributor__contributor_article')
                            $indexString .= '<tr class="oes-demo-author-article-intermediate"><th colspan="4">' .
                                ($this->theme_labels['single__table__header_contributor'][$this->language] ??
                                    'As Contributor:') .
                                '</th>' .
                                '</tr>';

                        /* loop through rows and check for additional data */
                        foreach ($rows as $row)
                            $indexString .= '<tr><td>' . $row['title'] . '</td>' .
                                '<td>' . implode(', ', $row['data']['versions'] ?? []) . '</td>' .
                                '<td>' . ($row['data']['date'] ?? '') . '</td>' .
                                '<td>' . ($row['data']['co-authors'] ?? '') . '</td>' .
                                '</tr>';
                    }

            return '<div class="oes-demo-author-articles-table-wrapper">' .
                '<h1 class="oes-content-table-header1">' .
                ($this->theme_labels['single__table__header'][$this->language] ?? 'Articles') .
                '</h1>' .
                '<table class="oes-demo-author-articles-table table">' .
                '<thead><tr><th>' .
                ($this->theme_labels['single__table__title'][$this->language] ?? 'Title') .
                '</th><th>' .
                ($this->theme_labels['single__table__version'][$this->language] ?? 'Version') .
                '</th><th>' .
                ($this->theme_labels['single__table__date'][$this->language] ?? 'Date') .
                '</th><th>' .
                ($this->theme_labels['single__table__contributors'][$this->language] ?? '(Co-)Authors') .
                '</th></tr></thead>' .
                $indexString .
                '</table>' .
                '</div>';
        }


        // Overwrite parent function
        function modify_metadata($field, $loop): array
        {
            /* replace gnd ID with shortcode */
            if ($field['key'] == 'field_demo_contributor__gnd_id')
                $field['value-display'] = '[gndlink id="' . $field['value'] . '"]';

            /* replace ORCHID ID with link */
            if ($field['key'] == 'field_demo_contributor__orcid_id')
                $field['value-display'] =
                    '<a href="' . $field['value'] . '" target="_blank">' . $field['value'] . '</a>';

            return $field;
        }
    }
endif;