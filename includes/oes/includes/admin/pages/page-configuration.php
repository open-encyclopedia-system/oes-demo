<?php

namespace OES\Admin;

use OES\Config as C;
use OES\ACF as ACF;
use OES\Option\Option;
use OES_Project_Config;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Menu_Page_Configuration')) :

    /**
     * Class Menu_Page_Configuration
     *
     * Class preparing the menu page 'Editorial Layer' and 'OES Theme'.
     */
    class Menu_Page_Configuration extends Menu_Page
    {
        /**
         * Set class parameters. Including page parameters.
         */
        protected function set_page_parameters()
        {
            $this->subPage = true;

            $args = [
                'page_title' => 'Editorial Layer',
                'menu_title' => 'Editorial Layer',
                'menu_slug' => $this->mainSlug . '_backend',
                'position' => 1
            ];
            $this->pageParameters = $args;
        }


        /**
         * Register settings
         */
        function admin_init()
        {
            /* register options --------------------------------------------------------------------------------------*/
            $optionsSettings[] = $this->get_settings_post_types_general();
            $optionsSettings[] = $this->get_settings_post_types();
            $optionsSettings[] = $this->get_settings_post_types_relationships();
            $optionsSettings[] = $this->get_settings_post_types_x_taxonomies();

            //TODO @2.0 Roadmap : move theme settings to other class?
            $optionsSettings[] = $this->get_settings_footnotes();
            $optionsSettings[] = $this->get_settings_frontpage();
            $optionsSettings[] = $this->get_settings_image();
            $optionsSettings[] = $this->get_settings_image_credit();

            //TODO @2.0 Roadmap : make these settings optional?

            foreach (OES_Project_Config::POST_TYPE_ALL as $postType) {
                $optionsSettings[] = $this->get_settings_theme($postType);
                $optionsSettings[] = $this->get_settings_theme_sorting_title($postType);

                /* add postbox for each post type */
                $args['postType'] = $postType;
                add_meta_box('oes-theme-post-type-'. $postType,
                    get_post_type_object($postType)->label,
                    [$this, 'html_postbox_post_types'],
                    'oes-theme-post-type',
                    'normal',
                    'high',
                    $args
                );
            }
            $optionsSettings[] = $this->get_settings_theme_search();

            /* prepare global variable */
            global $oes;

            foreach ($optionsSettings as $settingArray) {
                $settings = new Option();
                $settings->add_settings($settingArray);

                /* add to global variable */
                foreach ($settingArray as $optionKey => $option) {
                    $oes->options[] = $optionKey;
                }
            }



            /* include tool ------------------------------------------------------------------------------------------*/
            oes_include('/includes/admin/tools/tool.class.php', OES_PATH_TEMP);
            oes_include('/includes/admin/tools/tool-import-default-options.php', OES_PATH_TEMP);
            oes_include('/includes/admin/tools/tool-export-default-options.php', OES_PATH_TEMP);

        }


        /**
         * Prepare Settings for tab 'Post Types' General
         *
         * @return array[] Return settings.
         */
        private function get_settings_post_types_general()
        {
            return [
                C\Option::POST_TYPE_GENERAL => [
                    'fields' => [
                        'data_source' => [
                            'type' => 'radio',
                            'title' => 'Data Source',
                            'options' => [
                                'file' => __('Configuration from Backend', 'oes'),
                                'panel' => __('Configuration via Editorial Layer', 'oes')
                            ]
                        ],
                    ]
                ]
            ];
        }


        /**
         * Prepare Settings for tab 'Post Types'
         *
         * @return array[] Return settings.
         */
        private function get_settings_post_types()
        {
            $settings[C\Option::POST_TYPE] = [
                'title' => 'Post Types'
            ];

            /* get all post types */
            foreach (oes_get_post_types() as $postType => $post) {

                /* add new section */
                $sectionID = C\Option::POST_TYPE . '_' . $postType;
                $settings[$sectionID]['title'] = $post->label;
                $settings[$sectionID]['callback'] = [$this, 'html_section_post_type'];

                /* prepare field 'user group' */
                global $wp_roles;
                $userRoles = [
                    'none' => 'All User',
                    'wordpress' => ['optgroup' => 'Wordpress Roles', 'options' => []],
                    'oes' => ['optgroup' => 'OES Roles', 'options' => []]];

                foreach ($wp_roles->roles as $key => $role) {
                    if (oes_starts_with($key, 'oes_')) $userRoles['oes']['options'][$key] = $role['name'];
                    else $userRoles['wordpress']['options'][$key] = $role['name'];
                }


                $fields[$postType . '_new_label'] = ['type' => 'text', 'title' => 'New Label'];

                //TODO @2.0 Roadmap : Include translation
                //TODO @2.0 Roadmap : make versioning optional for configuration in editorial layer

                $fields[$postType . '_show_in_menu'] = ['type' => 'checkbox', 'title' => 'Show in Menu'];
                $fields[$postType . '_show_in_nav_menu'] = ['type' => 'checkbox', 'title' => 'Show in Nav Menu'];
                $fields[$postType . '_hierarchical'] = ['type' => 'checkbox', 'title' => 'Hierarchical'];
                $fields[$postType . '_has_archive'] = ['type' => 'checkbox', 'title' => 'Has Archive'];

                $settings[$sectionID]['fields'] = $fields;
            }
            return $settings;
        }


        /**
         * Prepare Settings for tab 'Post Types', Relationships
         *
         * @return array[] Return settings.
         */
        private function get_settings_post_types_relationships()
        {
            $settings[C\Option::POST_TYPE_RELATIONSHIP] = ['title' => ''];

            $relationshipMatrix = $this->build_post_type_relationship_matrix();

            /* loop through matrix */
            foreach ($relationshipMatrix as $postType => $postTypeData) {

                /* generate fields for options -----------------------------------------------------------------------*/
                $fields = [];

                /* loop through post type connections */
                $postTypeConnections = isset($postTypeData['connections']) ? $postTypeData['connections'] : [];
                foreach ($postTypeConnections as $connection) {

                    /* loop through fields */
                    foreach ($connection as $field) {

                        /* loop through connected post types */
                        foreach ($field['post_type'] as $connectedPostType) {

                            /* get connected fields */
                            if (isset($relationshipMatrix[$connectedPostType]['connections'][$postType])) {

                                /* loop through connected fields */
                                $connectedFields = $relationshipMatrix[$connectedPostType]['connections'][$postType];
                                foreach ($connectedFields as $connectedField) {

                                    /* prepare field to options ------------------------------------------------------*/
                                    $fieldID = $field['key'] . '_x_' . $connectedField['key'];

                                    /* left side (title): base post type and field */
                                    $title = $field['label'];

                                    /* right side (description): connected post type and field */
                                    $description = __('inherit to : ', 'oes') . '<span class="post-type">' .
                                        $relationshipMatrix[$connectedPostType]['sectionTitle'] . '</span> ' . ', ' .
                                        $connectedField['label'];

                                    /* prepare field */
                                    $fields[$fieldID] = ['type' => 'checkbox',
                                        'title' => $title,
                                        'description' => $description];

                                }
                            }
                        }
                    }

                    /* add self reference if connected multiple times to one post type */
                    if (count($connection) > 1) {

                        /* loop through fields inside post type connection */
                        foreach ($connection as $selfReferenceField1) {
                            foreach ($connection as $selfReferenceField2) {

                                /* do not add self reference to same field */
                                if ($selfReferenceField1['key'] != $selfReferenceField2['key']) {

                                    /* prepare field to options ------------------------------------------------------*/
                                    $fieldID = $selfReferenceField1['key'] . '_x_' . $selfReferenceField2['key'];

                                    /* left side (title): base post type and field */
                                    $title = $selfReferenceField1['label'];

                                    /* right side (description): connected post type and field */
                                    $description = __('inherit to : ', 'oes') . '<span class="post-type">' .
                                        $postTypeData['sectionTitle'] . '</span> ' . ', ' .
                                        $selfReferenceField2['label'] . ' (self reference)';

                                    /* prepare field */
                                    $fields[$fieldID] = ['type' => 'checkbox',
                                        'title' => $title,
                                        'description' => $description];
                                }
                            }
                        }
                    }
                }

                /* add new section */
                $sectionID = C\Option::POST_TYPE_RELATIONSHIP . '_' . $postType;

                /* if no fields add text */
                $title = isset($postTypeData['sectionTitle']) ? $postTypeData['sectionTitle'] : '';
                if (empty($fields)) $title .= ' (no connections)';
                $settings[$sectionID]['title'] = $title;

                /* add fields to section -----------------------------------------------------------------------------*/
                $settings[$sectionID]['fields'] = $fields;
            }
            return $settings;
        }


        /**
         * Build a matrix that stores relationship information between post types.
         *
         * @return array[] Return relationship information.
         */
        private function build_post_type_relationship_matrix()
        {
            $relationshipMatrix = [];

            /* loop through all post types */
            foreach (oes_get_post_types() as $postType => $post) {

                /* set post type section title */
                $relationshipMatrix[$postType]['sectionTitle'] = $post->label;

                /* get post type fields */
                $postFields = ACF\get_all_post_type_fields($postType, false);

                /* loop through fields and store fields */
                foreach ($postFields as $field) {

                    /* check for relationship field */
                    if ($field['type'] == 'relationship' || $field['type'] == 'post_object') {

                        /* get related post types */
                        $relatedPostTypes = $field['post_type'];

                        /* store field */
                        $addField = [
                            'key' => $field['key'],
                            'post_type' => $field['post_type'],
                            'label' => $field['label']];


                        foreach ($relatedPostTypes as $relatedPost) {
                            /* add matrix entry for post type connection */
                            $relationshipMatrix[$postType]['connections'][$relatedPost][] = $addField;
                        }
                    }
                }
            }

            return $relationshipMatrix;
        }


        /**
         * Prepare Settings for tab 'Taxonomies' register for post types
         *
         * @return array[] Return settings.
         */
        private function get_settings_post_types_x_taxonomies()
        {

            $settings[C\Option::POST_TYPE_X_TAXONOMY] = [
                'title' => 'Post Type x Taxonomies'
            ];

            /* get all post types */
            $allTaxonomies = get_taxonomies(['_builtin' => false], 'objects');

            /* add fields */
            foreach ($allTaxonomies as $taxonomyID => $taxonomy) {

                /* get description */
                $description = sprintf('%1s%2s<br><code>%3s</code>',
                    $taxonomy->label,
                    empty($taxonomy->description) ? '' :
                        '<br><span class="post-type-label">' . $taxonomy->description . '</span>',
                    $taxonomy->name
                );

                /* add fields for label */
                $sectionID = C\Option::POST_TYPE_X_TAXONOMY . '_' . $taxonomyID;
                $settings[$sectionID]['title'] = __('Taxonomy Label', 'oes');

                $settings[$sectionID]['fields'][$taxonomyID . '_new_label'] = [
                    'type' => 'text',
                    'title' => $description
                ];

                foreach (oes_get_post_types() as $postType => $post) {

                    /* add new section */
                    $sectionID = C\Option::POST_TYPE_X_TAXONOMY . '_' . $postType;
                    $settings[$sectionID]['title'] = $post->label;

                    $settings[$sectionID]['fields'][$postType . '_' . $taxonomyID] = [
                        'type' => 'checkbox',
                        'title' => $description,
                    ];
                }
            }

            return $settings;
        }


        /**
         * Prepare Settings for tab 'Footnotes'
         *
         * @return array[] Return settings.
         */
        private function get_settings_footnotes()
        {
            return [
                C\Option::FOOTNOTES => [
                    'fields' => [
                        'label' => [
                            'type' => 'text',
                            'title' => 'Label (Section)',
                        ],
                        'no_header' => [
                            'type' => 'checkbox',
                            'title' => 'Exclude from Table of Content',
                        ],
                        'hide' => [
                            'type' => 'checkbox',
                            'title' => 'Hide Section',
                        ],
                    ]
                ]
            ];
        }

        /**
         * Prepare Settings for tab 'Frontpage'
         *
         * TODO @2.0 Roadmap : reduce post options (only latest articles..?)
         *
         * @return array[] Return settings.
         */
        private function get_settings_frontpage()
        {

            /* prepare post types */
            $optionsPostsAll = [];

            //foreach (oes_get_post_types() as $postType => $post) {
            foreach (['oes_demo_article'] as $postType) {
                $optionsPostsAll[$postType] = 'Latest Article';// . $post->label;

                /* get all posts */
                $posts = get_posts([
                    'post_type' => $postType,
                    'post_status' => 'publish',
                    'numberposts' => -1
                ]);

                foreach ($posts as $singlePost) {
                    $optionsPostsAll[strval($singlePost->ID)] = $singlePost->post_title .
                        ' (Post ID: ' . $singlePost->ID . ')';
                }

                wp_reset_query();
            }

            /* get all options */
            return [
                C\Option::FRONTPAGE => [
                    'fields' => [
                        'featured_post' => [
                            'type' => 'dropdown',
                            'title' => 'Featured Article',
                            'options' => $optionsPostsAll
                        ],
                        'post_text' => [
                            'type' => 'text',
                            'title' => 'Label',
                        ],
                    ]
                ]
            ];
        }


        /**
         * Prepare Settings for tab 'Image'
         *
         * @return array[] Return settings.
         */
        private function get_settings_image_credit()
        {

            $imageFields = [
                    'none' => '-',
                'title' => 'Title',
                'alt' => 'Alternative Text',
                'caption' => 'Caption',
                'description' => 'Description',
                'date' => 'Publication Date'
            ];

            /* get acf group image fields */
            foreach(acf_get_fields('oes_image_field_group') as $field){
                $imageFields[$field['key']] = $field['label'];
            }

            /* get all options */
            return [
                C\Option::IMAGE_CREDIT => [
                    'fields' => [
                        'include_credit_link' => [
                            'type' => 'checkbox',
                            'title' => 'Include Credit Link in Subtitle',
                        ],
                        'credit_text' => [
                            'type' => 'dropdown',
                            'title' => 'Image Credit Field',
                            'options' => $imageFields,
                        ],
                        'credit_label' => [
                                'type' => 'text',
                            'title' => 'Label',
                            ],
                    ]
                ]
            ];
        }


        /**
         * Prepare Settings for tab 'Image'
         *
         * @return array[] Return settings.
         */
        private function get_settings_image()
        {

            $settings[C\Option::IMAGE] = ['title' => ''];

            /* image fields */
            $imageFields = [
                'title' => 'Title',
                'alt' => 'Alternative Text',
                'caption' => 'Caption',
                'description' => 'Description',
                'date' => 'Publication Date'
            ];

            /* get acf group image fields */
            foreach(acf_get_fields('oes_image_field_group') as $field){
                $imageFields[$field['key']] = $field['label'];
            }

            foreach($imageFields as $key => $label){

                $sectionID = C\Option::IMAGE . '_' . $key;
                $settings[$sectionID]['title'] = $label;

                $fields[$key . '_show_in_subtitle'] = ['type' => 'checkbox', 'title' => 'Show in Subtitle'];
                $fields[$key . '_show_in_panel'] = ['type' => 'checkbox', 'title' => 'Show in Panel'];
                $fields[$key . '_new_label'] = ['type' => 'text', 'title' => 'New Label'];
                $fields[$key . '_prefix'] = ['type' => 'text', 'title' => 'Prefix'];

                $settings[$sectionID]['fields'] = $fields;
            }

            return $settings;
        }


        /**
         * Prepare Settings for tab 'Theme Options' for all fields of a post type
         *
         * @param string $postType A string containing the post type key.
         * @return array[] Return settings.
         */
        private function get_settings_theme($postType)
        {

            $settings[C\Option::THEME . '-' . $postType] = [
                'title' => get_post_type_object($postType)->label
            ];

            /* define field options*/
            $optionIncludeInSearch = ['type' => 'checkbox', 'title' => 'Include in Search'];
            $optionIncludeInMeta = ['type' => 'checkbox', 'title' => 'Metadata'];
            $optionIncludeInArchive = ['type' => 'checkbox', 'title' => 'Archive'];
            $optionLabelInFrontend = ['type' => 'text', 'title' => 'Label For Frontend'];

            /* include title option */
            $settings[C\Option::THEME . '-' . $postType . '-' . 'wp_title'] = [
                'title' => 'Post Title (WordPress)',
                'fields' => [
                    'wp_title-include_in_search' => $optionIncludeInSearch,
                    'wp_title-include_in_meta' => $optionIncludeInMeta,
                    'wp_title-include_in_archive' => $optionIncludeInArchive,
                    'wp_title-label_for_frontend' => $optionLabelInFrontend
                ],
            ];

            /* get all fields for this post type */
            foreach (ACF\get_all_post_type_fields($postType, false) as $fieldKey => $field) {

                /* skip if field is tab */
                if ($field['type'] == 'tab') continue;

                /* prepare option for field */
                $settingsFields = [];

                /* add 'include in search' option if post type has required field type */
                if (in_array($field['type'], ['text', 'textarea', 'wysiwyg', 'url'])) {
                    $settingsFields[$field['name'] . '-include_in_search'] = $optionIncludeInSearch;
                }

                /* add 'include in meta' option */
                $settingsFields[$field['name'] . '-include_in_meta'] = $optionIncludeInMeta;

                /* add 'include in archive' option */
                $settingsFields[$field['name'] . '-include_in_archive'] = $optionIncludeInArchive;

                /* add 'label for frontend' option */
                $settingsFields[$field['name'] . '-label_for_frontend'] = $optionLabelInFrontend;

                /* add new section */
                $settings[C\Option::THEME. '-' . $postType . '-' . $field['name']] = [
                    'title' => $field['label'],
                    'fields' => $settingsFields,
                ];
            }

            return $settings;
        }


        /**
         * Prepare Settings for tab 'Theme Options' for the displayed post title inside the frontend of a post type.
         *
         * @param string $postType A string containing the post type key.
         * @return array[] Return settings.
         */
        private function get_settings_theme_sorting_title($postType)
        {

            /* add WordPress title */
            $allFieldsAllowNull['default'] = 'Same as Title';
            $allFields['wp_title'] = 'Post Title (WordPress)';
            foreach (ACF\get_all_post_type_fields($postType, ['text']) as $fieldKey => $field) {
                $allFields[$fieldKey] = $field['label'];
            }
            $allFieldsAllowNull = array_merge($allFieldsAllowNull, $allFields);

            /* include option for list title  */
            $settings[C\Option::THEME_TITLE . '-' . $postType . '-title'] = [
                'fields' => [
                    'post_title' => ['type' => 'dropdown', 'title' => 'Title (for single display)',
                        'options' => $allFields],
                    'list_title' => ['type' => 'dropdown', 'title' => 'List Title (for list display)',
                        'options' => $allFieldsAllowNull],
                    'list_title_sorting' => ['type' => 'dropdown', 'title' => 'List Sorting Title (for list sorting)',
                        'options' => $allFieldsAllowNull],
                    /* TODO @2.0 Roadmap : move to another option ? */
                    'display_archive_list' => [
                            'type' => 'checkbox',
                        'title' => 'Display Archive as List'
                    ]
                ],
            ];

            return $settings;
        }


        /**
         * Prepare Settings for tab 'Theme Options' for the search options in frontend.
         *
         * @return array[] Return settings.
         */
        private function get_settings_theme_search()
        {
            return [
                C\Option::THEME_SEARCH => [
                    'fields' => [
                        'first_sort' => [
                            'type' => 'dropdown',
                            'title' => 'Sort Search Results By',
                            'options' => ['default' => 'default',
                                'name_asc' => 'Name (ascending)',
                                'name_desc' => 'Name (descending)',
                                'type_asc' => 'Type (ascending)',
                                'type_desc' => 'Type (descending)',
                                'occurrences_asc' => 'Occurrences (ascending)',
                                'occurrences_desc' => 'Occurrences (descending)']
                        ],
                        'secondary_sort' => [
                            'type' => 'dropdown',
                            'title' => 'Secondary Sort Search Results By',
                            'options' => ['default' => 'default',
                                'name_asc' => 'Name (ascending)',
                                'name_desc' => 'Name (descending)',
                                'type_asc' => 'Type (ascending)',
                                'type_desc' => 'Type (descending)',
                                'occurrences_asc' => 'Occurrences (ascending)',
                                'occurrences_desc' => 'Occurrences (descending)']
                        ],
                    ]
                ]
            ];
        }


        /**
         * Callback html function for section for post types
         *
         * @param array $args Arguments passed while processing.
         */
        function html_section_post_type($args)
        {
            if (isset($args['id'])) {

                $postTypeID = substr($args['id'], strlen(C\Option::POST_TYPE) + 1);
                $postType = get_post_type_object($postTypeID);

                if ($postType) {
                    $descriptionFromConfig = (empty($postType->description)) ? '' : $postType->description;
                    $description = '';
                    $description .= '<span class="post-type-label">' . esc_html($descriptionFromConfig) . '</span><br>';
                    $description .= '<code>' . esc_attr($postType->name) . '</code>';

                    ?>
                    <div>
                        <?php echo $description; ?>
                    </div>
                    <?php
                }
            }
        }


        /**
         * Callback html function for taxonomies
         *
         * @param array $args Arguments passed while processing.
         */
        function html_section_taxonomy($args)
        {
            if (isset($args['id'])) {

                $taxonomyID = substr($args['id'], strlen(C\Option::TAXONOMY) + 1);
                $taxonomy = get_taxonomies(['name' => $taxonomyID], 'objects');

                if (isset($taxonomy[$taxonomyID])) {

                    $descriptionFromConfig = (empty($taxonomy[$taxonomyID]->description)) ? '' : $taxonomy[$taxonomyID]->description;
                    $description = '';
                    $description .= '<span class="post-type-label">' . esc_html($descriptionFromConfig) . '</span><br>';
                    $description .= '<code>' . esc_attr($taxonomy[$taxonomyID]->name) . '</code>';

                    ?>
                    <div>
                        <?php echo $description; ?>
                    </div>
                    <?php
                }
            }
        }


        /**
         * Callback html function for page
         */
        function html()
        {
            oes_get_view('view-configuration', [], OES_PATH_TEMP);
        }


        /**
         * Callback function for the meta box of "master" post types.
         *
         * @param WP_Post $post The current post.
         * @param array $callbackArgs Custom arguments passed by add_meta_box.
         */
        function html_postbox_post_types($post, $callbackArgs){
            $postType = $callbackArgs['args']['postType'];
            ?>
            <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
                <div class="settings-wrapper-post-types">
                    <div class="settings-display">
                        <?php
                       settings_fields(C\Option::THEME_TITLE . '-' . $postType . '-title');
                        \OES\Option\oes_do_settings_sections(C\Option::THEME_TITLE . '-' .
                            $postType . '-title', 'plain', ['id' => 'oes-presentation-table']);
                        ?>
                    </div>
                    <div class="oes-settings-submit"><?php
                        submit_button(); ?>
                    </div>
                </div>
            </form>
            <form method="POST" class="settings-post-types-x-taxonomies" action="options.php" style="margin-bottom:50px;">
                <div class="settings-display"><?php
                    settings_fields(C\Option::THEME . '-' . $postType);
                    \OES\Option\oes_do_settings_sections(C\Option::THEME . '-' . $postType,
                        'table',
                        ['class' => 'wp-list-table widefat fixed striped table-view-list taxonomies']);
                    ?>
                </div>
                <div class="oes-settings-submit"><?php
                    submit_button(); ?>
                </div>
            </form>

            <?php
        }

    }

// initialize
    OES()->menuPage['configuration'] = new Menu_Page_Configuration();

endif;

?>