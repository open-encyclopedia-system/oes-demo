<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Event
 */
if (class_exists('Demo_Post')) :

    class Demo_Event extends Demo_Post
    {
        /**
         * Modify content array to add event time labels, description, and sources.
         *
         * @param array $contentArray The content pieces to modify.
         * @return array Modified content array.
         */
        public function modify_content(array $contentArray): array
        {
            global $oes_language;

            // Prepare start and end time values
            $start = $this->fields['field_demo_event__time_start']['value-display'] ?? '';
            $end = $this->fields['field_demo_event__time_end']['value-display'] ?? '';

            // Determine start and end labels depending on language
            $labelStart = ($oes_language === 'language0') ?
                $this->fields['field_demo_event__time_start_label']['value-display'] ?? '' :
                $this->fields['field_demo_event__time_start_label_' . $oes_language]['value-display'] ?? '';

            $labelEnd = ($oes_language === 'language0') ?
                $this->fields['field_demo_event__time_end_label']['value-display'] ?? '' :
                $this->fields['field_demo_event__time_end_label_' . $oes_language]['value-display'] ?? '';

            $label = [];

            // Compose label parts, falling back to formatted date if label empty
            if (!empty($start)) {
                $label[] = !empty($labelStart) ? $labelStart : str_replace('/', '.', $start);
            }
            if (!empty($end) && $labelEnd !== 'hidden') {
                $label[] = !empty($labelEnd) ? $labelEnd : str_replace('/', '.', $end);
            }

            // If labels exist, add them as subtitle with bold styling
            if (!empty($label)) {
                $contentArray['100_subtitle'] = '<b class="oes-post-indirect-header">' .
                    implode(' - ', $label) .
                    '</b>';
            }

            // Add event description content
            $contentArray['200_content'] = '<div class="oes-demo-article-content">' .
                ($this->fields['field_demo_event__description']['value-display'] ?? '') .
                '</div>';

            // Add source info if available
            if (!empty($this->fields['field_demo_event__source']['value-display'])) {
                $labelTranslation = $this->fields['field_demo_event__source']['further_options']['label_translation_' . $oes_language] ?? '';

                $contentArray['250_sources'] = '<div class="oes-demo-article-content">' .
                    '<h2 class="oes-content-table-header">' . esc_html($labelTranslation) . '</h2>' .
                    ($this->fields['field_demo_event__source']['value-display'] ?? '') .
                    '</div>';
            }

            return $contentArray;
        }
    }
endif;
