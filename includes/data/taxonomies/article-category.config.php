<?php

use OES\Config\Taxonomy;

$config = [

    Taxonomy::TAXONOMY => 'oes_demo_tag_category',

    'labels' => ['name' => 'Categories', 'singular' => 'Category'],

    'hierarchical' => true,

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        'oes_demo_category_description_display' => [
            'type' => 'wysiwyg',
            'label' => 'Description for Display',
        ],

        'oes_tags_category_image' => [
            'type' => 'image',
            'label' => 'Category Image',
        ],

        'oes_tags_category_source' => [
            'type' => 'url',
            'label' => 'Source',
        ],
    ]
];