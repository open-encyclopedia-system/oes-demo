<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Place
 */
if (class_exists('Demo_Post')) :

    class Demo_Place extends Demo_Post
    {
        //Overwrite parent
        function modify_content(array $contentArray): array
        {
            if (function_exists('oes_map_HTML'))
                $contentArray['300_map'] = oes_map_HTML([
                    'ids' => $this->object_ID
                ]);

            return $contentArray;
        }
    }
endif;