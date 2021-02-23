<?php use OES\Config\Option; ?>
<div>
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php
                    _e('Footnotes Settings', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p>
                <?php _e('You can add footnotes to any content composed in a WYSIWYG editor. Some text fields are 
displayed with a content editor, such as the field <strong>Article Content</strong> for the post type article. To add 
a footnote to your text, add the shortcode below to the text in the editor.', 'oes'); ?>
            </p>
            <p><strong><?php _e('Shortcode:', 'oes'); ?></strong>
                <span style="font-style: italic"><?php _e('[oes_note]Insert your footnote here.[/oes_note]', 'oes'); ?>
    </span>
            </p>
            <p>
                <?php _e('Footnotes will be displayed below the post\'s content. If you want to add to the footnote 
    section you can use the input box "<strong>Label (Section)</strong>". If you check the checkbox 
    "<strong>Hide Section</strong>", footnotes won\'t be displayed for any post. If you check the checkbox 
    "<strong>Exclude from Table of Content</strong>", footnotes will not be listed in the table of content for any 
    post.',
                    'oes'); ?>
            </p>
        </div>
    </div>
    <div class="oes-settings-form-wrapper">
        <form method="POST" class="footnotes" action="options.php">
            <?php wp_nonce_field('oes_footnotes_settings_nonce'); ?>
            <div>
                <?php
                settings_fields(Option::FOOTNOTES);
                \OES\Option\oes_do_settings_sections(Option::FOOTNOTES,
                    'plain', ['id' => 'oes-presentation-table']);
                ?>
                <div class="buttons">
                    <?php submit_button(); ?>
                </div>
            </div>
        </form>
        <p><strong><?php _e('Shortcode:', 'oes'); ?></strong>
            <span style="font-style: italic"><?php _e('[oes_note]Insert your footnote here.[/oes_note]', 'oes'); ?>
    </span>
        </p>
    </div>
</div>
