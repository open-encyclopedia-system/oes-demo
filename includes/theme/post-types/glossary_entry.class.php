<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Versioning\get_parent_id;
use function OES\Versioning\get_version_field;

/**
 * Class Demo_Glossary_Entry
 */
if (class_exists('OES_Post')) :

    class Demo_Glossary_Entry extends OES_Post
    {

        //Overwrite parent
        function set_additional_archive_data(): string
        {
            $subLine = '';

            /* add authors */
            $authors = $this->fields['field_demo_glossary__author']['value-display'] ?? '';
            if(!empty($authors)) $subLine .= '<div class="oes-demo-article-archive-authors">' .
                '<span class="oes-article-author-by text-uppercase">' .
                ($this->theme_labels['archive__subline'][$this->language] ?? 'by') .
                '</span>' . $authors . '</div>';

            return $subLine;
        }


        // Overwrite parent function
        function modify_metadata($field, $loop): array
        {

            /* add more information to connected articles  */
            if ($field['key'] == 'field_demo_glossary__glossary_article'){

                /* loop through articles */
                $articles = [];
                if(!empty($field['value']))
                    foreach($field['value'] as $article){

                        /* prepare meta information */
                        $metaString = '';

                        /* get language label from parent */
                        $language = (oes_get_post_language_label(get_parent_id($article->ID)) ?: false);
                        if($language) $metaString .= $language;

                        /* get version information */
                        $version = get_version_field($article->ID) ?: false;
                        if($version) $metaString .= empty($metaString) ? $version : ', ' . $version;

                        /* add meta information to archive list */
                        $title = oes_get_display_title($article->ID);
                        $articles[$title . $article->ID] = sprintf('<a href="%s">%s</a>%s',
                            get_permalink($article->ID),
                            $title,
                            ($metaString ?
                                '<span class="oes-demo-glossary-archive-connected-articles-metainfo">(' .
                                $metaString . ')</span>' :
                                '')
                            );
                    }

                ksort($articles);
                $field['value-display'] = '<ul class="oes-demo-glossary-connected-articles oes-field-value-list"><li>' .
                    implode('</li><li>', $articles) . '</li></ul>';
            }

            return $field;
        }
    }
endif;