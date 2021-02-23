<?php

/* Get active tab or set to default 'general'. */
$activeTab = oes_isset_GET('post_type', 'general');

/* Prepare slug for linking to tabs. */
$slug = 'oes_settings_project_demo&tab=post_types';

?>
<div>
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php _e('Data Display Settings', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p><?php _e('A post of a custom post type can be displayed on the frontend as a 
<strong>single page</strong>, as a <strong>search result</strong> or as part of an <strong>archive</strong> listing all 
posts of a specific custom post type. For instance, the post type “article” can be displayed in full on one page, can 
be referred to as the result of a search, or can be listed in an overview of all articles of a reference work. For each 
of the three display modes, you can decide which fields of a post type should be included in the display. 
You need to save your configuration on this page for each post type separately.',
                    'oes'); ?></p>
            <h4><?php _e('Single Page', 'oes'); ?></h4>
            <p><?php _e('If displayed as a <strong>single page</strong>, a post will 
be divided into two main sections: the content and the metadata. The metadata will be displayed as a table at the end of 
a post. You can choose the post fields to be included in the table by checking the checkbox 
"<strong>Metadata</strong>" for this 
post type. Empty fields will not be displayed. Furthermore, you can adapt the way fields are labelled in the frontend by 
adding text to the input box "<strong>Label For Frontend</strong>". This overwrites the labels used in the editorial 
layer. For post 
types that are not part of the OES Demo Plugin, you will need to adjust the theme in order to display the content 
of a customized post.', 'oes'); ?></p>
            <h4><?php _e('Archive', 'oes'); ?></h4>
            <p><?php _e('Post of the same post type are displayed in <strong>archives</strong>. An archive refers 
to  a page that lists all posts (of a specific post type), for instance, a list of all articles or all persons. 
The OES Demo Theme lists the posts with a dropdown for each item. If you click 
the icon (by default this is a plus symbol) on the left of the item, a table with some basic information for 
the connected post will be shown. You can choose the post fields to be included in this table by checking the checkbox 
"<strong>Archive</strong>" for this post type. Empty fields will not be displayed.<br>
By choosing an option for "<strong>Sorting Title (for Lists)</strong>" for a post type, you can 
determine the field that will be used as title for this posts. Any list will sort the posts by this title alphabetically. 
By checking the checkbox "<strong>Display Archive as List</strong>" the archive view for this post type will be 
displayed as simple list instead of the default accordion view including a dropdown for each post.', 'oes');
                ?></p>
            <h4><?php _e('Search Results', 'oes'); ?></h4>
            <p><?php _e('By checking the checkbox "<strong>Include in Search</strong>" for a field this field\'s 
content will be included in the search. Currently only text fields are searchable.', 'oes'); ?></p>
        </div>
    </div>
    <div id="poststuff">
        <?php do_meta_boxes('oes-theme-post-type', 'normal', ''); ?>
    </div>
</div>
<script type="text/javascript">
    /* close and expand postboxes */
    jQuery(document).ready(function ($) {
        $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        postboxes.add_postbox_toggles('oes-settings_page_oes_frontend');
    });

    /* close postbox by default */
    jQuery(function(){
        jQuery('.postbox').addClass('closed');
    });
</script>

