<?php use OES\Config\Option;?>
<div>
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php _e('Image Settings', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p><?php _e('You can set the image information that will be displayed in the frontend. If you check 
the option "<strong>Show in Subtitle</strong>" the field value will be displayed as part of the subtitle below the image 
(if the field is not empty). By placing text in the "<strong>Prefix</strong>" option the field value will be precedented
 by this text.', 'oes');?></p>
            <p><?php _e('The option "<strong>Show in Panel</strong>" will display the field value available in the
 expanded (panel) view. You can modify the label for the table representation by placing a new label in the 
 "<strong>New Label</strong>" option.', 'oes'); ?></p>
        </div>
    </div>
    <div class="oes-settings-form-wrapper">
        <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
            <div class="settings-display"><?php
                settings_fields(Option::IMAGE_CREDIT);
                \OES\Option\oes_do_settings_sections(Option::IMAGE_CREDIT,
                    'plain', ['id' => 'oes-presentation-table']);
                ?>
            </div>
            <div class="oes-settings-submit"><?php
                submit_button(); ?>
            </div>
        </form>
        <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
            <div class="settings-display"><?php
                settings_fields(Option::IMAGE);
                \OES\Option\oes_do_settings_sections(Option::IMAGE, 'table',
                    ['class' => 'wp-list-table widefat fixed striped table-view-list taxonomies']);
                ?>
            </div>
            <div class="oes-settings-submit"><?php
                submit_button(); ?>
            </div>
        </form>
    </div>
</div>
