<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_CONTRIBUTOR,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Contributor',
        'plural' => 'Contributors',
    ],

    'description' => 'A contributor is a person contributing to the encyclopedia´s content: an 
    author (someone writing articles, glossary entries etc.), a co-author, an editor (responsible for editing and 
    publishing the content), a translator, etc..',

    'supports' => ['title', 'editor', 'custom-fields'],

    'has_archive' => true,

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        Post_Type::FIELD_EDITING_STATUS => [
            'label' => 'Editing Status',
            'type' => 'select',
            'choices' => Post_Type::SELECT_EDITING_STATUS,
            'instructions' => 'Choose the editing status. This is an status used for internal purposes inside the 
                                editorial layer only.'
        ],

        'oes_demo_contributor_meta_tab' => [
            'type' => 'tab',
            'label' => 'Metadata'
        ],

        /* Title of the contributor */
        'oes_demo_contributor_family_name' => [
            'label' => 'Family Name',
            'type' => 'text',
            'required' => 1,
        ],

        'oes_demo_contributor_given_name' => [
            'label' => 'Given Name',
            'type' => 'text',
            ],

        'oes_demo_contributor_acadtitle' => [
            'label' => 'Academic Title',
            'type' => 'select',
            'choices' => [
                '',
                'Prof.',
                'Dr.',
                'Prof. Dr. em,',
            ],
        ],

        'oes_demo_contributor_title' => [
            'label' => 'Display Contributor Title',
            'type' => 'text',
            'instructions' => 'Enter an contributor name to be displayed in the frontend. If blank, the default is: 
                                <br><span style="font-style:italic">[Academic Title] [Given Name] [Family Name]</span>.'
        ],

        'oes_demo_contributor_title_sorting' => [
            'label' => 'Sorting Contributor Title',
            'type' => 'text',
            'instructions' => 'TODO'
        ],

        'oes_demo_contributor_affiliation' => [
            'label' => 'Current Affiliation',
            'type' => 'text',
        ],

        'oes_demo_contributor_role' => [
            'label' => 'Contributor Role',
            'type' => 'select',
            'multiple' => true,
            'ui' => true,
            'choices' => [
                '',
                'author' => 'Author',
                'coauthor' => 'Co-Author',
                'translator' => 'Translator',
                'editor' => 'Editor'
            ],
            'instructions' => 'Define the contributor type by choosing a role to define his or her function inside this
                                encyclopedia.'
        ],

        'oes_demo_contributor_url' => [
            'label' => 'Email Address',
            'type' => 'email',
        ],

        'oes_demo_contributor_website' => [
            'label' => 'Website',
            'type' => 'url',
        ],

        'oes_demo_contributor_file_name' => [
            'label' => 'Name of Authority File',
            'type' => 'select',
            'choices' => [
                '' => '',
                'GND' => 'GND',
                'ORCID' => 'ORCID',
                'Wikidata' => 'Wikidata',
            ],
        ],

        'oes_demo_contributor_file_id' => [
            'label' => 'Authority File ID',
            'type' => 'text',
        ],

        'oes_demo_contributor_relationship_tab' => [
            'type' => 'tab',
            'label' => 'Related Content'
        ],

        'oes_demo_contributor_glossary' => [
            'label' => 'Connected Glossary Entries',
            'type' => 'relationship',
            'post_type' => [
                OES_Project_Config::POST_TYPE_GLOSSARY
            ],
        ],

        'oes_demo_contributor_article' => [
            'label' => 'Connected Articles',
            'type' => 'relationship',
            'post_type' => [
                OES_Project_Config::POST_TYPE_ARTICLE
            ],
        ],

    ],
];