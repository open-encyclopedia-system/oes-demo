<?php


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Restrict_User')) :

    /**
     * Class Restrict_User
     *
     * A class to construct a role for a demo user with read only access.
     */
    class Restrict_User
    {

        /** @var string A String containing the role name. */
        var $demoRole = 'oes_demo';


        /**
         * Restrict_User constructor.
         */
        function __construct()
        {
            /* add new role */
            add_action('init', [$this, 'add_demo_role']);

            /* check if user has demo role */
            global $current_user;
            if (is_admin() && in_array($this->demoRole, $current_user->roles)) {

                /* prevent submit on init */
                add_action('admin_init', [$this, 'prevent_submit']);

                /* prevent self administration */
                add_action('wp_before_admin_bar_render', [$this, 'prevent_self_administration']);
                add_action('admin_init', [$this, 'prevent_self_administration_profile']);

                /* prevent access to wp admin pages */
                add_action('admin_head', [$this, 'prevent_url_access']);

                /* remove add new links for all OES post types */
                add_action('admin_head', [$this, 'remove_add_new_link']);

                /* overwrite css */
                add_action('admin_menu', [$this, 'overwrite_visibility_via_css']);

                /* remove access to post-new.php */
                add_action('admin_menu', [$this, 'remove_access_to_post_new']);

                /* remove "Add new" menu */
                add_action('admin_menu', [$this, 'remove_add_new_menu']);

                /* remove row actions in list view */
                add_filter('post_row_actions', [$this, 'remove_row_actions'], 10, 2);

                /* remove the trash link from list view for all OES post types */
                foreach (oes_get_post_types() as $postType => $postTypeConfiguration) {
                    add_filter('views_edit-' . $postType, [$this, 'remove_trash_link']);
                }

                /* remove the publish meta box for all OES post types */
                add_action('do_meta_boxes', [$this, 'remove_publish_meta_box']);
            }
        }


        /**
         * Add role for OES Demo public user with only read-only access.
         *
         * TODO @2.0 Roadmap : make role constructing available and configurable.
         */
        function add_demo_role()
        {
            $capabilities = [
                'read' => true,
                'read_post' => true,
                'edit_post' => true,
                'edit_posts' => true,
                'edit_others_posts' => true,
                'edit_private_posts' => true,
                'edit_published_posts' => true,
                'read_private_posts' => true,
                'edit_pages' => true,
                'edit_others_pages' => true,
                'edit_private_pages' => true,
                'edit_published_pages' => true,
                'read_private_pages' => true,
                'manage_options_oes' => true
            ];

            /* TODO @2.0 Roadmap : check if role already exists, if so use add_cap */
            add_role(
                $this->demoRole,
                __('Read-only (OES Demo)', 'oes-demo'),
                $capabilities
            );
        }


        /**
         * Prevent submit.
         *
         * TODO @2.0 Roadmap : further testing, is this save enough.
         */
        function prevent_submit() {

            global $current_user;
            if (!empty($_POST)
                && in_array($_POST['post_type'], array_keys(oes_get_post_types()))
                && in_array($this->demoRole, $current_user->roles)) {

                if (true === DOING_AJAX) exit;

                if (!empty($_POST['post_ID'])) {
                    wp_safe_redirect(admin_url());
                    exit;
                } else {
                    wp_safe_redirect(admin_url());
                    exit;
                }
            }
        }


        /**
         * Prevent self administration.
         */
        function prevent_self_administration() {

            global $current_user, $wp_admin_bar;
            if (in_array($this->demoRole, $current_user->roles)) {
                $wp_admin_bar->remove_menu('edit-profile', 'user-actions', 'user');
            }
        }


        /**
         * Prevent self administration.
         */
        function prevent_self_administration_profile() {

            global $current_user;
            if (in_array($this->demoRole, $current_user->roles)){
                if(IS_PROFILE_PAGE === true) {
                    wp_die( 'You are not allowed to change profile data.' );
                }
                remove_menu_page( 'profile.php' );
                remove_submenu_page( 'users.php', 'profile.php' );
            }
        }


        /**
         * Prevent an url access to restricted pages.
         */
        function prevent_url_access()
        {
            global $current_screen, $current_user;

            /* if user does not have demo role, exit early */
            if (!in_array($this->demoRole, $current_user->roles)) return;

            /* redirect to dashboard for this pages */
            $doNotAccessPages = ['edit-acf', 'edit-comments', 'upload', 'tools', 'plugins', 'themes', 'options-general',
                'options-writing', 'options-reading', 'options-discussion', 'options-media', 'options-permalink',
                'options-privacy', 'post-new.php?post_type=oes_demo_article', 'profile'];
            if (in_array($current_screen->id, $doNotAccessPages)) {
                wp_redirect(admin_url());
                exit;
            }
        }


        /**
         * Remove the 'Add new' link inside the button for all OES post types.
         *
         * @return boolean
         */
        function remove_add_new_link() {

            global $post_new_file,$post_type_object, $current_user, $current_screen;

            /* exist early if page is not an OES post type page */
            if (!isset($post_type_object) ||
                !in_array($post_type_object->name, array_keys(oes_get_post_types()))) {
                return false;
            }

            if (in_array($this->demoRole, $current_user->roles)) {
                //$post_type_object->labels->add_new = 'Return to Index';
                $post_new_file = 'edit.php?post_type=' . $current_screen->post_type;
            }

            return true;
        }


        /**
         * Overwrite css to hide actions.
         *
         * TODO @2.0 Roadmap : unhide tags postbox with
         * <script type="text/javascript">
         * document.getElementById("tagsdiv-oes_demo_tag_topic").removeClass('hide-if-js');
         * </script>
         */
        function overwrite_visibility_via_css()
        {
            /* check if demo user */
            global $current_user;
            if (!in_array($this->demoRole, $current_user->roles)) return;

            /* hide sidebar link */
            global $submenu;
            global $oes;

            foreach ($oes->postTypes as $postType => $postTypeConfiguration) {
                unset($submenu['edit.php?post_type=' . $postType][10]);
            }

            /* overwrite css */
            ?>
            <style type="text/css">/* Overwrite css for OES Demo User */
                #wp-admin-bar-new-content, /* hide '+ New' from header */
                #wp-admin-bar-comments, /* hide comments icon from header */
                #menu-pages ul, /* hide 'Add new page' */
                .row-actions .delete a, /* hide delete link from tags editor */
                #menu-posts, /* hide menu "Posts" */
                #menu-comments, /* hide menu "Comments" */
                #menu-tools, /* hide menu "Tools" */
                #menu-users
                {
                    display: none !important;
                }

                .wrap .wp-heading-inline + .page-title-action, /* overwrite 'Add ...' button */
                #translation a, /* overwrite 'Create Translation' */
                #master-version-information .left a, /* overwrite 'Create New Version' */
                .edit-tag-actions a, /* overwrite 'Delete' link for tags editor */
                #edit-slug-buttons button[type="button"], /* overwrite 'Edit' button in slug editor */
                input[type="submit"],
                input[type="file"] {
                    pointer-events: none !important;
                    color: #a0a5aa !important;
                    background: #f7f7f7 !important;
                    border-color: #ddd !important;
                    box-shadow: none !important;
                    text-shadow: none !important;
                }</style><?php
        }


        /**
         * Remove the access to new post actions and redirect to list view.
         */
        function remove_access_to_post_new() {

            global $_REQUEST, $pagenow, $current_user;
            if (!empty($_REQUEST['post_type'])
                && in_array($_REQUEST['post_type'], array_keys(oes_get_post_types()))
                && !empty($pagenow)
                && 'post-new.php' == $pagenow
                && in_array($this->demoRole, $current_user->roles))
            {
                wp_safe_redirect(admin_url('edit.php?post_type=' . $_REQUEST['post_type']));
            }
        }


        /**
         * Remove add new posts form menu.
         */
        function remove_add_new_menu() {

            global $current_user;
            if (in_array($this->demoRole, $current_user->roles)) {

                /* remove submenu for pages and posts  */
                remove_submenu_page('edit.php?post_type=page',
                    'post-new.php?post_type=page');

                /* remove submenu for each post type */
                 foreach(oes_get_post_types() as $postType => $postTypeConfiguration){
                    remove_submenu_page('edit.php?post_type=' . $postType,
                        'post-new.php?post_type=' . $postType);
                }
            }
        }


        /**
         * Remove row actions like trash and quick edit for list views.
         *
         * @param $actions
         * @param $post
         * @return array
         */
        function remove_row_actions($actions, $post)
        {
            global $current_user;

            /* Remove Quick Edit and Delete */
            if (in_array($this->demoRole, $current_user->roles))
                return ['edit' => $actions['edit'], 'view' => $actions['view']];

            return $actions;
        }


        /**
         * Remove trash link for list views.
         *
         * @param $views
         * @return mixed
         */
        function remove_trash_link($views)
        {
            global $current_user;
            if (in_array($this->demoRole, $current_user->roles))
                unset($views['trash']);

            return $views;
        }


        /**
         * Remove the publish meta box for OES post types.
         */
        function remove_publish_meta_box()
        {
            global $current_user, $current_screen;

            if (in_array($this->demoRole, $current_user->roles) &&
                in_array($current_screen->id, array_keys(oes_get_post_types()))){
                remove_meta_box('submitdiv', $current_screen->id, 'side');
            }
        }
    }

    new Restrict_User();

endif;