<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_INDEX_PLACE,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Place',
        'plural' => 'Places',
    ],

    'description' => 'Post type to describe properties of a place, including name, authorative data 
    etc. Also used to build an index for encyclopaedic entries.',

    'supports' => ['title', 'editor', 'custom-fields'],

    'has_archive' => true,

    'menu_icon' => 'secondary',

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        'oes_demo_index_place_place' => [
            'type' => 'text',
            'label' => 'Place',
        ],

        'oes_demo_index_place_gnd' => [
            'type' => 'text',
            'label' => 'GND (Normeintrag)',
        ],

        'oes_demo_index_place_gnd_nr' => [
            'type' => 'text',
            'label' => 'GND number (GND-Kennung)',
            'instructions' => 'Enter the GND number as part of http://d-nb.info/gnd/[GND number].'
        ],

        'oes_demo_index_place_geonames' => [
            'type' => 'text',
            'label' => 'GeoNames',
            'instructions' => 'Enter the GeoName.html as part of https://www.geonames.org/[GeoName.html].'
        ],

        'oes_demo_index_place_country' => [
            'label' => 'Country',
            'type' => 'select',
            'allow_null' => true,
            'choices' => OES_Project_Config::COUNTRIES,
        ],

        'oes_demo_index_place_latitude' => [
            'label' => 'Latitude',
            'type' => 'text'
        ],

        'oes_demo_index_place_longitude' => [
            'label' => 'Longitude',
            'type' => 'text'
        ],

        'oes_demo_index_articles' => [
            'label' => 'Connected Article',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_ARTICLE],
        ],

    ]
];