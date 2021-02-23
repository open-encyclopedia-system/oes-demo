<?php  use OES\Config\Option;?>
<h2><?php _e('Footnote Settings', 'oes'); ?></h2>
<p>
    <?php _e('You can add footnotes inside wysiwyg fields (these are text field that are displayed with a content
     editor, e.g. the field <strong>Article Content</strong> for the post type article). To add a footnote add the 
     shortcode below to your text.', 'oes'); ?>
</p>
<p><strong><?php _e('Shortcode:', 'oes');?></strong>
    <span style="font-style: italic"><?php _e('[oes_note]Insert your footnote here.[/oes_note]', 'oes'); ?>
    </span>
</p>
<p>
    <?php _e('The footnotes will be displayed after the post content. If you want to add to the footnote section 
    you can use the input box "<strong>Label (Section)</strong>". If you check the checkbox 
    "<strong>Hide Section</strong>" the footnote section will not be displayed for any post. If you check the checkbox 
    "<strong>Exclude from Table of Content</strong>" the footnote section will not be listed in the table of content.',
        'oes'); ?>
</p>
<div<?php global $display;
echo $display; ?>>
    <form method="POST" class="footnotes" action="options.php">
        <?php wp_nonce_field('oes_footnotes_settings_nonce'); ?>
        <div>
            <?php
            settings_fields(Option::FOOTNOTES);
            do_settings_sections(Option::FOOTNOTES);
            ?>
            <div class="buttons">
                <?php submit_button(); ?>
            </div>
        </div>
    </form>
</div>
