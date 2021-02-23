<?php

use OES\Config\Option;

/* Hide settings and display info if no custom oes post types exist.*/
$display = '';
if (!oes_get_post_types()) :
    $display = ' style="display:none"';
    ?>
    <div class="notice notice-info">
    <p><?php _e('There are no custom post types registered.', 'oes'); ?></p>
    </div><?php
endif;

?>
<div class="configuration-post-type-relations">
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php _e('Post Type Relationships (Inheritance)', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p><?php
                _e('Post types can be connected by relations. Relationships between two post types are initially
            unidirectional, but can be made bidirectional by means of the inheritance settings provided below. 
            A checked box in the column "<strong>Inherits to Field</strong>" means that if the relationship field in 
            the column "<strong>Field</strong>" is updated, then the relationship field in the right column
            will be updated as well. This applies only to current operations.', 'oes'); ?>
            </p>
        </div>
    </div>
    <div class="oes-settings-form-wrapper">
        <form method="POST" class="post-types" action="options.php"<?php echo $display; ?>>
            <div class="post-types-table"><?php
                settings_fields(Option::POST_TYPE_RELATIONSHIP);
                \OES\Option\oes_do_settings_sections(Option::POST_TYPE_RELATIONSHIP, 'plain-alt',
                    ['id' => 'option-relationship',
                        'class' => 'wp-list-table widefat fixed striped table-view-list taxonomies',
                        'header-left' => 'Field',
                        'header-right' => 'Inherits to Field'
                    ]);
                ?>
                <div class="buttons">
                    <?php submit_button(); ?>
                </div>
            </div>
        </form>
    </div>
</div>