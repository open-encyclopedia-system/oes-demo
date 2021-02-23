<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_BIBLIOGRAPHY,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Bibliographic Entry',
        'plural' => 'Bibliographic Entries',
    ],

    'description' => 'A bibliography is a list of works on a subject or by an author that were used 
                                            or consulted to write a research paper, book or article. 
                                            A single work is referred to as bibliographic entry.',

    'supports' => ['title', 'editor', 'custom-fields'],

    'has_archive' => true,

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        'oes_demo_bibliography_main' => [
            'label' => 'Citation',
            'type' => 'textarea',
            'rows' => 3,
            'instructions' => 'If empty this field will be computed from the data in the info tab with the format <br>
<span style="font-style: italic">[Author(s)] ([Publish date (year)]). [Title], [Publisher], [Place], [Publish date], 
[URL], [Accessed]</span>.<br>Disclaimer: This is an exemplary computation for monographs and not implemented for any 
other item type.'
        ],

        'oes_demo_bibliography_basic_tab' => [
            'type' => 'tab',
            'label' => 'Basic'
        ],

        'oes_demo_bibliography_basic_abstract' => [
            'label' => 'Abstract',
            'type' => 'textarea',
        ],

        'oes_demo_bibliography_details_additional' => [
            'label' => 'Additional Info',
            'type' => 'textarea',
        ],


        'oes_demo_bibliography_details_tab' => [
            'type' => 'tab',
            'label' => 'Info',
        ],

        'oes_demo_bibliography_dummy' => [
            'type' => 'message',
            'label' => '',
            'message' => 'The following fields could be imported from or synced to Zotero. This feature will be ' .
                'available in a future release.'
        ],

        'oes_demo_bibliography_basic_item_type' => [
            'label' => 'Item type',
            'type' => 'select',
            'default_value' => ['book'],
            'choices' => [
                'artwork' => 'Artwork',
                'attachment' => 'Attachment',
                'audioRecording' => 'Audio Recording',
                'bill' => 'Bill',
                'blogPost' => 'Blog Post',
                'book' => 'Book',
                'bookSection' => 'Book Section',
                'case' => 'Case',
                'computerProgram' => 'Computer Program',
                'conferencePaper' => 'Conference Paper',
                'dictionaryEntry' => 'Dictionary Entry',
                'document' => 'Document',
                'email' => 'Email',
                'encyclopediaArticle' => 'Encyclopedia Article',
                'film' => 'Film',
                'forumPost' => 'Forum Post',
                'hearing' => 'Hearing',
                'instantMessage' => 'Instant Message',
                'interview' => 'Interview',
                'journalArticle' => 'Journal Article',
                'letter' => 'Letter',
                'magazineArticle' => 'Magazine Article',
                'manuscript' => 'Manuscript',
                'map' => 'Map',
                'newspaperArticle' => 'Newspaper Article',
                'note' => 'Note',
                'patent' => 'Patent',
                'podcast' => 'Podcast',
                'presentation' => 'Presentation',
                'radioBroadcast' => 'Radio Broadcast',
                'report' => 'Report',
                'statute' => 'Statute',
                'thesis' => 'Thesis',
                'tvBroadcast' => 'TV Broadcast',
                'videoRecording' => 'Video Recording',
                'webpage' => 'Webpage',
            ],
        ],

        'oes_demo_bibliography_basic_website_title' => [
            'label' => 'Website Title',
            'type' => 'url',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '==',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_basic_website_type' => [
            'label' => 'Website Type',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '==',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_basic_title' => [
            'label' => 'Title',
            'type' => 'text',
        ],

        /* TODO @afterZotero : multiple author/author type*/
        'oes_demo_bibliography_details_author' => [
            'label' => 'Author(s)',
            'type' => 'text',
        ],

        'oes_demo_bibliography_details_author_type' => [
            'label' => 'Author Type',
            'type' => 'select',
            'instructions' => 'With the current version of the OES Demo Plugin you can only choose a single type of 
            author(s). A future release will enable you to choose an author type for each author.',
            'default_value' => ['book'],
            'choices' => [
                'author' => 'Author',
                'contributor' => 'Contributor',
                'editor' => 'Editor',
                'series_editor' => 'Series Editor',
                'translator' => 'Translator'
            ]
        ],

        'oes_demo_bibliography_details_series' => [
            'label' => 'Series',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_series_nb' => [
            'label' => 'Series Number',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_volume' => [
            'label' => 'Volume',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_volume_nb' => [
            'label' => '# of Volumes',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_edition' => [
            'label' => 'Edition',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_place' => [
            'label' => 'Place',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_publisher' => [
            'label' => 'Publisher',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_date' => [
            'label' => 'Publish date',
            'type' => 'date_picker',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_pages_nb' => [
            'label' => '# of Pages',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_language' => [
            'label' => 'Language',
            'type' => 'text',
        ],

        'oes_demo_bibliography_details_isbn' => [
            'label' => 'ISBN',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_short_title' => [
            'label' => 'Short Title',
            'type' => 'text',
        ],

        'oes_demo_bibliography_details_url' => [
            'label' => 'URL',
            'type' => 'url',
        ],

        'oes_demo_bibliography_details_accessed' => [
            'label' => 'Accessed',
            'type' => 'date_time_picker',
        ],

        'oes_demo_bibliography_details_archive' => [
            'label' => 'Archive',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_loc_archive' => [
            'label' => 'Loc. in Archive',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_library' => [
            'label' => 'Library Catalog',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_call_number' => [
            'label' => 'Call Number',
            'type' => 'text',
            'conditional_logic' => [[[
                'field' => 'oes_demo_bibliography_basic_item_type',
                'operator' => '!=',
                'value' => 'webpage'
            ]]]
        ],

        'oes_demo_bibliography_details_rights' => [
            'label' => 'Rights',
            'type' => 'text',
        ],

        'oes_demo_bibliography_details_extra' => [
            'label' => 'Extra',
            'type' => 'text',
        ],

        'oes_demo_bibliography_details_date_added' => [
            'label' => 'Date Added',
            'type' => 'date_picker',
        ],

        'oes_demo_bibliography_details_date_modified' => [
            'label' => 'Date Modified',
            'type' => 'date_picker',
        ],

        'oes_demo_bibliography_reference_tab' => [
            'type' => 'tab',
            'label' => 'Related Content'
        ],

        'oes_demo_bibliography_contributor' => [
            'label' => 'Contributors',
            'type' => 'relationship',
            'post_type' => [OES_Project_Config::POST_TYPE_CONTRIBUTOR],
        ],

        'oes_demo_bibliography_article' => [
            'label' => 'Articles',
            'type' => 'relationship',
            'post_type' => [OES_Project_Config::POST_TYPE_ARTICLE],
        ],

        'oes_demo_bibliography_glossary' => [
            'label' => 'Connected Glossary',
            'type' => 'relationship',
            'post_type' => [OES_Project_Config::POST_TYPE_GLOSSARY],
        ],

    ],

];
