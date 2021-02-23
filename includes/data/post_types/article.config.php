<?php

use OES\Config\Post_Type;

$config = [

    Post_Type::POST_TYPE => OES_Project_Config::POST_TYPE_ARTICLE,

    Post_Type::DTM_CLASS => 'OES_Project_DTM',

    'labels' => [
        'singular_name' => 'Article',
        'plural' => 'Articles',
        'add_new' => 'Add new article',
        'all_items' => 'All articles',
    ],

    'description' => 'An article is the main post type in OES. It is used for the individual entries or articles of the 
    encyclopedia or reference work (i.e. lemmas)',

    'has_archive' => true,

    Post_Type::ENABLE_VERSIONING => true,

    Post_Type::ENABLE_TRANSLATING => true,

    Post_Type::TRANSLATING_LANGUAGES => ['primary' => ['label' => 'English', 'identifier' => 'english'],
        'secondary' => ['label' => 'Deutsch', 'identifier' => 'german']],

    Post_Type::VERSION_INHERIT_FIELDS => [[['master' => 'oes_demo_article_master_title',
        'child' => 'oes_demo_article_title']]],

    \OES\Config\ACF::FIELD_GROUP_FIELDS_MASTER => [

        'oes_demo_article_master_title' => [
            'label' => 'Display Article Title',
            'type' => 'text',
            'instructions' => 'Enter an article title to be displayed in the frontend. If blank, the default is the 
                                post title above.' . '<br>This field will be inherited to article version.'
        ],

    ],

    \OES\Config\ACF::FIELD_GROUP_FIELDS => [

        Post_Type::FIELD_EDITING_STATUS => [
            'label' => 'Editing Status',
            'type' => 'select',
            'choices' => Post_Type::SELECT_EDITING_STATUS,
            'instructions' => 'Choose the editing status. This is an status used for internal purposes inside the 
                                editorial layer only. If the post is version controlled and the status is "locked", the 
                                article will not be considered for the current displayed version.'
        ],

        'oes_demo_article_excerpt_tab' => [
            'type' => 'tab',
            'label' => 'Teaser',
        ],

        'oes_demo_article_excerpt' => [
            'label' => 'Article Excerpt',
            'type' => 'textarea',
            'instructions' => 'Enter an article excerpt to be displayed in the frontend, e.g. in the list view of all
                                articles.',
            'rows' => 5,
        ],

        'oes_demo_article_meta_tab' => [
            'type' => 'tab',
            'label' => 'Metadata'
        ],

        'oes_demo_article_title' => [
            'label' => 'Display Article Title',
            'type' => 'text',
            'instructions' => 'Enter an article title to be displayed in the frontend. If blank, the default is the 
                                post title above.'
        ],

        'oes_demo_article_author' => [
            'label' => 'Authors',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_CONTRIBUTOR],
            'instructions' => 'Choose the contributing authors.',
        ],

        'oes_demo_article_pub_date' => [
            'label' => 'Publication Date',
            'type' => 'date_picker',
            'instructions' => 'Choose the publication date.'
        ],

        'oes_demo_article_latest_change' => [
            'label' => 'Latest Change',
            'type' => 'date_picker',
            'instructions' => 'Choose the date when the article was last changed.'
        ],

        Post_Type::ACF_FIELD_VERSION => [
            'label' => 'Display Version Number',
            'type' => 'text',
            'instructions' => 'Create Version Number to be Displayed.'
        ],

        'oes_demo_article_licence_type' => [
            'label' => 'Creative Commons Licence Type',
            'type' => 'select',
            'allow_null' => true,
            'choices' => [
                'https://creativecommons.org/licenses/by/4.0/' => 'Attribution CC BY (4.0)',
                'https://creativecommons.org/licenses/by-sa/4.0/' => 'Attribution ShareAlike CC BY-SA (4.0)',
                'https://creativecommons.org/licenses/by-nd/4.0/' => 'Attribution-NoDerivs CC BY-ND (4.0)',
                'https://creativecommons.org/licenses/by-nc/4.0/' => 'Attribution-NonCommercial CC BY-NC (4.0)',
                'https://creativecommons.org/licenses/by-nc-sa/4.0/' =>
                    'Attribution-NonCommercial-ShareAlike CC BY-NC-SA (4.0)',
                'https://creativecommons.org/licenses/by-nc-nd/4.0/' =>
                    'Attribution-NonCommercial-NoDerivs CC BY-NC-ND (4.0)'
            ],
            'instructions' => 'Select a creative commons license. For more information visit 
                                <a href="https://creativecommons.org/">creativecommons.org</a>.',
        ],

        'oes_demo_article_doi_system' => [
            'label' => 'DOI',
            'type' => 'text',
            'instructions' => 'After publishing you can enter the DOI here. The DOI is a type of Handle System handle, 
            which takes the form of a character string divided into two parts, a prefix and a suffix, separated by a 
            slash. The prefix identifies the registrant of the identifier and the suffix identifies the specific object 
            associated with that DOI. For more information see 
            <a href="https://en.wikipedia.org/wiki/Digital_object_identifier#Nomenclature_and_syntax">
            https://en.wikipedia.org/wiki/Digital_object_identifier#Nomenclature_and_syntax
            </a>.'
        ],

        'oes_demo_article_further_contributors' => [
            'label' => 'Additional Contributor',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_CONTRIBUTOR],
            'instructions' => 'Choose additional contributors (Editors, Translators,...).',
        ],

        'oes_demo_article_content_tab' => [
            'type' => 'tab',
            'label' => 'Content',
        ],

        'oes_demo_article_content' => [
            'label' => 'Article Content',
            'type' => 'wysiwyg',
            'instructions' => 'Insert the content of the article.<br><span style="font-style:italic">Tip: To insert an 
                                image, add the image to media and copy the link. Switch editor to "Text" and insert an 
                                "img" tag. Follow the pop-up instructions.<br>The headings "Heading 1",... , 
                                "Heading 6" will be used to create the table of contents for the article inside the OES 
                                Demo WordPress Theme.</span>'
        ],

        'oes_demo_article_citation_style' => [
            'label' => 'Citation',
            'type' => 'wysiwyg',
            'instructions' => 'Enter a citation style determining how this article is to be cited.'
        ],

        'oes_demo_article_media_tab' => [
            'type' => 'tab',
            'label' => 'Media'
        ],

        'oes_demo_article_media_gallery' => [
            'label' => 'Featured Image',
            'type' => 'image',
            'instructions' => 'Add an image to the article. This image will be displayed as a thumbnail for a featured 
                                article on the front page of the OES Demo WordPress Theme. You can add multiple images 
                                if you buy the licenced pro version of ACF.'
        ],

        'oes_demo_article_relationship_tab' => [
            'type' => 'tab',
            'label' => 'References'
        ],

        'oes_demo_article_biblio' => [
            'label' => 'Connected Bibliography Entries',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_BIBLIOGRAPHY],
        ],

        'oes_demo_article_related_content_tab' => [
            'type' => 'tab',
            'label' => 'Related Content'
        ],

        'oes_demo_article_glossary' => [
            'label' => 'Connected Glossary Entries',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_GLOSSARY],
        ],

        'oes_demo_article_article' => [
            'label' => 'Connected Articles',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_ARTICLE],
        ],

        'oes_demo_article_index_person' => [
            'label' => 'Connected Index Person',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_INDEX_PERSON],
        ],

        'oes_demo_article_index_institute' => [
            'label' => 'Connected Index Institution',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_INDEX_INSTITUTE],
        ],

        'oes_demo_article_index_place' => [
            'label' => 'Connected Index Place',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_INDEX_PLACE],
        ],

        'oes_demo_article_index_subject' => [
            'label' => 'Connected Index Subject',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_INDEX_SUBJECT],
        ],

        'oes_demo_article_index_time' => [
            'label' => 'Connected Index Time',
            'type' => 'relationship',
            'filters' => ['search'],
            'post_type' => [OES_Project_Config::POST_TYPE_INDEX_TIME],
        ],

        'oes_demo_article_related_link_tab' => [
            'type' => 'tab',
            'label' => 'Related Links',
        ],

        'oes_demo_article_related_link_dummy' => [
            'type' => 'message',
            'label' => '',
            'message' => 'You can link the article with upto 5 URLs. If your project uses the ACF Pro Plugin you ' .
            'could change these fields to a ACF repeater field for a flexible number of URLs specific for each ' .
            'article.<br>If you are using the OES Demo Theme the following 5 links  - if set - will be included as ' .
                'part of the main content after the content of the field "Article Content".'
        ],

        'oes_demo_article_related_link_1' => [
            'type' => 'link',
            'label' => 'URL 1',
        ],

        'oes_demo_article_related_link_2' => [
            'type' => 'link',
            'label' => 'URL 2',
        ],

        'oes_demo_article_related_link_3' => [
            'type' => 'link',
            'label' => 'URL 3',
        ],

        'oes_demo_article_related_link_4' => [
            'type' => 'link',
            'label' => 'URL 4',
        ],

        'oes_demo_article_related_link_5' => [
            'type' => 'link',
            'label' => 'URL 5',
        ],

    ],
];
