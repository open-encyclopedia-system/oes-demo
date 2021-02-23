<?php


use OES\Config\Option;


/* Check if at least one custom taxonomies exists. */
$allTaxonomies = get_taxonomies(['_builtin' => false], 'objects');

/* Hide settings and display info if no custom taxonomy exist.*/
$display = '';
if (empty($allTaxonomies)) :
    $display = ' style="display:none"';
    ?><div class="notice notice-info">
    <p><?php _e('There are no custom taxonomies registered.', 'oes'); ?></p>
    </div><?php
endif;

?>
<div class="configuration-post-type-relations">
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php _e('Register Taxonomies for Post Types', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p><?php _e('A taxonomy within WordPress is a way of grouping posts together. By checking the checkbox 
            for a post type and a taxonomy, the taxonomy will be available for 
            this specific post type. You can change the taxonomy label by 
            filling the text box. For more information about taxonomies check the WordPress Manuals.', 'oes'); ?></p>

        </div>
    </div>
    <div class="oes-settings-form-wrapper">
        <form method="POST" class="settings-post-types-x-taxonomies" action="options.php" <?php echo $display; ?>>
            <div class="settings-display">
                <?php
                settings_fields(Option::POST_TYPE_X_TAXONOMY);
                \OES\Option\oes_do_settings_sections(Option::POST_TYPE_X_TAXONOMY,
                    'table', ['class' => 'wp-list-table widefat fixed striped table-view-list taxonomies']);
                ?>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
</div>