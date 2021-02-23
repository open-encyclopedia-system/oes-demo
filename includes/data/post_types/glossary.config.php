<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_GLOSSARY,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Glossary Entry',
        'plural' => 'Glossary',
    ],

    'description' => 'A glossary entry is part of a glossary, i.e. an alphabetical list of difficult, 
    technical, or foreign words in a text along with explanations of their meanings.',

    'supports' => ['title', 'editor', 'custom-fields'],

    'has_archive' => true,

    Post_Type::ENABLE_TRANSLATING => true,

    Post_Type::TRANSLATING_LANGUAGES => ['primary' => ['label' => 'English', 'identifier' => 'english'],
        'secondary' => ['label' => 'Deutsch', 'identifier' => 'german']],

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        Post_Type::FIELD_EDITING_STATUS => [
            'label' => 'Editing Status',
            'type' => 'select',
            'choices' => Post_Type::SELECT_EDITING_STATUS,
            'instructions' => 'Choose the editorial status. This is an status used for internal purposes inside the 
            editing layer only.'
        ],

        'oes_demo_glossary_meta_tab' => [
            'type' => 'tab',
            'label' => 'Metadata'
        ],

        'oes_demo_glossary_title' => [
            'label' => 'Display Article Title',
            'type' => 'text',
            'instructions' => 'Enter an article title to be displayed in the frontend. If blank, the default is the 
            post title above.'
        ],

        'oes_demo_glossary_author' => [
            'label' => 'Authors',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_CONTRIBUTOR],
            'instructions' => 'Choose the contributing authors.'
        ],

        'oes_demo_glossary_publication_date' => [
            'label' => 'Publication Date',
            'type' => 'date_picker',
            'instructions' => 'Choose the publication date.',
        ],

        'oes_demo_glossary_latest_change' => [
            'label' => 'Latest Change',
            'type' => 'date_picker',
            'instructions' => 'Latest update date.',
        ],

        'oes_demo_glossary_content_tab' => [
            'type' => 'tab',
            'label' => 'Content'
        ],

        'oes_demo_glossary_content' => [
            'label' => 'Entry Content',
            'type' => 'wysiwyg',
            'instructions' => 'Insert the content of the glossary entry.'
        ],

    ],

];
