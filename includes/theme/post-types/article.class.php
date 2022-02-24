<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;
use function OES\Figures\oes_get_modal_image;
use function OES\get_post_dtm_parts_from_array;

/**
 * Class Demo_Article
 */
if (class_exists('OES_Post')) :

    class Demo_Article extends OES_Post
    {

        //Overwrite parent
        function set_additional_archive_data(): string
        {
            /* prepare additional archive data */
            $subLine = '';

            /* add language */
            $language = (oes_get_post_language_label($this->parent_ID) ?? false);
            if ($language) $subLine .= '<span class="oes-demo-article-archive-language">(' . $language . ')</span>';

            /* add authors */
            $authors = $this->fields['field_demo_article__article_author']['value-display'] ?? '';
            if (!empty($authors))
                $subLine .= '<div class="oes-demo-article-archive-authors">' .
                    '<span class="oes-demo-article-archive-authors-by">' .
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by') . '</span>' .
                    $authors . '</div>';

            return $subLine;
        }


        //Overwrite parent
        function set_language(string $language)
        {
            $this->language = oes_get_post_language($this->parent_ID) ?? $language;
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
                sprintf('<div class="oes-article-authors text-uppercase"><span>%s</span>%s</div>',
                    '',
                    $this->fields['field_demo_article__article_author']['value-display']
                ) :
                '';

            /* prepare read mor button */
            $readMoreButton = sprintf('<a href="%s" class="btn">%s</a>',
                get_permalink($this->object_ID),
                ($this->theme_labels['featured_article__read_more'][$this->language] ?? 'Read More')
            );

            return sprintf('<div class="oes-featured-article-card">%s' .
                '<div class="oes-featured-article-card-body"><h1 class="oes-content-table-header1">' .
                '<a href="%s">%s</a></h1>%s</div></div>',
                $imageHTML,
                get_permalink($this->object_ID),
                $this->title,
                $authors . ($this->fields["field_demo_article__excerpt"]["value-display"] ?? '') . $readMoreButton
            );
        }


        //Overwrite parent
        function get_html_main(array $args = []): string
        {
            /* prepare content */
            $contentArray = $this->prepare_html_main($args);
            $content = do_shortcode(apply_filters('oes/the_content', implode('', $contentArray)));

            /* check for keywords */
            $keywordString = $this->get_html_terms(['t_demo_subject']);

            /* check for image */
            $imageHTML = '';
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false)
                $imageHTML = oes_get_modal_image($image);

            /* return split view or content */
            if (!empty($keywordString) || !empty($imageHTML))
                return '<div class="oes-demo-article-split-view">' .
                    '<div class="oes-demo-article-split-view-content">' . $content . '</div>' .
                    '<div class="oes-demo-article-side-keywords">' .
                    $imageHTML . $keywordString . '</div>' .
                    '</div>';
            else return $content;
        }


        //Overwrite parent
        function modify_content(array $contentArray): array
        {

            /** Structure:
             * Sub line
             * Table of Contents (OES Core)
             * Content (OES Core)
             * Endnotes (OES Core)
             * Bibliography
             * Citation
             * Metadata (OES Core)
             */


            /* generate sub line -------------------------------------------------------------------------------------*/
            $contentArray['050_sub_line'] = $this->get_html_sub_line();


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
                    if (oes_get_field('field_bib_main', $entry->ID)) {
                        $literatureList .= '<li><div class="oes-custom-indent">' .
                            oes_get_field('field_bib_main', $entry->ID) . '</div></li>';
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
                        ($this->theme_labels['single__toc__header_citation'][$this->language] ?? 'Citation'), 1),
                    $citation);


            /* add index information single__toc__index */
            $contentArray['600_index'] = $this->get_index_connections();

            /* update table of contents */
            $contentArray['100_toc'] = $excludeToc ? '' :
                $this->get_html_table_of_contents(['toc-header' =>
                    $this->theme_labels['single__toc__header_toc'][$this->language] ?? 'Table of Contents']);

            return $contentArray;
        }


        /**
         * Get sub line.
         */
        function get_html_sub_line(): string
        {
            /* first line : display authors --------------------------------------------------------------------------*/
            $firstLine = $this->check_if_field_not_empty('field_demo_article__article_author') ?
                sprintf('<div class="oes-article-authors text-uppercase">' .
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
                            $selectLabels .= sprintf('<li><a href="%s" class="text-uppercase">%s</a></li>',
                                $version['permalink'],
                                $label . ' ' . $version['version']
                            );

                        $label = sprintf('<span class="oes-article-versions-toggle">' .
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
            if ($this->check_if_field_not_empty('field_demo_article__latest_date'))
                $furtherInformation['field_demo_article__latest_date'] = [
                    'label' => $this->get_field_label('field_demo_article__latest_date'),
                    'value-display' =>
                        strftime("%e. %B %Y", strtotime($this->fields['field_demo_article__latest_date']['value']))
                ];

            elseif ($this->check_if_field_not_empty('field_demo_article__published'))
                $furtherInformation['field_demo_article__published'] = [
                    'label' => $this->get_field_label('field_demo_article__published'),
                    'value-display' =>
                        strftime("%e. %B %Y", strtotime($this->fields['field_demo_article__published']['value']))
                ];

            /* display further information */
            if (!empty($furtherInformation)) {

                /* prepare information */
                $secondLineContent = '';
                foreach ($furtherInformation as $info) {

                    /* check if first element */
                    reset($furtherInformation);
                    $secondLineContent .= sprintf('<li class="text-uppercase">%s %s</li>',
                        $info['label'],
                        $info['value-display']
                    );
                }
                $secondLine .= '<div><ul class="oes-horizontal-list">' . $secondLineContent . '</ul></div>';
            }

            return '<div class="oes-article-sub-line">' . $firstLine . $secondLine . '</div>';
        }

    }
endif;