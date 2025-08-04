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
        /** @inheritdoc */
        public function modify_metadata($field, $loop): array
        {
            if ($field['key'] !== 'field_demo_glossary__glossary_article') {
                return $field;
            }

            global $oes_language;

            $articles = [];

            foreach ($field['value'] ?? [] as $article_id) {
                $metaString = '';

                // Get post language of parent post of the article
                $language = oes_get_post_language(get_parent_id($article_id)) ?: false;

                // Include article if language matches current language or is undefined
                if (!$language || $language === $oes_language) {

                    // Get version info if available
                    $version = get_version_field($article_id) ?: false;
                    if ($version) {
                        $metaString .= 'Version ' . esc_html($version);
                    }

                    // Get display title safely
                    $title = oes_get_display_title($article_id);
                    $link = '<a href="' . esc_url(get_permalink($article_id)) . '">' . esc_html($title) . '</a>';

                    $articles[$title . $article_id] = $link .
                        (!empty($metaString) ? '<span> (' . $metaString . ')</span>' : '');
                }
            }

            if (!empty($articles)) {

                // Sort articles alphabetically by key (title + ID)
                ksort($articles);

                $field['value-display'] = '<ul class="oes-demo-glossary-connected-articles oes-vertical-list"><li>' .
                    implode('</li><li>', $articles) . '</li></ul>';
            }

            return $field;
        }
    }
endif;
