<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Image_License')) :


    /**
     * Class Image_License
     *
     * This class adds a license field to media objects.
     *
     * @package OES\Admin
     */
    class Image_License
    {

        /**
         * Image_License constructor.
         */
        function __construct()
        {
            /* add fields to media object in acf field groups */
            $this->add_media_fields_acf();
        }


        /**
         * Add field group for attachments.
         */
        function add_media_fields_acf()
        {
            /* TODO @2.0 Roadmap : use OES\ACF instead */
            acf_add_local_field_group([
                'key' => 'oes_image_field_group',
                'title' => __('OES media fields', 'oes'),
                'fields' => [
                    [
                        'key' => 'oes_media_license',
                        'label' => __('Media License', 'oes'),
                        'name' => 'oes_media_license',
                        'type' => 'link'
                    ],
                    [
                        'key' => 'oes_media_author_attribution',
                        'label' => __('Author Attribution', 'oes'),
                        'name' => 'oes_media_author_attribution',
                        'type' => 'link',
                        'instructions' => __('Include author attribution.', 'oes')
                    ]
                ],
                'location' => [[[
                    'param' => 'attachment',
                    'operator' => '==',
                    'value' => 'all',
                ]]],
            ]);
        }


    }

    /* instantiate */
    new Image_License();

endif;