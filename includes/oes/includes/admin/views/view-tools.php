<?php

/* Check if user has rights to manage options. */
if (!current_user_can('manage_options') && !current_user_can('manage_options_oes')) :
    ?>
    <div class="notice notice-error">
    <p><?php _e('Sorry, you are not allowed to manage options for this site.', 'oes'); ?></p>
    </div><?php
    wp_die();
endif;


/* Display errors after first h2-header. */
settings_errors();

/* get tool messages */
do_action('oes_admin_notices');

?>
    <div class="wrap" id="settings-page">
        <!-- dummy for admin notices -->
        <h2 class="dummy-admin-notices"></h2>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('<strong>Please note</strong> that as for now the .csv files are 
            <strong>utf-8</strong> encoded!', 'oes'); ?></p>
        </div>
        <div>
            <h2><?php _e('Import', 'oes'); ?></h2>
            <p><?php _e('Select a .csv file with post data you want to import to your database. The file must 
            follow a required format concerning field names or matching classes have to be implemented. A full 
            documentation will be following soon. You can 
            download an import template for a post type below. You can import any post parameter with an import file 
            but we recommend to only import the parameter as provided in the import template. A full 
            documentation for post type parameters can be found here: ', 'oes'); ?>
                <a href="https://developer.wordpress.org/reference/classes/wp_post/">
                    https://developer.wordpress.org/reference/classes/wp_post/</a>.</p>
            <p><?php _e('The valid operations are: "insert" to add a new post, "update" to edit an existing post 
            and "delete" to remove an existing post. For "update" and "delete" operation you need a valid post id. If 
            you check the checkbox "<strong>Force Delete</strong>" the posts will be deleted permanently instead of 
            being moved to trash.', 'oes'); ?></p>
            <p><?php _e('The time limit for import operations has been set to 10 minutes.', 'oes'); ?></p>
            <?php OES()->adminTools->display_tool('import'); ?>
        </div>
        <hr>
        <div>
            <h2><?php _e('Export', 'oes'); ?></h2>
            <p><?php _e('Select the post type you would like to export and then select an output format. Use the 
            download button to export the generated file. You can use the exported files to import your data to another 
            OES installation with the same post types. If you check the checkbox "<strong>Generate Template</strong>" 
            you can generate a template file for the selected post type.', 'oes'); ?></p>
            <?php OES()->adminTools->display_tool('export'); ?>
        </div>
        <hr>
        <div>
            <h2><?php _e('Delete', 'oes'); ?></h2>
            <p><?php _e('Select the post type or taxonomy that you want to delete. All posts or terms of the 
            chosen post type or taxonomy will be deleted. If you check the checkbox "<strong>Force Delete</strong>" the 
            posts or pages will be deleted permanently instead of being moved to trash.', 'oes'); ?></p>
            <?php OES()->adminTools->display_tool('delete-posts'); ?>
        </div>
    </div>