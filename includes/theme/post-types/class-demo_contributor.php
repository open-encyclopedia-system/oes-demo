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
            global $oes_language;

            /* get further publications */
            $publications = $this->fields['field_demo_contributor__publications_other']['value-display'] ?? '';
            if (!empty($publications)) {
                $header =
                    $this->fields['field_demo_contributor__publications_other']['further_options']['label_translation_' . $oes_language] ??
                    '';
                $contentArray['410_further_publication'] = '<div class="oes-demo-author-publications">' .
                    (!empty($header) ? '<h2 class="oes-content-table-header">' . $header . '</h2>' : '') .
                    $publications . '</div>';
            }
            return $contentArray;
        }


        //Overwrite parent
        function get_index_entries_html(array $indexElements): string
        {
            global $oes_language;
            $html = '';
            foreach ($indexElements as $fieldKey => $singleIndex) {
                ksort($singleIndex);
                $html .= '<div class="oes-archive-wrapper-header">' .
                    (($fieldKey !== 'field_demo_contributor__author_article') ?
                        ('<h3 class="oes-demo-contributor-index oes-content-table-header">' .
                            $this->fields[$fieldKey]['further_options']['label_translation_' . $oes_language] .
                            '</h3>') :
                        '') .
                    '</div>' .
                    implode('', $singleIndex);
            }

            return empty($html) ? '' : '<div class="oes-archive-wrapper">' . $html . '</div>';
        }
    }
endif;