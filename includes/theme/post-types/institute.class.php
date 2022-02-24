<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Institution
 */
if (class_exists('OES_Post')) :

    class Demo_Institution extends OES_Post
    {

        // Overwrite parent function
        function modify_metadata($field, $loop): array
        {
            /* replace gnd ID with shortcode */
            if ($field['key'] == 'field_demo_institution__gnd_id')
                $field['value-display'] = sprintf('<a href="%s" target="_blank">%s</a>%s',
                    'https://d-nb.info/gnd/' . $field['value'],
                    'https://d-nb.info/gnd/' . $field['value'],
                    '[gndlink id="' . $field['value'] . '" label=""]'
                );
            return $field;
        }

    }
endif;