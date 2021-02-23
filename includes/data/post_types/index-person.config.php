<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_INDEX_PERSON,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Person',
        'plural' => 'People',
    ],

    'description' => 'Post type to describe the properties of a person, including name, authorative 
    data etc. Also used to build an index for encyclopaedic entries.',

    'supports' => ['title', 'editor', 'custom-fields'],

    'has_archive' => true,

    'menu_icon' => 'secondary',

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        'oes_demo_index_person_name' => [
            'type' => 'text',
            'label' => 'Name',
            'required' => true
        ],

        'oes_demo_index_person_family_name' => [
            'type' => 'text',
            'label' => 'Family Name',
        ],

        'oes_demo_index_person_given_name' => [
            'type' => 'text',
            'label' => 'Given Name',
        ],

        'oes_demo_index_person_gnd' => [
            'type' => 'text',
            'label' => 'GND (Normeintrag)',
        ],

        'oes_demo_index_person_gnd_nr' => [
            'type' => 'text',
            'label' => 'GND number (GND-Kennung)',
            'instructions' => 'Enter the GND number as part of http://d-nb.info/gnd/[GND number].'
        ],

        'oes_demo_index_person_countries' => [
            'label' => 'Countries',
            'type' => 'select',
            'choices' => OES_Project_Config::COUNTRIES,
            'multiple' => true,
            'ui' => true
        ],

        'oes_demo_index_person_living_dates_start' => [
            'label' => 'Biographical Data (Birth)',
            'type' => 'range',
            'min' => -2000,
            'max' => 2100
        ],

        'oes_demo_index_person_living_dates_end' => [
            'label' => 'Biographical Data (Death)',
            'type' => 'range',
            'min' => -2000,
            'max' => 2100
        ],

        'oes_demo_index_articles' => [
            'label' => 'Connected Article',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_ARTICLE],
        ],
    ]
];