<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Event
 */
if (class_exists('Demo_Post')) :

    class Demo_Event extends Demo_Post
    {

        //Overwrite parent
        function modify_content(array $contentArray): array
        {
            /* prepare label */
            $start = $this->fields['field_demo_event__time_start']['value-display'] ?? '';
            $end = $this->fields['field_demo_event__time_end']['value-display'] ?? '';
            $labelStart = ($this->language === 'language0') ?
                $this->fields['field_demo_event__time_start_label']['value-display'] :
                $this->fields['field_demo_event__time_start_label_' . $this->language]['value-display'];
            $labelEnd = ($this->language === 'language0') ?
                $this->fields['field_demo_event__time_end_label']['value-display'] :
                $this->fields['field_demo_event__time_end_label_' . $this->language]['value-display'];

            $label = [];
            if (!empty($start))
                $label[] = !empty($labelStart) ? $labelStart : str_replace('/', '.', $start);
            if (!empty($end) && $labelEnd != 'hidden') $label[] = !empty($labelEnd) ? $labelEnd : str_replace('/', '.', $end);

            if (!empty($label))
                $contentArray['100_subtitle'] = '<div class="oes-post-indirect-header">' . implode(' - ', $label) . '</div>';

            $contentArray['200_content'] = '<div class="oes-demo-article-content">' .
                ($this->fields['field_demo_event__description']['value-display'] ?? '') . '</div>';

            if ($image = $this->fields['field_demo_event__media']['value'] ?? false)
                $contentArray['210_image'] = oes_get_modal_image($image);

            if (!empty($this->fields['field_demo_event__source']['value-display']))
                $contentArray['250_sources'] = '<div class="oes-demo-article-content">' .
                    '<h2 class="oes-content-table-header">' .
                    $this->fields['field_demo_event__source']['further_options']['label_translation_' . $this->language] .
                    '</h2>' .
                    ($this->fields['field_demo_event__source']['value-display'] ?? '') . '</div>';

            return $contentArray;
        }
    }
endif;