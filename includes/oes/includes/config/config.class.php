<?php

namespace OES\Config;


/**
 * Class Option containing constants for option processing.
 *
 * @package OES\Config
 */
abstract class Option
{

    /**
     * A constant containing the slug for the admin option page.
     */
    const ADMIN_PAGE_MAIN_SLUG = 'oes_settings';

    /**
     * A constant containing the option key for options concerning the OES Theme.
     */
    const THEME = 'oes_theme';

    /**
     * A constant containing the option key for options concerning the post title to be displayed and lists to be
     * ordered by.
     */
    const THEME_TITLE = 'oes_theme_title';

    /**
     * A constant containing the option key for theme search options.
     */
    const THEME_SEARCH = 'oes_theme_search';

    /**
     * A constant containing the option key for options concerning general post type settings like the data source.
     */
    const POST_TYPE_GENERAL = 'oes_post_types_general';

    /**
     * A constant containing the option key for options concerning post types such as parameters for the WordPress
     * function register_post_type while registering the post type.
     */
    const POST_TYPE = 'oes_post_types';

    /**
     * A constant containing the option key for options enabling a registration of taxonomies for post types.
     */
    const POST_TYPE_X_TAXONOMY = 'oes_post_type_x_taxonomy';

    /**
     * A constant containing the option key for relation inheritance between two post types.
     */
    const POST_TYPE_RELATIONSHIP = 'oes_post_types_relations';

    /**
     * A constant containing the option key for options concerning taxonomies.
     */
    const TAXONOMY = 'oes_taxonomies';

    /**
     * A constant containing the option key for footnotes.
     */
    const FOOTNOTES = 'oes_footnotes';

    /**
     * A constant containing the option key for the theme frontpage.
     */
    const FRONTPAGE = 'oes_frontpage';

    /**
     * A constant containing the option key for images.
     */
    const IMAGE = 'oes_image';

    /**
     * A constant containing the option key for images.
     */
    const IMAGE_CREDIT = 'oes_image_credit';

    /**
     * A constant containing the dummy text for (option) text input fields.
     */
    const TEXT_FIELD_DUMMY = 'Place text here';

    /**
     * A constant containing the option key for messages.
     */
    const OES_MESSAGE = 'oes_message';
}



/**
 * TODO @2.0 Roadmap : add further documentation
 * Class Post_Type
 * @package OES\Config
 */
abstract class Post_Type
{

    /**
     *
     */
    const FIELD_MASTER_ID = 'oes_version_master_id';
    /**
     *
     */
    const FIELD_VERSION_IDS = 'oes_version_children_ids';
    /**
     *
     */
    const FIELD_CURRENT_VERSION = 'oes_version_current';
    /**
     *
     */
    const FIELD_TRANSLATION_FROM = 'oes_translation_from';
    /**
     *
     */
    const FIELD_TRANSLATION_TO = 'oes_translation_to';

    /**
     *
     */
    const ACF_FIELD_VERSION = 'oes_post_version';

    /* args for register_post_type -----------------------------------------------------------------------------------*/
    /* register post type */
    /**
     *
     */
    const POST_TYPE = 'post_type';

    /**
     *
     */
    const DTM_CLASS = 'dtm_class';

    /* versioning */
    /**
     *
     */
    const ENABLE_VERSIONING = 'enable_versioning';
    /**
     *
     */
    const VERSION_INHERIT_FIELDS = 'inherit_fields';
    /**
     *
     */
    const VERSION_INHERIT_TERMS = 'inherit_terms';


    /* translating */
    /**
     *
     */
    const ENABLE_TRANSLATING = 'enable_translating';
    /**
     *
     */
    const TRANSLATING_LANGUAGES = 'translating_languages';

    //TODO @2.0 should be defined in project?
    /**
     *
     */
    const DEFAULT_PRIMARY_LANGUAGE = ['label' => 'English', 'identifier' => 'english'];
    /**
     *
     */
    const DEFAULT_SECOND_LANGUAGE = ['label' => 'Deutsch', 'identifier' => 'german'];

    /**
     *
     */
    const ATTACHMENT = 'attachment';
    /**
     *
     */
    const IS_IMAGE = 'is_image';
    /**
     *
     */
    const DONT_ADD = 'dont_add_post_type';

    // Default args for register_post_type
    /**
     *
     */
    const ARGS = [
        'description' => ['description', ''],
        'public' => ['public', true],
        'publicly_queryable' => ['publicly_queryable', true],
        'show_ui' => ['show_ui', true],
        'show_in_rest' => ['show_in_rest', false],
        'rest_base' => ['rest_base', ''],
        'menu_icon' => ['menu_icon', null],
        'has_archive' => ['has_archive', false],
        'show_in_menu' => ['show_in_menu', true],
        'exclude_from_search' => ['exclude_from_search', false],
        'capability_type' => ['capability_type', 'post'],
        'map_meta_cap' => ['map_meta_cap', true],
        'hierarchical' => ['hierarchical', false],
        'query_var' => ['query_var', true],
        'menu_position' => ['menu_position', null],
        'show_in_nav_menus' => ['show_in_nav_menus', true],
        'show_in_admin_bar' => ['show_in_admin_bar', true],
        'register_meta_box_cb' => ['register_meta_box_cb', null],
        'can_export' => ['can_export', true],
        'delete_with_user' => ['delete_with_user', null],
        '_builtin' => ['_builtin', false],
        '_edit_link' => ['_edit_link', 'post.php?post=%d'],
        'rest_controller_class' => ['rest_controller_class', 'WP_REST_Blocks_Controller'],
    ];

    /**
     *
     */
    const LABELS_PLURAL = 'plural';

    /**
     *
     */
    const ARGS_LABELS = [
        'name' => ['name', 'Post'],
        'singular_name' => ['singular_name', 'Post'],
        'add_new' => ['add_new', 'Add new'],
        'add_new_item' => ['add_new_item', 'Add New Post'],
        'edit_item' => ['edit_item', 'Edit Post'],
        'new_item' => ['new_item', 'New Post'],
        'view_item' => ['view_item', 'View Post'],
        'view_items' => ['view_items', 'View Posts'],
        'search_items' => ['search_items', 'Search item'],
        'not_found' => ['not_found', 'No posts found'],
        'not_found_in_trash' => ['not_found_in_trash', 'No posts found in trash'],
        'parent_item_colon' => ['parent_item_colon', 'Parent Page'],
        'all_items' => ['all_items', 'All Posts'],
        'archives' => ['archives', 'Post Archives'],
        'attributes' => ['attributes', 'Post Attributes'],
        'insert_into_item' => ['insert_into_item', 'Insert in to post'],
        'uploaded_to_this_item' => ['uploaded_to_this_item', 'Uploaded to this post'],
        'featured_image' => ['featured_image', 'Featured image'],
        'set_featured_image' => ['set_featured_image', 'Set featured image'],
        'remove_featured_image' => ['remove_featured_image', 'Remove featured image'],
        'use_featured_image' => ['use_featured_image', 'Use as featured image'],
        'menu_name' => ['menu_name', 'oesTODO'],
        'filter_items_list' => ['filter_items_list', 'Filter posts list'],
        'items_list_navigation' => ['items_list_navigation', 'Post list navigation'],
        'items_list' => ['items_list', 'Posts list'],
        'item_published' => ['item_published', 'Post published.'],
        'item_published_privately' => ['item_published_privately', 'Post published privately.'],
        'item_reverted_to_draft' => ['item_reverted_to_draft', 'Post reverted to draft.'],
        'item_scheduled' => ['item_scheduled', 'Post scheduled.'],
        'item_updated' => ['item_updated', 'Post updated.'],
    ];

    /* post type values ----------------------------------------------------------------------------------------------*/

    /* status */
    /**
     *
     */
    const FIELD_EDITING_STATUS = 'oes_editing_status';

    /**
     *
     */
    const STATUS_NEW = 'new';
    /**
     *
     */
    const STATUS_IN_PROCESS = 'in process';
    /**
     *
     */
    const STATUS_IN_REVIEW = 'in review';
    /**
     *
     */
    const STATUS_READY = 'ready for publication';
    /**
     *
     */
    const STATUS_PUBLISHED = 'published';
    /**
     *
     */
    const STATUS_LOCKED = 'locked';
    /**
     *
     */
    const STATUS_DELETED = 'deleted';
    /**
     *
     */
    const SELECT_EDITING_STATUS = [
        self::STATUS_NEW => self::STATUS_NEW,
        self::STATUS_IN_PROCESS => self::STATUS_IN_PROCESS,
        self::STATUS_IN_REVIEW => self::STATUS_IN_REVIEW,
        self::STATUS_READY => self::STATUS_READY,
        self::STATUS_PUBLISHED => self::STATUS_PUBLISHED,
        self::STATUS_LOCKED => self::STATUS_LOCKED,
        self::STATUS_DELETED => self::STATUS_DELETED
    ];

}

/**
 * Class Taxonomy containing constants for taxonomies.
 *
 * @package OES\Config
 */
abstract class Taxonomy
{

    /* args for register_post_type -----------------------------------------------------------------------------------*/

    /**
     * A constant containing the key for a taxonomy to be registered.
     */
    const TAXONOMY = 'taxonomy';

    /**
     * An array of constants containing parameters for register_taxonomy,
     * see https://developer.wordpress.org/reference/functions/register_taxonomy/.
     */
    const ARGS = [
        'description' => ['description', ''],
        'public' => ['public', true],
        'publicly_queryable' => ['publicly_queryable', true],
        'hierarchical' => ['hierarchical', false],
        'show_ui' => ['show_ui', true],
        'show_in_menu' => ['show_in_menu', true],
        'show_in_nav_menus' => ['show_in_nav_menus', true],
        'show_in_rest' => ['show_in_rest', false],
        'rest_base' => ['rest_base', ''],
        'rest_controller_class' => ['rest_controller_class', 'WP_REST_Blocks_Controller'],
        'show_tagcloud' => ['show_tagcloud', true],
        'show_in_quick_edit' => ['show_in_quick_edit', true],
        'show_admin_column' => ['show_admin_column', false],
        //'meta_box_cb' => ['meta_box_cb', false],
        //'meta_box_sanitize_cb' => ['meta_box_sanitize_cb', false],
        //'query_var' => ['query_var', true],
        //'update_count_callback' => ['update_count_callback', null],
        //'_builtin' => ['_builtin', false],
    ];

    /**
     * An array of constants containing taxonomy labels for register_taxonomy,
     * see https://developer.wordpress.org/reference/functions/get_taxonomy_labels/.
     */
    const ARGS_LABELS = [
        'name' => ['name', 'Tags'],
        'singular_name' => ['singular_name', 'Tag'],
        'search_items' => ['search_item', 'Search Tags'],
        'popular_items' => ['popular_items', 'Popular Tags'],
        'all_items' => ['all_items', 'All Tags'],
        'parent_item' => ['parent_item', 'Parent Tag'],
        'parent_item_colon' => ['parent_item_colon', 'Parent Tag:'],
        'edit_item' => ['edit_item', 'Edit Tag'],
        'view_item' => ['view_item', 'View Tag'],
        'update_item' => ['update_item', 'Update Tag'],
        'add_new_item' => ['add_new_item', 'Add New Tag'],
        'new_item_name' => ['new_item_name', 'New Tag Name'],
        'separate_items_with_commas' => ['separate_items_with_commas', 'Separate tags with commas'],
        'add_or_remove_items' => ['add_or_remove_items', 'Add or remove tags'],
        'choose_from_most_used' => ['choose_from_most_used', 'Choose from the most used tags'],
        'not_found' => ['not_found', 'No tags found'],
        'no_terms' => ['no_terms', 'No tags'],
        'items_list_navigation' => ['items_list_navigation', ''],
        'items_list' => ['items_list', ''],
        'most_used' => ['most_used', 'Most Used'],
        'back_to_items' => ['back_to_items', ''],
    ];

}



/**
 * Class Admin
 * @package OES\Config
 */
abstract class Admin
{

    /**
     *
     */
    const CUSTOM_OES_LOGO_PATH = OES_PATH_RELATIVE_PART . '/assets/images/oes_cubic_512.png';
    /**
     *
     */
    const CUSTOM_MENU_ICON_PATH = OES_PATH_RELATIVE_PART . '/assets/images/oes_cubic_18x18.png';
    /**
     *
     */
    const CUSTOM_MENU_ICON_PATH_SECOND = OES_PATH_RELATIVE_PART . '/assets/images/oes_cubic_18x18_second.png';
    /**
     *
     */
    const CUSTOM_MENU_ICON_PATH_MASTER = OES_PATH_RELATIVE_PART . '/assets/images/oes_cubic_18x18_master.png';
}

/**
 * Class ACF
 * @package OES\Config
 */
abstract class ACF
{

    /**
     *
     */
    const FORM_ID = 'acf_form_id';

    /* args for acf_add_local_field_group ----------------------------------------------------------------------------*/
    /**
     *
     */
    const FIELD_GROUP_KEY = 'key';
    /**
     *
     */
    const FIELD_GROUP_TITLE = 'title';
    /**
     *
     */
    const FIELD_GROUP_FIELDS = 'fields';
    /**
     *
     */
    const FIELD_GROUP_FIELDS_MASTER = 'master_fields';
    /**
     *
     */
    const FIELD_GROUP_LOCATION = 'location';
    /**
     *
     */
    const FIELD_GROUP_MENU_ORDER = 'menu_order';
    /**
     *
     */
    const FIELD_GROUP_POSITION = 'position';
    /**
     *
     */
    const FIELD_GROUP_STYLE = 'style';
    /**
     *
     */
    const FIELD_GROUP_LABEL_PLACEMENT = 'label_placement';
    /**
     *
     */
    const FIELD_GROUP_INSTRUCTION_PLACEMENT = 'instruction_placement';
    /**
     *
     */
    const FIELD_GROUP_HIDE_ON_SCREEN = 'hide_on_screen';


}