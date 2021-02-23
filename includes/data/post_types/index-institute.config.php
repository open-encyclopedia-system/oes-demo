<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_INDEX_INSTITUTE,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Institution',
        'plural' => 'Institutions',
    ],

    'description' => 'Post type to describe properties of an institution. Also used to build an 
    index for encyclopaedic entries.',

    'supports' => ['title', 'editor', 'custom-fields'],

    'has_archive' => true,

    'menu_icon' => 'secondary',

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        'oes_demo_index_institute_url' => [
            'type' => 'url',
            'label' => 'URL',
        ],

        'oes_demo_index_institute_gnd' => [
            'type' => 'text',
            'label' => 'GND (Normeintrag)',
        ],

        'oes_demo_index_institute_gnd_nr' => [
            'type' => 'text',
            'label' => 'GND number (GND-Kennung)',
            'instructions' => 'Enter the GND number as part of http://d-nb.info/gnd/[GND number].'
        ],

        'oes_demo_index_articles' => [
            'label' => 'Connected Article',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_ARTICLE],
        ],

        'oes_demo_index_description_display' => [
            'type' => 'wysiwyg',
            'label' => 'Description for Display',
        ],
    ]
];