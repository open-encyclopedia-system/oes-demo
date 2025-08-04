<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Demo_Article
 */
if (class_exists('Demo_Post')) :

    class Demo_Article extends Demo_Post
    {
        /**
         * Return HTML for featured post, switching between block and classic themes.
         *
         * @param array $args Arguments.
         * @return string
         */
        public function get_html_featured_post(array $args): string
        {
            if ($this->block_theme) {
                return $this->get_html_featured_post_block($args);
            } else {
                return $this->get_html_featured_post_classic($args);
            }
        }

        /**
         * Get HTML featured post for block theme.
         *
         * @param array $args Arguments.
         * @return string
         */
        public function get_html_featured_post_block(array $args): string
        {
            global $oes_language;

            // Prepare authors line, if available
            $authors = $this->check_if_field_not_empty('field_demo_article__article_author')
                ? sprintf(
                    '<div class="oes-article-authors"><span>%s</span>%s</div>',
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by') . ' ',
                    $this->fields['field_demo_article__article_author']['value-display']
                )
                : '';

            // Prepare read more button
            $readMoreButton = '<a href="' . get_permalink($this->object_ID) . '" class="wp-block-button__link wp-element-button">'
                . ($this->theme_labels['button__read_more'][$oes_language] ?? 'Read More')
                . '</a>';

            // Prepare title with permalink
            $title = '<a href="' . get_permalink($this->object_ID) . '">' . esc_html($this->title) . '</a>';

            // Prepare image HTML if image exists
            $imageHTML = '';
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false) {
                $imageHTML = '<figure class="wp-block-image size-full">
                    <img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '" class="wp-image-' . intval($image['id']) . '"/>
                </figure>';
            }

            // Return the combined block HTML
            return '<div class="wp-block-group">
    <div class="wp-block-group" style="border-top-color:var(--wp--preset--color--background);border-top-width:4px;border-left-color:var(--wp--preset--color--background);border-left-width:4px;min-height:20px"></div>
    <div class="wp-block-columns" style="border-bottom-color:var(--wp--preset--color--background);border-bottom-width:4px;padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30)">
        <div class="wp-block-column" style="padding-right:var(--wp--preset--spacing--30);flex-basis:33.33%">
            ' . $imageHTML . '
        </div>
        <div class="wp-block-column" style="flex-basis:66.66%">
            <h2 class="wp-block-heading">' . $title . '</h2>
            ' . $authors . ($this->fields['field_demo_article__excerpt']['value-display'] ?? '') . '
            <div class="wp-block-buttons">
                <div class="wp-block-button">' . $readMoreButton . '</div>
            </div>
        </div>
    </div>
</div>';
        }

        /**
         * Get HTML featured post for classic theme.
         *
         * @param array $args Arguments.
         * @return string
         */
        public function get_html_featured_post_classic(array $args): string
        {
            // Prepare image HTML if exists
            $imageHTML = '';
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false) {
                $imageHTML = oes_get_html_img(
                    $image['url'],
                    $image['alt']
                );
            }

            // Prepare authors line, if available
            $authors = $this->check_if_field_not_empty('field_demo_article__article_author')
                ? sprintf(
                    '<div class="oes-article-authors"><span>%s</span>%s</div>',
                    $this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by' . ' ',
                    $this->fields['field_demo_article__article_author']['value-display']
                )
                : '';

            // Prepare read more button
            $readMoreButton = oes_get_html_anchor(
                ($this->theme_labels['button__read_more'][$this->language] ?? 'Read More'),
                get_permalink($this->object_ID),
                '',
                'btn'
            );

            return sprintf(
                '<div class="oes-featured-article-card">%s
                    <div class="oes-featured-article-card-body">
                        <h2 class="oes-content-table-header">%s</h2>%s
                    </div>
                </div>',
                $imageHTML,
                oes_get_html_anchor($this->title, get_permalink($this->object_ID)),
                $authors . ($this->fields['field_demo_article__excerpt']['value-display'] ?? '') . $readMoreButton
            );
        }

        /**
         * Display sidebar with various sidebar items (for classic theme).
         */
        public function display_sidebar(): void
        {
            global $oes;

            $sidebarItems = [];

            // Table of Contents
            $sidebarItems['toc'] = [
                'label' => $this->theme_labels['single__toc__header_toc'][$this->language] ?? 'Table of Contents',
                'content' => $this->get_html_table_of_contents(['toc-header-exclude' => true])
            ];

            // Keywords (terms in taxonomy 't_demo_subject')
            foreach ($this->get_all_terms(['t_demo_subject']) as $terms) {
                if (!empty($terms)) {
                    $label = $oes->taxonomies['t_demo_subject']['label_translations_plural'][$this->language]
                        ?? $oes->taxonomies['t_demo_subject']['label_translations'][$this->language]
                        ?? (get_taxonomy('t_demo_subject')->label ?? 't_demo_subject');

                    $sidebarItems['keywords'] = [
                        'label' => $label,
                        'content' => '<div class="oes-post-terms-list">' . implode('', $terms) . '</div>',
                        'amount' => count($terms),
                    ];
                }
            }

            // Images
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false) {
                $sidebarItems['images'] = [
                    'label' => $this->theme_labels['single__toc__header_images'][$this->language] ?? 'Images',
                    'content' => \OES\Figures\oes_get_modal_image($image),
                    'amount' => 1,
                ];
            }

            // Map
            if (!empty($this->fields['field_demo_article__article_place']['value']) && function_exists('oes_map_html')) {
                $sidebarItems['map'] = [
                    'label' => $this->theme_labels['single__toc__header_map'][$this->language] ?? 'Map',
                    'additional' => 'oes-resize-columns-6-6',
                    'content' => oes_map_html([
                        'ids' => $this->fields['field_demo_article__article_place']['value'],
                    ]),
                    'amount' => count($this->fields['field_demo_article__article_place']['value']),
                ];
            }

            // Render sidebar
            if (!empty($sidebarItems)) {
                echo '<div class="oes-sidebar-list-wrapper oes-demo-article-sidebar">';
                foreach ($sidebarItems as $key => $item) {
                    echo '<div class="oes-sidebar-demo-article-wrapper oes-sidebar-article-wrapper-' . esc_attr($key) . '">';
                    echo oes_get_details_block(
                        '<span class="' . esc_attr($item['additional'] ?? '') . '">' .
                        esc_html($item['label'] ?? $key) .
                        (isset($item['amount']) ? '<span class="oes-expand-info-amount">' . intval($item['amount']) . '</span>' : '') .
                        '</span>',
                        '<div class="oes-sidebar-details-wrapper">' . ($item['content'] ?? '') . '</div>'
                    );
                    echo '</div>';
                }
                echo '<div class="oes-sidebar-demo-article-wrapper">';
                echo oes_print_button_html();
                echo '</div></div>';
            }
        }

    }
endif;