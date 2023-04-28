<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;
use function OES\Figures\oes_get_modal_image;
use function OES\get_post_dtm_parts_from_array;
use function OES\Versioning\get_parent_id;
use function OES\Versioning\get_version_field;

/**
 * Class Demo_Article
 */
if (class_exists('Demo_Post')) :

    class Demo_Article extends Demo_Post
    {

        //Overwrite parent
        function set_additional_archive_data(): string
        {
            global $oes;

            /* prepare additional archive data */
            $subLine = '';

            /* add language */
            $language = (oes_get_post_language_label($this->parent_ID) ?? false);
            if ($language) $subLine .= '<span class="oes-demo-article-archive-language">(' . $language . ')</span>';

            /* add occurrences for search */
            if (isset($args['occurrences-count']))
                $subLine .= sprintf('<span class="oes-demo-article-archive-occurrences">%s %s</span>',
                    $args['occurrences-count'],
                    ($oes->theme_labels['search__result_table__header_occurrences'][$this->language] ?? 'Occurrences')
                );

            /* add authors */
            $authors = $this->fields['field_demo_article__article_author']['value-display'] ?? '';
            if (!empty($authors))
                $subLine .= '<div class="oes-demo-article-archive-authors">' .
                    '<span class="oes-demo-article-archive-authors-by">' .
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'By') . '</span>' .
                    $authors . '</div>';

            return $subLine;
        }


        //Overwrite parent
        function set_language(string $language): void
        {
            if ($parentID = get_parent_id($this->object_ID))
                $this->language = oes_get_post_language($parentID) ?? $language;
        }


        //Overwrite parent
        function get_html_featured_post(array $args): string
        {
            /* prepare image */
            $imageHTML = '';
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false) {
                $imageHTML .= sprintf('<img src="%s" alt="%s">',
                    $image['url'],
                    $image['alt']
                );
            }

            /* prepare sub line */
            $authors = $this->check_if_field_not_empty('field_demo_article__article_author') ?
                sprintf('<div class="oes-article-authors"><span>%s</span>%s</div>',
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by') . ' ',
                    $this->fields['field_demo_article__article_author']['value-display']
                ) :
                '';

            /* prepare read more button */
            $readMoreButton = sprintf('<a href="%s" class="btn">%s</a>',
                get_permalink($this->object_ID),
                ($this->theme_labels['button__read_more'][$this->language] ?? 'Read More')
            );

            return sprintf('<div class="oes-featured-article-card">%s' .
                '<div class="oes-featured-article-card-body"><h2 class="oes-content-table-header">' .
                '<a href="%s">%s</a></h2>%s</div></div>',
                $imageHTML,
                get_permalink($this->object_ID),
                $this->title,
                $authors . ($this->fields["field_demo_article__excerpt"]["value-display"] ?? '') . $readMoreButton
            );
        }


        // Overwrite parent
        function get_html_sub_header(array $args = []): string
        {
            return '';
        }


        //Implement parent
        function get_html_short_title(array $args = []): string
        {
            /* get authors */
            $authors = $this->check_if_field_not_empty('field_demo_article__article_author') ?
                sprintf('. <span class="oes-article-author-by">%s</span>%s.',
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by') . ' ',
                    $this->fields['field_demo_article__article_author']['value-display']
                ) :
                '';

            return $this->get_title() . $authors;

        }


        //Overwrite parent
        function modify_content(array $contentArray): array
        {

            /** Structure:
             * Cover Info
             * Table of Contents (OES Core)
             * Content (OES Core)
             * Notes (OES Core)
             * Bibliography
             * Citation
             * Metadata (OES Core)
             */


            /* get sub line ------------------------------------------------------------------------------------------*/
            $contentArray['100_cover_info'] = '<div class="oes-demo-cover-info">' . $this->get_cover_info() . '</div>';


            /* wrap content ------------------------------------------------------------------------------------------*/
            $contentArray['200_content'] = '<div class="oes-demo-article-content">' .
                $contentArray['200_content'] . '</div>';


            /* check for table of content (skip if no entry so far) --------------------------------------------------*/
            $excludeToc = empty($this->table_of_contents);

            /* get bibliography --------------------------------------------------------------------------------------*/
            $literature = '';
            if ($this->check_if_field_not_empty('field_demo_article__bib')) {

                /* loop through entries */
                $literatureList = '';
                foreach ($this->fields['field_demo_article__bib']['value'] ?? [] as $entry)
                    if (oes_get_field('field_bib_main', $entry)) {
                        $literatureList .= '<li><div class="oes-custom-indent">' .
                            oes_get_field('field_bib_main', $entry) . '</div></li>';
                    }

                if (!empty($literatureList))
                    $literature = '<div class="oes-demo-article-literature">' .
                        $this->generate_table_of_contents_header(
                            $this->theme_labels['single__toc__header_literature'][$this->language] ??
                            'Literature') .
                        '<ul class="oes-vertical-list">' .
                        $literatureList . '</ul></div>';

            }
            $contentArray['310_literature'] = $literature;

            $furtherLiterature = '';
            if ($this->check_if_field_not_empty('field_demo_article__further_literature'))
                $furtherLiterature = '<div class="oes-demo-article-further-literature">' .
                    $this->generate_table_of_contents_header(
                        $this->theme_labels['single__toc__header_literature_further'][$this->language] ??
                        'Further Literature') .
                    $this->fields['field_demo_article__further_literature']['value-display'] . '</div>';
            $contentArray['320_further_literature'] = $furtherLiterature;


            /* get citation ------------------------------------------------------------------------------------------*/
            global $oes;
            $citation = ($this->check_if_field_not_empty('field_demo_article__cite_as') &&
                isset($this->fields['field_demo_article__cite_as']['value-display']) &&
                $this->fields['field_demo_article__cite_as']['value-display'] != 'generate') ?
                $this->fields['field_demo_article__cite_as']['value-display'] :
                get_post_dtm_parts_from_array(
                    $oes->post_types['demo_article']['field_options']['field_demo_article__cite_as']['pattern']['parts'] ?? [],
                    $this->object_ID, ' ');
            $contentArray['330_citation'] = empty($citation) ?
                '' :
                sprintf('<div class="oes-demo-article-citation">%s%s</div>',
                    $this->generate_table_of_contents_header(
                        ($this->theme_labels['single__toc__header_citation'][$this->language] ?? 'Citation')),
                    $citation);

            /* update table of contents */
            $contentArray['100_toc'] = $excludeToc ? '' :
                $this->get_html_table_of_contents(['toc-header' =>
                    $this->theme_labels['single__toc__header_toc'][$this->language] ?? 'Table of Contents']);

            return $contentArray;
        }


        // Overwrite parent function
        function modify_metadata($field, $loop): array
        {
            /* replace cc licence with link */
            if ($field['key'] === 'field_demo_article__licence_type')
                $field['value-display'] = sprintf('<a href="%s" target="_blank">%s</a>',
                    $field['value'],
                    $field['value-display']
                );

            return $field;
        }


        //Implement sidebar
        function display_sidebar()
        {
            global $oes;

            $sidebarItems = [];

            /* ToC */
            $sidebarItems['toc'] = [
                'label' => $this->theme_labels['single__toc__header_toc'][$this->language] ?? 'Table of Contents',
                'content' => $this->get_html_table_of_contents(['toc-header-exclude' => true])
            ];

            /* Keywords */
            foreach ($this->get_all_terms(['t_demo_subject'], $this->object_ID) as $terms)
                if (!empty($terms))
                    $sidebarItems['keywords'] = [
                        'label' => ($oes->taxonomies['t_demo_subject']['label_translations_plural'][$this->language] ?:
                            ($oes->taxonomies['t_demo_subject']['label_translations'][$this->language] ?:
                                (get_taxonomy('t_demo_subject')->label ?? 't_demo_subject'))),
                        'content' => implode('', $terms),
                        'amount' => sizeof($terms)
                    ];

            /* Images */
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false)
                $sidebarItems['images'] = [
                    'label' => ($this->theme_labels['single__toc__header_images'][$this->language] ?? 'Images'),
                    'content' => oes_get_modal_image($image),
                    'amount' => 1
                ];

            /* Map */
            if (!empty($this->fields['field_demo_article__article_place']['value']) &&
                function_exists('oes_map_HTML'))
                $sidebarItems['map'] = [
                    'label' => ($this->theme_labels['single__toc__header_map'][$this->language] ?? 'Map'),
                    'additional' => 'oes-resize-columns-6-6',
                    'content' => oes_map_HTML([
                        'ids' => $this->fields['field_demo_article__article_place']['value']
                    ]),
                    'amount' => sizeof($this->fields['field_demo_article__article_place']['value'])
                ];

            /* Timeline */
            if (!empty($this->fields['field_demo_article__article_event']['value']) &&
                function_exists('oes_timeline_HTML'))
                $sidebarItems['timeline'] = [
                    'label' => ($this->theme_labels['single__toc__header_timeline'][$this->language] ?? 'Timeline'),
                    'additional' => 'oes-resize-columns-6-6',
                    'content' => oes_timeline_HTML([
                        'ids' => $this->fields['field_demo_article__article_event']['value']
                    ]),
                    'amount' => sizeof($this->fields['field_demo_article__article_event']['value'])
                ];


            if (!empty($sidebarItems)) {

                echo '<div class="oes-sidebar-list-wrapper oes-demo-article-sidebar">';

                foreach ($sidebarItems as $key => $item)
                    printf('<div class="oes-demo-expand-info">' .
                        '<div class="oes-sidebar-demo-article-wrapper %s">' .
                        '<a href="#%s" class="%s oes-sidebar-demo-article-trigger" data-toggle="collapse" aria-expanded="false">%s%s</a>' .
                        '</div>' .
                        '<div class="oes-sidebar-demo-article-triggered collapse" id="%s">%s</div>' .
                        '</div>',
                        ('oes-sidebar-article-wrapper-' . $key),
                        ('oes-sidebar-article-' . $key),
                        $item['additional'] ?? '',
                        $item['label'] ?? $key,
                        (isset($item['amount']) ?
                            ('<span class="oes-demo-expand-info-amount">' . $item['amount'] . '</span>') :
                            ''),
                        ('oes-sidebar-article-' . $key),
                        $item['content'] ?? ''
                    );
                echo '</div>';
            }
        }


        /**
         * Get cover info (as sub line).
         *
         * @return string The cover info.
         */
        function get_cover_info(): string
        {
            /* first line : display authors --------------------------------------------------------------------------*/
            $firstLine = $this->check_if_field_not_empty('field_demo_article__article_author') ?
                sprintf('<div class="oes-article-authors">' .
                    '<span class="oes-article-author-by">%s</span>%s</div>',
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by') . ' ',
                    $this->fields['field_demo_article__article_author']['value-display']
                ) :
                '';


            /* second line -------------------------------------------------------------------------------------------*/
            $secondLine = '';

            /* prepare information for display */
            $furtherInformation = [];

            /* prepare version list */
            if ($this->check_if_field_not_empty('field_oes_post_version')) {

                /* get all version */
                $label = $this->get_field_label('field_oes_post_version') . ' ';
                $value = $this->fields['field_oes_post_version']['value-display'];

                /* check if there are more versions */
                if ($this->parent_ID) {

                    /* get all versions connected to the parent post */
                    $allVersions = $this->get_all_versions(['publish']);

                    /* add each version to the version dropdown */
                    if (!empty($allVersions)) {

                        /* prepare selection values */
                        $selectLabels = '';
                        foreach ($allVersions as $version)
                            $selectLabels .= sprintf('<li><a href="%s">%s</a></li>',
                                $version['permalink'],
                                $label . ' ' . $version['version']
                            );

                        $label = sprintf('<span class="oes-toggle-down oes-article-versions-toggle">' .
                            '<a href="#oes-article-versions-ul" data-toggle="collapse" aria-expanded="false">%s</a>' .
                            '<ul id="oes-article-versions-ul" class="collapse">%s</ul></span>',
                            $this->get_field_label('field_oes_post_version') . ' ' .
                            $this->fields['field_oes_post_version']['value-display'],
                            $selectLabels
                        );

                        /* unset value (given value only needed when no versions found) */
                        $value = '';
                    }
                }

                /* add information for second line */
                $furtherInformation['field_oes_post_version'] = [
                    'label' => $label,
                    'value-display' => $value
                ];
            }

            /* prepare publication date */
            /* get the latest change date and add information for second line, if empty check for publication date */
            $publishedDate = false;
            if ($this->check_if_field_not_empty('field_demo_article__published')) {

                if ($dateValue = $this->fields['field_demo_article__published']['value'])
                    $publishedDate = oes_convert_date_to_formatted_string($dateValue,
                        ($this->language === 'language1' ? 'de_DE' : 'en_US'));

                $furtherInformation['field_demo_article__published'] = [
                    'label' => $this->get_field_label('field_demo_article__published'),
                    'value-display' => $publishedDate
                ];
            }

            if ($this->check_if_field_not_empty('field_demo_article__latest_date')) {

                $editedDate = false;
                if ($dateValue = $this->fields['field_demo_article__latest_date']['value'])
                    $editedDate = oes_convert_date_to_formatted_string($dateValue,
                        ($this->language === 'language1' ? 'de_DE' : 'en_US'));

                if ($editedDate && $editedDate !== $publishedDate)
                    $furtherInformation['field_demo_article__latest_date'] = [
                        'label' => $this->get_field_label('field_demo_article__latest_date'),
                        'value-display' => $editedDate
                    ];
            }

            /* display further information */
            if (!empty($furtherInformation)) {

                /* prepare information */
                $secondLineContent = '';
                foreach ($furtherInformation as $info)
                    $secondLineContent .= sprintf('<li>%s %s</li>',
                        $info['label'],
                        $info['value-display']
                    );
                $secondLine .= '<div><ul class="oes-horizontal-list">' . $secondLineContent . '</ul></div>';
            }

            /* add translation */
            $translationLink = '';
            if (!empty($this->translations)) {
                foreach ($this->translations as $translation)
                    if ($translation['language'] != $this->language) {

                        $additionalInfo = [];
                        if ($translationVersion = get_version_field($translation['id']))
                            $additionalInfo[] = 'Version ' . $translationVersion;
                        $additionalInfo[] = OES()->languages[$translation['language']]['label'] ?? '';

                        $translationLink = ($this->theme_labels['single__sub_line__translation'][$this->language] ?? 'English version available: ') .
                            sprintf('<a href="%s"><span class="oes-search-result-version-info">(%s)</span></a>',
                                get_permalink($translation['id']),
                                implode(' | ', $additionalInfo)
                            );
                    }
            }
            $thirdLine = empty($translationLink) ? '' : ('<div class="oes-demo-article-translation-link pt-3">' . $translationLink . '</div>');

            return '<div class="oes-article-sub-line">' . $firstLine . $secondLine . $thirdLine . '</div>';
        }
    }
endif;