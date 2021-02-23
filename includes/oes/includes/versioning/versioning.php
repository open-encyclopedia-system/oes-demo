<?php

namespace OES\Versioning;

use OES\Config as C;
use OES\ACF as ACF;
use function OES\ACF\get_all_post_type_fields;
use function OES\ACF\get_select_field_value;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Versioning')) :

    /**
     * Class Versioning
     *
     * This class hooks processing concerning the feature "Versioning" to post actions.
     * "Versioning" includes a version controlling post type "Master" and a version controlled post type "Version".
     *
     * The feature "Translating" is a special case of the feature "Versioning" where a post "Origin Post" is linked to a
     * post "Translation" of the same post type. This feature can be combined with a master post type.
     *
     * TODO @2.0 Roadmap : delete children posts are only effective for master post after update.
     * TODO @2.0 Roadmap : this option will be expanded to allow more than one translation.
     */
    class Versioning
    {


        /**
         * Versioning constructor.
         */
        function __construct()
        {
            /* Hide publishing actions for post types that are controlled by a master post type. */
            //add_action('admin_head-post.php', 'admin_hide_publishing_actions');

            /* Hide permalink for master post type. */
            add_filter('get_sample_permalink_html', [$this, 'admin_hide_permalink_for_master_posts'], 10, 2);

            /* Register a new meta box for master post types and post types that are controlled by a master post
            type. */
            add_action('add_meta_boxes', [$this, 'admin_version_meta_boxes']);

            /* Add post meta information for master post types. */
            add_action('save_post', [$this, 'processing_add_master_post_meta'], 10, 2);

            /* When deleting a version of a master post type, delete meta information for master post. */
            add_action('wp_trash_post', [$this, 'processing_delete_from_master_and_versions']);
            add_action('wp_delete_post', [$this, 'processing_delete_from_master_and_versions']);

            /* When creating a new version of a master post type, copy data from master and add post meta data for
            version post. */
            add_action('wp_insert_post', [$this, 'processing_copy_from_master_post'], 10, 1);

            /* When creating a new version of a master post, copy data from current version and update master post. */
            add_filter('edit_form_top', [$this, 'processing_copy_from_current_version']);

        }


        /**
         * Hide publishing actions for post types that are controlled by a master post type.
         */
        function admin_hide_publishing_actions()
        {
            /* get global post variable and OES configurations */
            global $post;

            /* hide publishing action if post type has master post type. */
            if (get_master_post_type($post->post_type)) {
                echo '<style type="text/css">#misc-publishing-actions,#minor-publishing-actions{display:none;}</style>';
            }
        }


        /**
         * Hide permalink for post types that are controlled by a master post type.
         *
         * @param string $return A string containing the permalink before it is being displayed.
         * @param int $post An int representing the current post.
         * @return string Return the permalink or empty string.
         */
        function admin_hide_permalink_for_master_posts($return, $post_id)
        {
            /* check if post type is controlled by a master post type and return empty string */
            $post = get_post($post_id);
            if (get_version_post_type($post->post_type)) return '';

            /* if post type is not controlled by a master post type, return string as it was before the hook. */
            return $return;
        }


        /**
         * Register a meta box for a post type that is controlling other post types ("master" post type) and post types
         * that are controlled by master post types.
         * Meta boxes are sorted alphabetically by default. ACF meta boxes have the context 'normal' and priority
         * 'high'. To place the meta box above the acf form fields use the same parameters.
         * Start new meta box slug with '0' to appear after the title.
         *
         * @param string $post_type A string containing the current post type, passed by hook.
         */
        function admin_version_meta_boxes($post_type)
        {
            /* check if post type is controlling other posts */
            if (get_version_post_type($post_type)) {

                /* set callback arguments */
                $args['post'] = $post_type;
                $args['versionPostType'] = get_version_post_type($post_type);

                add_meta_box('0-oes-version-master',
                    __('Version List', 'oes'),
                    [$this, 'meta_box_master_post'],
                    null,
                    'normal',
                    'high',
                    $args
                );
            }

            /* check if post type is controlled by a master post type or allows translation */
            elseif (get_master_post_type($post_type) || check_if_translation_enabled($post_type)) {

                /* set callback arguments */
                $args['post'] = $post_type;

                add_meta_box('0-oes-version-post',
                    __('Version Information', 'oes'),
                    [$this, 'meta_box_version_post'],
                    null,
                    'normal',
                    'high',
                    $args
                );
            }
        }


        /**
         * Callback function for the meta box of "master" post types.
         *
         * @param WP_Post $post The current post.
         * @param array $callbackArgs Custom arguments passed by add_meta_box.
         */
        function meta_box_master_post($post, $callbackArgs)
        {
            /* get all versions of this master post. */
            $currentValue = get_all_version_ids($post->ID);

            /* sort current versions by ID TODO @2.0 Roadmap : sort by version number? */
            if(!empty($currentValue)) rsort($currentValue);

            /* get current version of this master post. */
            $currentVersion = get_current_version_id($post->ID);

            /* get the slug for editing a new version */
            $slug = admin_url() . 'post-new.php?post_type=' . $callbackArgs['args']['versionPostType'] .
                '&master_id=' . $post->ID;

            /* display version information after the title */
            ?>
            <div id="master-version-information">
                <div class="left">
                    <a href="<?php echo $slug; ?>" class="button button-primary button-large"><?php
                        _e('Create New Version', 'oes'); ?></a>
                </div>
                <div class="right">
                    <table id="versioning-list"><?php

                        /* loop through all versions and link to the post */
                        if(!empty($currentValue)) :
                        foreach ($currentValue as $versionPostID) :
                            $versionPost = get_post($versionPostID);

                            /* check if post is translation from an other post */
                            $translatedFromID = get_origin_version_id($versionPostID);
                            if (!$translatedFromID) :

                                /* display line with post information ------------------------------------------------*/
                                ?>
                                <tr>
                                <td>
                                    <label>
                                        <a href="<?php echo get_edit_post_link($versionPostID); ?>"><?php
                                            /* display status TODO @2.0 display editing status as well?  */
                                            echo $versionPost->post_title . ', ' . $versionPost->post_status; ?>
                                        </a><?php

                                        /* display translations ------------------------------------------------------*/

                                        /* check if there exist translations of this post */
                                        $translatedToID = get_translated_version_id($versionPostID);
                                        if ($translatedToID) :
                                            $translatedPost = get_post($translatedToID);
                                            if ($translatedPost) :?><?php echo ', '; ?>
                                            <a href="<?php echo get_edit_post_link($translatedToID); ?>">
                                                <?php echo _e('Translated Version', 'oes');
                                                ?>
                                                </a><?php
                                            endif;
                                        endif;

                                        /* check if post is current version ------------------------------------------*/
                                        if ($currentVersion == $versionPostID) :?>
                                            <span class="current-version"><?php
                                            _e('- (This is the currently displayed version.)', 'oes'); ?></span><?php
                                        endif; ?>
                                    </label>
                                </td>
                                </tr><?php

                            /* check if there exists a translated version where the origin post is missing -----------*/
                            else :
                                $translatedFromPost = get_post($translatedFromID);
                                if (!$translatedFromPost || $translatedFromPost->post_status == 'trash') :
                                    ?>
                                    <tr>
                                    <td>
                                        <label>
                                            <a href="<?php echo get_edit_post_link($versionPostID); ?>">
                                                <?php echo $versionPost->post_title; ?>
                                            </a><span class="current-version"><?php
                                                _e('- (Origin version of translation is missing.)', 'oes'); ?></span>
                                        </label>
                                    </td>
                                    </tr><?php
                                endif;
                            endif;
                        endforeach; endif; ?>
                    </table>
                </div>
                <div class="version-tip"><?php _e('<strong>Versioning : </strong>The version with the highest version 
                    number will be set as current version and will be displayed in the frontend. If you want to exclude 
                    a post from being set as current version, set the editing status of this post to "locked".',
                        'oes'); ?></div><?php

                /* check if translation enabled for version posts */
                if (check_if_translation_enabled($callbackArgs['args']['versionPostType'])) :
                    ?>
                    <div class="version-tip">
                    <strong><?php _e('Create Translation : ', 'oes'); ?></strong><?php
                    printf(__('The default language for this post is %1s. To create a %2s version of a post use the 
                            translation button inside the post version that is to be translated. After publishing the 
                            post you will be able to switch between the two languages in the OES Demo WordPress Theme 
                            frontend.',
                        'oes'),
                        get_language_string($callbackArgs['args']['versionPostType'],
                            'primary', 'identifier'),
                        get_language_string($callbackArgs['args']['versionPostType'],
                            'secondary', 'identifier')
                    ); ?>
                    </div><?php
                endif; ?>
            </div>
            <?php
        }


        /**
         * Callback function for the meta box of "version" post types. For post types that are controlled by a master
         * post type display a link to the master post and current version after the title.
         *
         * @param WP_Post $post The current post.
         * @param array $callbackArgs Custom arguments passed by add_meta_box.
         */
        function meta_box_version_post($post, $callbackArgs)
        {
            /* check if post has a master post. */
            $masterID = get_master_id($post->ID);

            /* check if translation enabled */
            $translation = check_if_translation_enabled($post->post_type);

            /* get master post */
            if ($masterID) {

                /* get information about master post */
                $masterPost = get_post($masterID);

                /* get current version from master post */
                $currentPost = get_current_version_post($masterID);

                /* display links */
                if ($masterPost) {

                    $label = get_post_type_object($masterPost->post_type)->label;

                    /* link to master post ---------------------------------------------------------------------------*/
                    ?>
                    <div id="version-information">
                        <div>
                            <strong><?php echo $label . ': '; ?></strong>
                            <span><a href="<?php echo get_edit_post_link($masterID); ?>"><?php
                                    echo $masterPost->post_title; ?></a></span>
                        </div><?php

                        /* link to currently displayed version -------------------------------------------------------*/
                        if ($currentPost->ID && $currentPost->ID != $post->ID) :?>
                            <div>
                            <strong><?php _e('Currently Displayed Version:', 'oes'); ?></strong>
                            <span><a href="<?php echo get_edit_post_link($currentPost->ID); ?>"><?php
                                    echo $currentPost->post_title; ?></a></span>
                            </div><?php
                        else:?>
                            <div><strong><?php
                                _e('This is the currently displayed version.', 'oes'); ?></strong></div><?php
                        endif; ?>
                    </div>
                    <div class="version-tip"><?php _e('The version with the highest version number will be set as 
                        current version and will be displayed in the frontend. If you want to exclude a post from being 
                        set as current version, set the editing status of this post to "locked".', 'oes'); ?></div>
                    <?php
                }
            }

            /* If post type has master post type but this post no master ID the post is orphaned. */
            elseif (!$masterID && get_master_post_type($post->post_type)) {

                /* orphaned post -------------------------------------------------------------------------------------*/
                ?>
                <div id="orphaned-post"><?php _e('This post is not linked to a master post. To create an article 
                    version of a master post go to the master post and use the "Create Post" button.<br> This article 
                    will not be displayed in the OES Demo WordPress Theme Frontend.', 'oes') ?>
                </div>
                <?php
            }

            /* Include translation information. */
            if ($translation) {

                /* get the slug for editing a translation */
                $slug = admin_url() . 'post-new.php?post_type=' . $callbackArgs['args']['post'] . '&master_id=' .
                    $masterID . '&translate_from=' . $post->ID;

                /* check if post is translation from other post */
                $translatedFromPost = get_origin_version_post($post->ID);

                /* check if post has a translation post */
                $translatedToPost = get_translated_version_post($post->ID);

                ?>
                <div id="translation"><?php

                    /* check if post is translation of another post and link to this post ----------------------------*/
                    if ($translatedFromPost) :?>
                        <div class="meta-box-inner">
                        <strong><?php _e('This is a translation of the origin post:', 'oes'); ?></strong>
                        <span><a href="<?php echo get_edit_post_link($translatedFromPost->ID); ?>"><?php
                                echo $translatedFromPost->post_title; ?></a></span>
                        </div><?php

                    /* check if post is orphaned translation without origin post -------------------------------------*/
                    elseif (!$translatedFromPost && get_origin_version_id($post->ID)):?>
                        <div class="version-tip"><?php _e('The origin post is missing.', 'oes'); ?></div>
                    <?php

                    /* check if translation exists and display link to translation -----------------------------------*/
                    elseif ($translatedToPost) :?>
                        <div class="meta-box-inner">
                        <strong><?php _e('Übersetzung:', 'oes'); ?></strong>
                        <span><a href="<?php echo get_edit_post_link($translatedToPost->ID); ?>"><?php
                                echo $translatedToPost->post_title; ?></a></span>
                        </div><?php

                    /* else display new translation button -----------------------------------------------------------*/
                    else:?>
                        <a href="<?php echo $slug; ?>" class="button button-primary button-large"><?php
                            printf(__('Create Translation (%s)', 'oes'),
                                get_language_string($post->post_type, 'secondary')
                            );
                            ?></a>
                        <div class="version-tip">
                            <strong><?php _e('Create Translation : ', 'oes'); ?></strong><?php
                            printf(__('The default language for this post is %1s. To create a %2s version of a post use 
                                the translation button inside the post version that is to be translated. After 
                                publishing the post you will be able to switch between the two languages in the OES 
                                Demo WordPress Theme frontend.', 'oes'),
                                get_language_string($post->post_type, 'primary', 'identifier'),
                                get_language_string($post->post_type, 'secondary', 'identifier')
                            ); ?>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <?php
            }
        }


        /**
         * Add post meta information for fields that are master post types.
         *
         * @param int $post_id The current post id.
         * @param WP_Post $post The current post.
         * @return void
         */
        function processing_add_master_post_meta($post_id, $post)
        {
            /* check if post type has master post type, post is published and post is not a translation of another
            post */
            if (get_master_post_type($post->post_type)
                && 'trash' != $post->post_status
                && !get_origin_version_id($post->ID)) {

                /* update master post */
                $masterID = get_master_id($post_id);

                /* break if post has no master post and therefore is orphaned post */
                if (!$masterID) return ; //TODO @2.0 Roadmap : do_action for orphaned post

                /* a) add this post id to version ids of master post */
                $currentValue = get_all_version_ids($masterID) ?
                    get_all_version_ids($masterID) : [];
                if (!in_array($post->ID, $currentValue)) $currentValue = array_merge([$post->ID], $currentValue);
                set_all_version_ids($masterID, $currentValue);

                /* b) check if new post should be current version by comparing the version number */
                $currentVersionID = get_current_version_id($masterID);

                if ($currentVersionID) {

                    /* compare versions */
                    $currentVersionNumber = get_acf_version_field($currentVersionID);
                    $postVersionNumberText = get_acf_version_field($post_id);
                    $postVersionNumber = get_version_number_from_string($postVersionNumberText);

                    /* update master post if version number is bigger than current version number */
                    if (floatval($currentVersionNumber) < floatval($postVersionNumber)) {
                        set_current_version_id($masterID, $post_id);
                    }
                } /* set post as current version of master post */
                else {
                    set_current_version_id($masterID, $post_id);
                }

                /* force update master post */
                wp_update_post(['ID' => $masterID]);
            }

            /* check if post type is master post type ----------------------------------------------------------------*/
            if (get_version_post_type($post->post_type)) {

                /* get all connected posts */
                $allConnectedPosts = get_all_version_ids_from_database($post_id);

                /* a) update current value */
                set_all_version_ids($post_id, $allConnectedPosts['posts']);

                /* b) set version with highest version number as current version, if editing status is not locked. */
                set_current_version_id($post_id, $allConnectedPosts['current_version']);
            }
        }


        /**
         * When creating a new version of a master post type, copy data from master and add post meta data for version
         * post.
         *
         * @param WP_Post $post The current post.
         */
        function processing_copy_from_master_post($post)
        {
            /* check if master id was passed when loading the page */
            $masterID = oes_isset_GET('master_id', false);

            /* if master id exists, get master information */
            if ($masterID) {

                /* update post : set masterID as master id for this post */
                set_master_id($post, $masterID);

                /* check if master post type has fields to be inherited */
                $inheritFields = get_fields_to_be_inherited(get_post_type($masterID));
                if ($inheritFields) {

                    /* loop through fields */
                    foreach ($inheritFields as $field) {
                        $childField = isset($field['child']) ? $field['child'] : false;
                        $masterField = isset($field['master']) ? $field['master'] : false;

                        /* inherit field to child */
                        if ($childField && $masterField) {
                            oes_update_post_meta($post, $childField, ACF\get_acf_field($masterField, $masterID));
                        }
                    }
                }

                /* inherit terms and categories from master */
                $inheritTerms = get_terms_to_be_inherited(get_post_type($masterID));
                if ($inheritTerms) {

                    /* loop through fields */
                    foreach ($inheritTerms as $term) {
                        $masterTerms = get_the_terms($masterID, $term);

                        if ($masterTerms) {

                            /* inherit term to version post */
                            foreach ($masterTerms as $singleTerm) {
                                wp_set_post_terms($post, [$singleTerm->term_id], $term, true);
                            }
                        }
                    }
                }
            }
        }


        /**
         * When deleting a post that is controlled by a master post delete the connected in the master post.
         * When deleting a post that is a translation of a origin post delete the translation in the origin post.
         *
         * @param WP_Post $post The current post.
         */
        function processing_delete_from_master_and_versions($post)
        {

            /* check if post has master post */
            $masterID = get_master_id($post);
            if ($masterID) {

                /* prepare new values for master post */
                $versionsWithoutCurrentPost = NULL;
                $versionsWithoutTranslations = NULL;

                /* get all stored versions from master post */
                $allVersions = get_all_version_ids($masterID);
                if ($allVersions) {

                    /* loop through all versions */
                    foreach ($allVersions as $version) {

                        /* exclude current post */
                        if ($version != $post) {
                            $versionsWithoutCurrentPost[] = $version;

                            /* exclude translations */
                            if (!get_origin_version_id($version)) $versionsWithoutTranslations[] = $version;
                        }
                    }
                }

                /* update master value */
                set_all_version_ids($masterID, $versionsWithoutCurrentPost);

                /* get current version from master post and update master if the current version is the same as deleted
                post */
                $currentVersion = get_current_version_id($masterID);
                if ($currentVersion == $post && $versionsWithoutTranslations) {
                    set_current_version_id($masterID, max($versionsWithoutTranslations));
                }

                /* force update master post */
                wp_update_post(['ID' => $masterID]);

            }

            /* delete the 'translation to' info in the origin post if existing */
            delete_connection_to_translated_version($post);
        }


        /**
         * When creating a new version of a master post, copy data from current version and update master post.
         *
         * @param WP_Post $post The current post.
         */
        function processing_copy_from_current_version($post)
        {
            /* check if master id was passed when loading the page */
            $masterID = oes_isset_GET('master_id', false);
            $translatedFromID = oes_isset_GET('translate_from', false);

            /* if master id exists but this post is not a translation, get master information */
            if ($masterID && !$translatedFromID) {

                /* get title */
                $masterPostDisplay = get_the_title($masterID);

                $maxVersion = get_max_version_number($masterID);
                $newVersion = increment_version_number($maxVersion);

                /* set title */
                $post->post_title = $masterPostDisplay . ' (Version ' . $newVersion . ')';


                /* get field values from current version */
                $currentPostID = get_current_version_id($masterID);


                /* get all fields for this post type */
                $postFields = get_all_post_type_fields($post->post_type, false);

                /* loop through fields and store values for new version */
                foreach ($postFields as $field) {
                    $fieldValue = ACF\get_acf_field($field['key'], $currentPostID);

                    /* strip textarea of html tags */
                    if ($field['type'] == 'textarea') $fieldValue = strip_tags($fieldValue);

                    update_field($field['key'], $fieldValue, $post->ID);
                }

                /* overwrite version field */
                update_field('oes_post_version', $newVersion, $post->ID);

                /* inherit terms */

                /* get taxonomies */
                $taxonomies = get_object_taxonomies($post->post_type);

                /* loop through taxonomies */
                foreach($taxonomies as $taxonomy){

                    /* get terms */
                    $postTerms = get_the_terms($currentPostID, $taxonomy);

                    /* inherit term to version post */
                    foreach ($postTerms as $singleTerm) {
                        wp_set_post_terms($post->ID, [$singleTerm->term_id], $taxonomy, true);
                    }

                }
            }

            /* if translation from id exists, get information from origin post */
            elseif ($translatedFromID) {

                /* set title */
                $titleDisplay = $masterID ? get_the_title($masterID) : get_the_title($translatedFromID);
                $version = get_acf_version_field($translatedFromID);
                $language = get_language_string($post->post_type, 'secondary');

                $titleAppendix = '';
                $titleAppendix .= $version ? 'Version ' . $version : '';
                $titleAppendix .= ($version && $language) ? ', ' : '';
                $titleAppendix .= $language ? $language : '';

                $post->post_title = $titleDisplay . ' (' . $titleAppendix . ')';

                /* get all fields for this post type */
                $postFields = get_all_post_type_fields($post->post_type, false);

                /* loop through fields of origin post and store values for new version */
                foreach ($postFields as $field) {
                    $fieldValue = ACF\get_acf_field($field['key'], $translatedFromID);

                    /* strip textarea of html tags TODO @afterRefactoring : remove_filter wpautop instead of strip_tags */
                    if ($field['type'] == 'textarea') $fieldValue = strip_tags($fieldValue);

                    update_field($field['key'], $fieldValue, $post->ID);
                }

                /* update translation fields for origin post and translated post */
                set_origin_version_id($post->ID, $translatedFromID);

                /* update origin post */
                set_translated_version_id($translatedFromID, $post->ID);
            }
        }

    }

    /* create new instance of feature 'Versioning' and hook processing to post actions. */
    new Versioning();

endif;


/* get post information concerning the feature 'Versioning' ----------------------------------------------------------*/


/**
 * Check if feature 'Translation' is enabled for this post type.
 * 
 * @param string $postType A string containing the post type.
 * @return false|mixed Return false if not enabled.
 */
function check_if_translation_enabled($postType)
{
    return oes_get_global_post_type_settings($postType, 'translating');
}


/**
 * Get fields to be inherited from master post to version post for this post type.
 * 
 * @param string|int $postType A string containing the post type.
 * @return false|mixed Return an array containing the fields to be inherited from master post type for this post type.
 */
function get_fields_to_be_inherited($postType)
{
    return oes_get_global_post_type_settings($postType, 'inherited_fields', true);
}


/**
 * Get terms to be inherited from master post to version post for this post type.
 *
 * @param string|int $postType A string containing the post type.
 * @return false|mixed Return an array containing the terms to be inherited from master post type for this post type.
 */
function get_terms_to_be_inherited($postType)
{
    return oes_get_global_post_type_settings($postType, 'inherited_terms', true);
}


/**
 * Get language text string for translating post types.
 * 
 * @param string|boolean $postType A string containing the post type.
 * @param string $languageKey A string containing the language key. Valid options depend on project (e.g. 'primary', 'secondary', ... )
 * @param string $itemKey Optional string containing the item key.
 * @return false|mixed Returns the language text string or false if not found.
 */
function get_language_string($postType, $languageKey, $itemKey = 'label')
{
    $languages = oes_get_global_post_type_settings($postType, 'translating_languages');
    return isset($languages[$languageKey][$itemKey]) ? $languages[$languageKey][$itemKey] : false;
}


/**
 * Get the version field of this post. This field is an acf field and displayed inside the post edit form.
 * 
 * @param string|int $postID A string containing the post ID.
 * @return mixed Returns a string containing the value of the version field.
 */
function get_acf_version_field($postID)
{
    return ACF\get_acf_field('oes_post_version', $postID);
}


/**
 * Get the version post type for this post type from global settings.
 *
 * @param string $postType A string containing the post type.
 * @return false|mixed Returns the version post type from global settings or false if not found.
 */
function get_version_post_type($postType)
{
    return oes_get_global_post_type_settings($postType, 'version_controlling', true);
}


/**
 * Get the master post type for this post type from global settings.
 *
 * @param string $postType A string containing the post type.
 * @return false|mixed Returns the master post type from global settings or false if not found.
 */
function get_master_post_type($postType)
{
    return oes_get_global_post_type_settings($postType, 'version_controlled_by', false);
}


/**
 * If the post is connected to a master post, get the post ID of the master post. The information is stored as post
 * meta data and not displayed in the post edit form.
 * 
 * @param string|int $postID A string containing the post ID.
 * @return mixed Returns the post ID of the master post or empty if no master post exists.
 */
function get_master_id($postID)
{
    return oes_get_post_meta($postID, C\Post_Type::FIELD_MASTER_ID)[0];
}


/**
 * If the post is connected to a master post, get the master post. 
 *
 * @param string|int $postID A string containing the post ID.
 * @return WP_Post|array|false Returns the master post or false if not found.
 */
function get_master_post($postID)
{
    $masterID = get_master_id($postID);
    return $masterID ? get_post($masterID) : false;
}


/**
 * If the post is a master post and controls post versions get all connected post versions by searching the database 
 * for posts that have post meta data linking the post to the master post. 
 * Search for the current version by comparing version numbers of the found posts and return the maximum version number. 
 * (To compare the numbers the version number will be multiply 1000, which means any version number with more than 3 
 * digits behind the comma will be ignored).
 *
 * @param string|int $masterID A string containing the post ID of the master post.
 * @return mixed Returns an array containing the post ID of the current post version and an array with all post IDs of 
 * connected posts.
 */
function get_all_version_ids_from_database($masterID)
{

    /* get master post type */
    $masterPost = get_post($masterID);
    $masterPostType = $masterPost ? $masterPost->post_type : false;
    $versionPostType = $masterPostType ? get_version_post_type($masterPostType) : false;

    /* return false if post type not found */
    if (!$versionPostType) return false;

    /* get all versions connected to this master post */
    $args = [
        'post_type' => $versionPostType,
        'post_status' => ['publish', 'draft'],
        'meta_key' => C\Post_Type::FIELD_MASTER_ID,
        'meta_value' => $masterID
    ];
    $posts = oes_get_wp_query_posts($args);

    /* loop through all versions and store the post ids */
    $postsNotLocked = [];
    $postIDs = [];

    foreach ($posts as $post) {

        $postIDs[] = $post->ID;

        /* skip locked and translated posts */
        //TODO @2.0 Roadmap : change to registered post status $post->post_status != 'oes-locked'
        if (get_select_field_value('oes_editing_status', $post->ID) != 'locked'
            && $post->post_status != 'draft'
            && !get_origin_version_id($post->ID)) {

            /* get version text, match to pattern #.# and split to pattern */
            $versionText = get_version_number_from_string(get_acf_version_field($post->ID));

            /* To compare version text transform text into integer by multiplying the first digit by 1000 and adding
            the second digit. */
            if ($versionText) $postsNotLocked[$post->ID] = $versionText[1] * 1000 + $versionText[2];
        }
    }

    /* extract current version (maximal version number) */
    $currentVersion = false;
    if (!empty($postsNotLocked)) $currentVersion = array_search(max($postsNotLocked), $postsNotLocked);

    return ['posts' => $postIDs, 'current_version' => $currentVersion];
}


/**
 * If the post is a master post and controls post versions get all post IDs of connected post versions. The information 
 * is stored as post meta data and not displayed in the post edit form.
 * 
 * @param string|int $postID A string containing the post ID.
 * @return mixed Returns an array of all connected post IDs or empty if post meta value does not exist.
 */
function get_all_version_ids($postID)
{
    return oes_get_post_meta($postID, C\Post_Type::FIELD_VERSION_IDS)[0];
}


/**
 * If the post is a master post and controls post versions get all connected post versions. 
 *
 * @param string|int $postID A string containing the post ID.
 * @return mixed Returns an array of all connected posts or false if not found.
 */
function get_all_version_posts($postID)
{
    /* get array with all connected post IDs */
    $allVersionIDs = get_all_version_ids($postID);

    /* loop through all post IDs and get WP_Post object */
    $allVersionPosts = false;
    if ($allVersionIDs) {
        foreach ($allVersionIDs as $version) {
            $allVersionPosts[] = get_post($version);
        }
    }
    
    return $allVersionPosts;
}


/**
 * If the post is a master post and controls post versions get the post ID of the current version. If the post is not a
 * master post, get master post and check for the current version there. The information is stored as post meta data
 * and not displayed in the post edit form.
 *
 * @param string|int $postID A string containing the post ID.
 * @return mixed Returns an string containing the post ID of the current version or empty if post meta value does not
 * exist.
 */
function get_current_version_id($postID)
{
    return oes_get_post_meta(
            get_master_id($postID) ? get_master_id($postID) : $postID,
        C\Post_Type::FIELD_CURRENT_VERSION
    )[0];
}


/**
 * If the post is a master post and controls post versions get the post with the current version.
 *
 * @param string|int $postID A string containing the post ID.
 * @return WP_Post|array|false Returns the current post version or false if not found.
 */
function get_current_version_post($postID)
{
    $currentPostID = get_current_version_id($postID);
    return $currentPostID ? get_post($currentPostID) : false;
}


/**
 * If the post is the translation of an other post get the post ID of the origin post. The information is
 * stored as post meta data and not displayed in the post edit form.
 *
 * @param string|int $postID A string containing the post ID.
 * @return mixed Returns an string containing the post ID of the origin post or empty if post meta value does not
 * exist.
 */
function get_origin_version_id($postID)
{
    return oes_get_post_meta($postID, C\Post_Type::FIELD_TRANSLATION_FROM)[0];
}


/**
 * If the post is the translation of an other post get the post ID of the origin post. The information is
 * stored as post meta data and not displayed in the post edit form.
 *
 * @param string|int $postID A string containing the post ID.
 * @return WP_Post|array|false Returns the origin post version or false if not found.
 */
function get_origin_version_post($postID)
{
    $translatedFrom = get_origin_version_id($postID);
    return $translatedFrom ? get_post($translatedFrom) : false;
}


/**
 * If the post has a translation get the post ID of this post. The information is stored as post meta data and not
 * displayed in the post edit form.
 *
 * @param string|int $postID A string containing the post ID.
 * @return mixed Returns an string containing the post ID of the translated post or empty if post meta value does not
 * exist.
 */
function get_translated_version_id($postID)
{
    return oes_get_post_meta($postID, C\Post_Type::FIELD_TRANSLATION_TO)[0];
}


/**
 * If the post has a translation get the translated post.
 *
 * @param string|int $postID A string containing the post ID.
 * @return WP_Post|array|false Returns the translated post version or false if not found.
 */
function get_translated_version_post($postID)
{
    $translatedTo = get_translated_version_id($postID);
    return $translatedTo ? get_post($translatedTo) : false;
}


/* set post information concerning the feature 'Versioning' ----------------------------------------------------------*/


/**
 * Connect a post to a master post by adding the post ID to the post meta data of the master post. The information is 
 * stored as post meta data and not displayed in the post edit form.
 *
 * @param string|int $postID A string containing the post ID.
 * @param string|int $masterID A string containing the post ID of the master post.
 */
function set_master_id($postID, $masterID)
{
    oes_update_post_meta($postID, C\Post_Type::FIELD_MASTER_ID, $masterID);
}


/**
 * Connect a master post to post versions by updating the post meta data of the master post. The information is
 * stored as post meta data and not displayed in the post edit form.
 *
 * @param string|int $masterID A string containing the post ID.
 * @param array $versionIDs An array containing the post version IDs.
 */
function set_all_version_ids($masterID, $versionIDs)
{
    $oldValue = get_all_version_ids($masterID);
    if ($oldValue != $versionIDs) oes_update_post_meta($masterID, C\Post_Type::FIELD_VERSION_IDS, $versionIDs);
}


/**
 * Connect a master post to the current post version by updating the post meta data of the master post. The information 
 * is stored as post meta data and not displayed in the post edit form.
 *
 * @param string|int $masterID A string containing the post ID.
 * @param string|int $currentPostID A string containing the current version post ID.
 */
function set_current_version_id($masterID, $currentPostID)
{
    $oldValue = get_all_version_ids($masterID);
    if ($oldValue != $currentPostID) oes_update_post_meta($masterID, C\Post_Type::FIELD_CURRENT_VERSION, $currentPostID);
}


/**
 * Connect a post to the origin post version by updating the post meta data of the post. The information
 * is stored as post meta data and not displayed in the post edit form.
 *
 * @param string|int $postID A string containing the post ID.
 * @param string|int $originVersionID A string containing the post ID of the origin post version.
 */
function set_origin_version_id($postID, $originVersionID)
{
    $oldValue = get_origin_version_id($postID);
    if ($oldValue != $originVersionID) oes_update_post_meta($postID, C\Post_Type::FIELD_TRANSLATION_FROM, $originVersionID);
}


/**
 * Connect a post to the translated post version by updating the post meta data of the post. The information
 * is stored as post meta data and not displayed in the post edit form.
 *
 * @param string|int $postID A string containing the post ID.
 * @param string|int $translatedVersionID A string containing the post ID of the translated post version.
 */
function set_translated_version_id($postID, $translatedVersionID)
{
    $oldValue = get_translated_version_id($postID);
    if ($oldValue != $translatedVersionID) oes_update_post_meta($postID, C\Post_Type::FIELD_TRANSLATION_TO, $translatedVersionID);
}


/**
 * Delete the connection of a origin post to the translated post version with the given ID.
 * 
 * @param string|int $postID A string containing the post ID which is to be deleted from the origin post.
 */
function delete_connection_to_translated_version($postID)
{
    /* get origin post ID */
    $translationFromID = get_origin_version_id($postID);

    /* delete value for translation post is found */
    if ($translationFromID) {
        if (get_translated_version_id($translationFromID)) {
            delete_post_meta($translationFromID, C\Post_Type::FIELD_TRANSLATION_TO);
        }
    }
}


/* processing version number concerning the feature 'Versioning' -----------------------------------------------------*/


/**
 * Get a version number from a string. A version number must match the pattern 0.9, 1, 1.3, 2.50, etc.
 *
 * @param string $versionText A string containing the version text.
 * @param bool $returnOnlyValue Optional boolean indicating if the return value should contain the split $versionText
 * as well. Default is false.
 * @return mixed Return the extracted version number or the split version text.
 */
function get_version_number_from_string($versionText, $returnOnlyValue = false)
{
    preg_match('/(\d+).?(\d*)/', $versionText, $splitVersionText);
    return $returnOnlyValue ? $splitVersionText[0] : $splitVersionText;
}


/**
 * Get the maximum version number of all connected post version of a master post.
 * 
 * @param string|int $masterID A string containing the post ID of the master post.
 * @return false|int|string Return a string containing the maximum version number.
 */
function get_max_version_number($masterID)
{

    /* get all version */
    $currentValue = get_all_version_ids($masterID);

    /* loop through all versions and check version field */
    $existingVersions = [];

    if(!empty($currentValue)){
        foreach ($currentValue as $versionPostID) {

            /* get version text from post */
            $versionText = get_acf_version_field($versionPostID);

            /* strip to #.#*/
            $existingVersions[$versionPostID] = get_version_number_from_string($versionText, true);
        }
    }
    
    /* get all version numbers and prepare for comparison */
    $versionForComparison = [];
    foreach ($existingVersions as $version) {
        $versionText = get_version_number_from_string($version);
        $versionForComparison[$version] = $versionText[1] * 1000 + $versionText[2];
    }

    /* return maximum value of all version numbers */
    return empty($versionForComparison) ? false : array_search(max($versionForComparison), $versionForComparison);

}


/**
 * Increment the version text by incrementing the number after the digit, e.g. increment 1.4 to 1.41 and 1.21 to 1.22.
 * TODO @2.0 Roadmap : consider more than 1000 versions per post and different incrementation.
 * 
 * @param string $versionText A string containing the version text.
 * @return mixed|string Returns a string containing the incremented version number.
 */
function increment_version_number($versionText)
{ 
    $splitVersion = get_version_number_from_string($versionText);

    /* no recognized pattern */
    if (empty($splitVersion)) {
        return '1.0';
    } 
    
    /* max version has pattern #.# */
    elseif (!empty($splitVersion[2])) {
        $newInteger = intval($splitVersion[2]) + 1;
        return $splitVersion[1] . '.' . $newInteger;
    } 
    
    /* max version has pattern # */
    else {
        return $splitVersion[1] . '.1';
    }

}


/**
 * Get the post with the highest version from array of posts.
 *
 * @param array $posts An array containing the posts to be compared.
 * @param false $ids A boolean identifying if array contains post objects or post IDs.
 * @return WP_Post|array|string Returns the post or post ID with the highest version.
 */
function get_post_with_highest_version($posts, $ids = false){

    $temp = [];
    foreach($posts as $post){

        $postID = $ids ? $post : $post->ID;

        /* get version text from post */
        $version = get_acf_version_field($postID);

        /* strip to #.#*/
        $temp[$postID] = get_version_number_from_string($version, true);
    }

    /* get all version numbers and prepare for comparison */
    $versionForComparison = [];
    foreach ($temp as $postID => $version) {
        $versionText = get_version_number_from_string($version);
        $versionForComparison[$postID] = $versionText[1] * 1000 + $versionText[2];
    }

    /* return maximum value of all version numbers */
    return get_post(array_search(max($versionForComparison), $versionForComparison));
}