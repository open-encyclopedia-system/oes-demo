<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Place
 */
if (class_exists('Demo_Post')) :

    class Demo_Place extends Demo_Post
    {

        /** @inheritdoc */
        function modify_content(array $contentArray): array
        {
            // include map representation if OES Module is active
            if (function_exists('oes_map_html'))
                $contentArray['300_map'] = oes_map_html([
                    'ids' => $this->object_ID
                ]);
            return $contentArray;
        }
    }
endif;