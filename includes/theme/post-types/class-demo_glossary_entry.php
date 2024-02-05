<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Versioning\get_parent_id;
use function OES\Versioning\get_version_field;

/**
 * Class Demo_Glossary_Entry
 */
if (class_exists('Demo_Post')) :

    class Demo_Glossary_Entry extends Demo_Post
    {


        // Overwrite parent function
        function modify_metadata($field, $loop): array
        {

            /* add more information about connected articles  */
            if ($field['key'] == 'field_demo_glossary__glossary_article') {

                global $oes_language;

                /* loop through articles */
                $articles = [];
                foreach ($field['value'] ?? [] as $article) {

                    /* prepare meta information */
                    $metaString = '';

                    /* get language label from parent */
                    $language = (oes_get_post_language(get_parent_id($article)) ?: false);

                    if(!$language || $language === $oes_language) {

                        /* get version information */
                        $version = get_version_field($article) ?: false;
                        if ($version) $metaString .= 'Version ' . $version;

                        /* add meta information to archive list */
                        $title = oes_get_display_title($article);
                        $articles[$title . $article] = '<a href="' . get_permalink($article) . '">' . $title . '</a>' .
                            (!empty($metaString) ?
                                '<span> (' . $metaString . ')</span>' :
                                '');
                    }
                }

                if(!empty($articles)) {
                    ksort($articles);
                    $field['value-display'] = '<ul class="oes-demo-glossary-connected-articles oes-vertical-list"><li>' .
                        implode('</li><li>', $articles) . '</li></ul>';
                }
            }

            return $field;
        }
    }
endif;