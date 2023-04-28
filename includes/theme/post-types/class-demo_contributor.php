<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Class Demo_Contributor
 */
if (class_exists('Demo_Post')) :

    class Demo_Contributor extends Demo_Post
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


            /* get further publications ------------------------------------------------------------------------------*/
            $publications = $this->fields['field_demo_contributor__publications_other']['value-display'] ?? '';
            if (!empty($publications))
                $contentArray['410_further_publication'] = '<div class="oes-demo-author-publications">' .
                    '<h2 class="oes-content-table-header">' .
                    ($this->theme_labels['single__header_further_publications'][$this->language] ?? 'Further Publications') .
                    '</h2>' . $publications . '</div>';

            return $contentArray;
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


        // Overwrite parent
        function get_index_entries_html(array $indexElements): string {

            global $oes_is_index;
            $header = $oes_is_index ?
                $this->theme_labels['single__toc__index_inner'][$this->language] ?? 'Referred to in' :
                ($this->theme_labels['single__list__header'][$this->language] ?? 'Articles') ;

            $collectIndexElements = [];
            $indexHTML = '';
            foreach($indexElements as $fieldKey => $singleIndex) {

                if($fieldKey === 'field_demo_contributor__contributor_article'){
                    ksort($singleIndex);
                    $indexHTML = '<div class="oes-archive-wrapper-header">' .
                        ($this->theme_labels['single__list__header_contributor'][$this->language] ??
                            'As Contributor:') .
                        '</div>' .
                        '<div class="oes-archive-wrapper">' .
                        '<div class="oes-alphabet-container">' .
                        implode('', $singleIndex) .
                        '</div>' .
                        '</div>';
                }
                else{
                    foreach ($singleIndex as $singleEntryKey => $singleEntry)
                        $collectIndexElements[$singleEntryKey] = $singleEntry;
                }
            }

            if(!empty($collectIndexElements)){
                ksort($collectIndexElements);
                $indexHTML = '<div class="oes-archive-wrapper-header">' .
                    $header .
                    '</div>' .
                    '<div class="oes-archive-wrapper">' .
                    '<div class="oes-alphabet-container">' .
                    implode('', $collectIndexElements) .
                    '</div>' .
                    '</div>' .
                    $indexHTML;
            }

            return '<div class="oes-archive-wrapper">' .
                $indexHTML .
                '</div>';
        }
    }
endif;