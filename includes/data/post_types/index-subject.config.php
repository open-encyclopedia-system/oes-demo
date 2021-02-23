<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_INDEX_SUBJECT,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Subject',
        'plural' => 'Subjects',
    ],

    'description' => 'Post type to describe a subject. Used to build an index for encyclopaedic 
    entries.',

    'supports' => ['title', 'editor', 'custom-fields'],

    'has_archive' => true,

    'menu_icon' => 'secondary',

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        'oes_demo_index_subject_display' => [
            'type' => 'wysiwyg',
            'label' => 'Description for Display',
        ],

        'oes_demo_index_articles' => [
            'label' => 'Connected Article',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_ARTICLE],
        ],
    ]
];