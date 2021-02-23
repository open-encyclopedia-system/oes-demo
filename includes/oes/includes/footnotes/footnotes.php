<?php

namespace OES\Footnotes;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Footnotes')) :

    /**
     * Enable the feature "Footnotes" to add a shortcode [oes_note][/oes_note] and compute a list of notes for
     * displaying.
     *
     * Based on easy-footnotes, Copyright 2018, Jason Yingling, https://jasonyingling.me/easy-footnotes-wordpress/
     */
    class Footnotes
    {

        /** @var array $footnotes An array for the footnotes to be stored. */
        public $footnotes = [];


        /**
         * Constructing the shortcodes and hook them to processing.
         */
        public function __construct()
        {
            /* add shortcodes */
            add_shortcode('oes_note', [$this, 'oes_easy_footnote_shortcode']);

            /* display footnotes list after content */
            add_filter('oes_footnote_list', [$this, 'oes_easy_footnote_after_content'], 20);
        }


        /**
         * Create the html representation of a footnote and prepare footnote list.
         *
         * @param array $args Shortcode attributes (is mostly empty, unused).
         * @param string $content Content within the shortcode.
         *
         * @return string Return the html string representing a footnote.
         */
        public function oes_easy_footnote_shortcode($args, $content = null)
        {

            /* Skip if admin */
            if (is_admin()) return '[oes_note]' . $content . '[/oes_note]';

            /* add content (without shortcode) to global footnote variable. */
            $numberOfNote = sizeof($this->footnotes) + 1;
            $this->footnotes[$numberOfNote] = do_shortcode($content);

            /* compute footnote link */
            $footnoteLink = sprintf('#oes-footnote-bottom-%1s-%2s',
                $numberOfNote,
                get_the_ID()
            );

            /* add permalink if not on this page */
            if (!(is_singular() && is_main_query())) $footnoteLink = get_permalink(get_the_ID()) . $footnoteLink;

            return sprintf('<span id="oes-footnote-%1s" class="oes-anchor"></span>
            <span class="oes-footnote"><a href="%2s" title="%3s"><sup>%4s</sup></a></span>',
                esc_attr($numberOfNote) . '-' . get_the_ID(),
                esc_url($footnoteLink),
                htmlspecialchars(do_shortcode($content), ENT_QUOTES),
                '[' . esc_html($numberOfNote) . ']'
            );
        }


        /**
         * Display the list of footnotes after the post content.
         *
         * @param string $content The content of the current post.
         * @return string Return the html string representing the footnote list.
         */
        public function oes_easy_footnote_after_content($content)
        {

            /* get configuration option */
            $footnoteOptions = get_option('oes_footnotes');

            /* Skip if empty, options for hiding set, not singular page or not main query */
            if (empty($this->footnotes) || // Skip if no footnotes set.
                !is_singular() || // Skip if page is not singular page.
                !is_main_query() || // Skip if not main query.
                (isset($footnoteOptions['hide']) &&
                    $footnoteOptions['hide']) // Skip if option 'hide' is set.
            ) return '';


            /* Prepare return string containing the footnote list in html. */
            $footnotesHtml = '';


            /* Prepare footnote content. -----------------------------------------------------------------------------*/
            $footnoteContent = '';

            /* Add filter before footnote list. (To add additional content before footnote). */
            $footnoteContent = apply_filters('oes_before_footnote', $footnoteContent);


            /* Prepare list with footnotes. --------------------------------------------------------------------------*/
            $footnoteList = '';
            foreach ($this->footnotes as $count => $footnote) {
                $footnoteList .= sprintf('<li>' .
                    '<span id="oes-footnote-bottom-%1s-%2s" class="oes-anchor"></span>%3s' .
                    '<a id="oes-footnote-to-top" href="%4s"></a></li>',
                    esc_attr($count),
                    get_the_ID(),
                    wp_kses_post($footnote),
                    esc_url('#oes-footnote-' . $count . '-' . get_the_ID())
                );
            }

            /* Wrap footnote list.*/
            $footnoteContent .= '<ol id="oes-footnotes">' . $footnoteList . '</ol>';

            /* Add filter after footnote list. (To add additional content after footnote). */
            $footnoteContent = apply_filters('oes_after_footnote', $footnoteContent);

            /* Add list to html. */
            $footnotesHtml .= $footnoteContent;

            /* Add filter before returning footnotes in html. (To add additional content before displaying the
            footnote). */
            $footnotesHtml = apply_filters('oes_footnote_list_output', $footnotesHtml);

            /* Search content for shortcodes and filter shortcodes through their hooks before returning.  */
            return do_shortcode($footnotesHtml);
        }
    }

    //initialize processing
    new Footnotes();

    //initialize style and scripts
    OES()->assets->add_style('oes-footnotes', OES_PATH_RELATIVE . '/assets/css/oes-footnotes.css', [], oes_get_version(), false);
    OES()->assets->add_style('qtipstyles', OES_PATH_RELATIVE . '/assets/js/qtip/jquery.qtip.min.css', [], oes_get_version(), false);
    OES()->assets->add_script('qtip', OES_PATH_RELATIVE . '/assets/js/qtip/jquery.qtip.min.js', ['jquery'], oes_get_version(), true);
    OES()->assets->add_script('qtipcall', OES_PATH_RELATIVE . '/assets/js/qtip/jquery.qtipcall.js', ['jquery', 'qtip'], oes_get_version(), true);

endif;