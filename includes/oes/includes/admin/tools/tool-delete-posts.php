<?php

namespace OES\Admin\Tools;


use function OES\Admin\add_oes_notice_after_refresh;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Delete_Posts')) :

    /**
     * Class Delete_Posts
     *
     * Export options to json file.
     */
    class Delete_Posts extends Tools
    {

        /**
         * Initialize class parameters
         */
        function initialize_parameters()
        {
            $this->name = 'delete-posts';
            $this->formAction = admin_url('admin-post.php');
            $this->formParameters = ' enctype="multipart/form-data"';
        }


        /**
         * Display the tools parameters for form.
         */
        function html()
        {

            /* get all post types */
            $choices = [];
            $postTypes = get_post_types(['public' => true], 'objects');
            if ($postTypes) {
                foreach ($postTypes as $postType) {
                    if (!in_array($postType->name, ['post', 'attachment'])) $choices[$postType->name] = $postType->label;
                }
            }

            /* get all taxonomy */
            $taxonomies = get_taxonomies(['public' => true], 'objects');
            if ($taxonomies) {
                foreach ($taxonomies as $taxonomy) {
                    if (!in_array($taxonomy->name, ['post_tag', 'category', 'post_format']))
                        $choices[$taxonomy->name] = $taxonomy->label . ' (Taxonomy)';
                }
            }
            ?>
            <div id="tools">
                <div>
                    <p><strong><?php _e('Select Post Type or Taxonomy', 'oes'); ?></strong></p>
                    <?php
                    /* display radio boxes to select from all custom post types */
                    foreach ($choices as $postTypeName => $postTypeLabel) :?>
                        <input type="radio" id="delete_<?php echo $postTypeName; ?>" name="post_types_delete[]"
                               value="delete_<?php echo $postTypeName; ?>">
                        <label for="delete_<?php echo $postTypeName; ?>"><?php echo $postTypeLabel; ?></label><br>
                    <?php endforeach; ?>
                </div>
                <div id="tools-checkbox">
                    <p><strong><?php _e('Force Delete', 'oes'); ?></strong></p>
                    <input type="checkbox" id="force_delete_post" name="force_delete_post">
                    <label for="force_delete_post"><?php
                        _e('If deleting a post or page, force delete it permanently (instead of moving the post 
                        or page to trash).',
                            'oes'); ?></label>
                </div>
            </div>
            <?php submit_button(__('Delete Posts/Tags', 'oes')); ?>
            <?php
        }


        /**
         * Runs when admin post request for the given action.
         */
        function admin_post_tool_action()
        {

            /* get post type array from form (post type has pattern 'delete_'[post_type])*/
            $postType = substr($_POST['post_types_delete'][0], 7);

            /* skip if no post type selected */
            if (!$postType) {
                add_oes_notice_after_refresh(__('No post types selected.', 'oes'), 'error');
                return false;
            }

            /* check if post type */
            $count = 0;
            $countErrors = 0;
            $isTerm = false;
            if (post_type_exists($postType)) {

                /* get all posts */
                $posts = get_posts([
                    'post_type' => [$postType],
                    'post_status' => get_post_types('', 'names'),
                    'numberposts' => -1
                ]);

                /* delete all posts */
                foreach ($posts as $post) {
                    $success = $_POST['force_delete_post'] ? wp_delete_post($post->ID) : wp_trash_post($post->ID);
                    if ($success) $count++;
                    else $countErrors++;
                }
            } /* check if taxonomy */
            elseif (taxonomy_exists($postType)) {

                $isTerm = true;

                /* get all tags */
                $terms = get_terms([
                    'taxonomy' => $postType,
                    'hide_empty' => false
                ]);

                /* delete all posts */
                foreach ($terms as $term) {
                    $success = wp_delete_term($term->term_id, $postType);
                    if (!is_wp_error($success) && $success) $count++;
                    else $countErrors++;
                }
            }

            if ($count) add_oes_notice_after_refresh(
                sprintf(__('%1s %2s deleted.%3s', 'oes'),
                    $count,
                    $isTerm ? 'terms' : 'posts',
                    !empty($countErrors) ? $countErrors . __(' error(s) occurred.', 'oes') : ''
                ),
                'info');
            else add_oes_notice_after_refresh(__('No posts or terms deleted.', 'oes'), 'info');

            return true;
        }

    }

    // initialize
    oes_register_admin_tool('\OES\Admin\Tools\Delete_Posts');

endif;