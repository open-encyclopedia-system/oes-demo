<?php use OES\Config\Option;?>
<div>
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php _e('Frontpage Settings', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p><?php _e('You can choose which article should be set as "<strong>Featured Article</strong>" on your 
front page. By selecting "<strong>Latest Article</strong>" the article last published will be set as featured post. You 
can set the label for the featured article in "<strong>Label</strong>". The 
default label is "Featured Article".', 'oes'); ?></p>
        </div>
    </div>
    <div class="oes-settings-form-wrapper">
        <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
            <div class="settings-display"><?php
                settings_fields(Option::FRONTPAGE);
                \OES\Option\oes_do_settings_sections(Option::FRONTPAGE,
                    'plain', ['id' => 'oes-presentation-table']);
                ?>
            </div>
            <div class="oes-settings-submit"><?php
                submit_button(); ?>
            </div>
        </form>
    </div>
</div>
