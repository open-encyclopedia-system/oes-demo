<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Article
 */
if (class_exists('Demo_Post')) :

    class Demo_Article extends Demo_Post
    {

        //Overwrite parent
        function get_html_featured_post(array $args): string
        {
            if ($this->block_theme) return $this->get_html_featured_post_block($args);
            else return $this->get_html_featured_post_classic($args);
        }


        //Overwrite parent
        function get_html_featured_post_block(array $args): string
        {

            /* prepare sub line */
            $authors = $this->check_if_field_not_empty('field_demo_article__article_author') ?
                sprintf('<div class="oes-article-authors"><span>%s</span>%s</div>',
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by') . ' ',
                    $this->fields['field_demo_article__article_author']['value-display']
                ) :
                '';

            /* prepare read more button */
            global $oes_language;
            $readMoreButton = '<a href="' . get_permalink($this->object_ID) .
                '" class="wp-block-button__link wp-element-button">' .
                ($this->theme_labels['button__read_more'][$oes_language] ?? 'Read More') .
                '</a>';

            /* prepare title */
            $title = '<a href="' . get_permalink($this->object_ID) . '">' . $this->title . '</a>';

            /* image */
            $imageHTML = '';
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false)
                $imageHTML = '<!-- wp:image {"id":' . $image['id'] . ',"sizeSlug":"full","linkDestination":"none"} -->
            <figure class="wp-block-image size-full">
            <img src="' . $image['url'] . '" alt="' . $image['alt'] . '" class="wp-image-' . $image['id'] . '"/>
            </figure>
            <!-- /wp:image -->';

            /* check if image */
            return '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"border":{"top":{"color":"var:preset|color|tertiary","width":"4px"},"left":{"color":"var:preset|color|tertiary","width":"4px"}},"dimensions":{"minHeight":"20px"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="border-top-color:var(--wp--preset--color--background);border-top-width:4px;border-left-color:var(--wp--preset--color--background);border-left-width:4px;min-height:20px"></div>
<!-- /wp:group -->
<!-- wp:columns {"style":{"spacing":{"padding":{"right":"var:preset|spacing|30","left":"var:preset|spacing|30","bottom":"var:preset|spacing|30"},"blockGap":{"top":"0","left":"var:preset|spacing|30"}},"border":{"bottom":{"color":"var:preset|color|tertiary","width":"4px"}}}} -->
    <div class="wp-block-columns" style="border-bottom-color:var(--wp--preset--color--background);border-bottom-width:4px;padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30)"><!-- wp:column {"width":"33.33%","style":{"spacing":{"blockGap":"1rem","padding":{"right":"var:preset|spacing|30"}}}} -->
        <div class="wp-block-column" style="padding-right:var(--wp--preset--spacing--30);flex-basis:33.33%">' .
                $imageHTML . '</div>
        <!-- /wp:column -->
        <!-- wp:column {"width":"66.66%"} -->
        <div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:heading -->
            <h2 class="wp-block-heading">' . $title . '</h2>
            <!-- /wp:heading -->' .
                $authors . ($this->fields['field_demo_article__excerpt']['value-display'] ?? '') .
                '<!-- wp:buttons -->
            <div class="wp-block-buttons"><!-- wp:button -->
                <div class="wp-block-button">' . $readMoreButton . '</div>
                <!-- /wp:button --></div>
            <!-- /wp:buttons --></div>
        <!-- /wp:column --></div>
    <!-- /wp:columns --></div>';
        }


        //Overwrite parent
        function get_html_featured_post_classic(array $args): string
        {
            /* prepare image */
            $imageHTML = '';
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false)
                $imageHTML = oes_get_html_img(
                    $image['url'],
                    $image['alt']
                );

            /* prepare sub line */
            $authors = $this->check_if_field_not_empty('field_demo_article__article_author') ?
                sprintf('<div class="oes-article-authors"><span>%s</span>%s</div>',
                    ($this->theme_labels['single__sub_line__author_by'][$this->language] ?? 'by') . ' ',
                    $this->fields['field_demo_article__article_author']['value-display']
                ) :
                '';

            /* prepare read more button */
            $readMoreButton = oes_get_html_anchor(
                ($this->theme_labels['button__read_more'][$this->language] ?? 'Read More'),
                get_permalink($this->object_ID),
                '',
                'btn'
            );

            return sprintf('<div class="oes-featured-article-card">%s' .
                '<div class="oes-featured-article-card-body">' .
                '<h2 class="oes-content-table-header">%s</h2>%s</div></div>',
                $imageHTML,
                oes_get_html_anchor($this->title, get_permalink($this->object_ID)),
                $authors . ($this->fields['field_demo_article__excerpt']['value-display'] ?? '') . $readMoreButton
            );
        }


        //Implement sidebar @oesClassicTheme
        function display_sidebar(): void
        {

            $sidebarItems = [];

            /* ToC */
            $sidebarItems['toc'] = [
                'label' => $this->theme_labels['single__toc__header_toc'][$this->language] ?? 'Table of Contents',
                'content' => $this->get_html_table_of_contents(['toc-header-exclude' => true])
            ];

            /* Keywords */
            global $oes;
            foreach ($this->get_all_terms(['t_demo_subject']) as $terms)
                if (!empty($terms))
                    $sidebarItems['keywords'] = [
                        'label' => ($oes->taxonomies['t_demo_subject']['label_translations_plural'][$this->language] ?:
                            ($oes->taxonomies['t_demo_subject']['label_translations'][$this->language] ?:
                                (get_taxonomy('t_demo_subject')->label ?? 't_demo_subject'))),
                        'content' => '<div class="oes-post-terms-list">' . implode('', $terms) . '</div>',
                        'amount' => sizeof($terms)
                    ];

            /* Images */
            if ($image = $this->fields['field_demo_article__image']['value'] ?? false)
                $sidebarItems['images'] = [
                    'label' => ($this->theme_labels['single__toc__header_images'][$this->language] ?? 'Images'),
                    'content' => \OES\Figures\oes_get_modal_image($image),
                    'amount' => 1
                ];

            /* Map */
            if (!empty($this->fields['field_demo_article__article_place']['value']) &&
                function_exists('oes_map_html'))
                $sidebarItems['map'] = [
                    'label' => ($this->theme_labels['single__toc__header_map'][$this->language] ?? 'Map'),
                    'additional' => 'oes-resize-columns-6-6',
                    'content' => oes_map_html([
                        'ids' => $this->fields['field_demo_article__article_place']['value']
                    ]),
                    'amount' => sizeof($this->fields['field_demo_article__article_place']['value'])
                ];


            $sidebar = '';
            if (!empty($sidebarItems)) {
                $sidebar .= '<div class="oes-sidebar-list-wrapper oes-demo-article-sidebar">';
                foreach ($sidebarItems as $key => $item)
                    $sidebar .= '<div class="oes-sidebar-demo-article-wrapper ' .
                        ('oes-sidebar-article-wrapper-' . $key) . '">' .
                        oes_get_details_block(
                            '<span class="' . ($item['additional'] ?? '') . '">' .
                            ($item['label'] ?? $key) .
                            (isset($item['amount']) ?
                                ('<span class="oes-expand-info-amount">' . $item['amount'] . '</span>') :
                                '') .
                            '</span>',
                            '<div class="oes-sidebar-details-wrapper">' . ($item['content'] ?? '') . '</div>'
                        ) .
                        '</div>';
                $sidebar .= '<div class="oes-sidebar-demo-article-wrapper">' .
                    oes_print_button_html() .
                    '</div>' .
                    '</div>';
            }
            echo $sidebar;
        }
    }
endif;