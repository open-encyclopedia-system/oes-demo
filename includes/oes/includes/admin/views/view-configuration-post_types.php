<?php


use OES\Config\Option;


/* Hide settings and display info if no custom oes post types exist.*/
$display = '';
if (!oes_get_post_types()) :
    $display = ' style="display:none"';
    ?><div class="notice notice-info">
        <p><?php _e('There are no custom post types registered.', 'oes'); ?></p>
    </div><?php
endif;

?>
<div class="configuration-post-type-relations">
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php _e('Post Type Configuration', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p><?php
                _e('The configurations merely affect the display of post types inside the editorial layer and the 
                frontend, but not the database itself. For more information see <strong>Configuring OES</strong> inside 
                the General tab.'); ?></p>
            <table id="legend">
                <tr>
                    <th><?php _e('New Label', 'oes'); ?></th>
                    <td><?php _e('Defines a new label for the post type. [Singular], [Plural]. The new label will be 
                    used inside the editorial layer, e.g. in the navigation menu on the left.', 'oes'); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Show in Menu', 'oes'); ?></th>
                    <td><?php _e('Defines if the post type is shown in the editorial layer, e.g. in the navigation 
                    menu on the left. If checked, the post type is shown in its own top level menu. This is a 
                    requirement for editing the post type inside the editorial layer.', 'oes'); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Show in Nav Menu', 'oes'); ?></th>
                    <td><?php _e('Defines if the post type is shown in the navigation menus. If checked, this post 
                    type is available for selection in navigation menus in your frontend.', 'oes'); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Hierarchical', 'oes'); ?></th>
                    <td><?php _e('Defines if post type is hierarchical. If checked, this post type is hierarchical 
                    and a post can have parent or child posts and thus create a hierarchy.', 'oes'); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Has Archive', 'oes'); ?></th>
                    <td><?php _e('Defines if post type has an archive. If checked, an archive 
                    page for this post type is part of your frontend. For WordPress themes an archive page is referring 
                    to a collection of posts grouped by their post type, usually displayed as a list of posts.',
                            'oes'); ?></td>
                </tr>
            </table>

        </div>
    </div>
    <div class="oes-settings-form-wrapper">
        <form method="POST" class="post-types" action="options.php"<?php echo $display; ?>>
            <div class="post-types-table">
                <?php
                settings_fields(Option::POST_TYPE);
                \OES\Option\oes_do_settings_sections(Option::POST_TYPE,
                    'table',
                    ['class' => 'wp-list-table widefat fixed striped table-view-list taxonomies']);
                ?>
                <div class="buttons">
                    <?php submit_button(); ?>
                </div>
            </div>
        </form>
    </div>
</div>