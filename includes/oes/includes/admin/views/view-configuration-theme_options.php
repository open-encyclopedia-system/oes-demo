<?php use OES\Config\Option;?>
<h2><?php _e('Configuring Theme (Frontend) Options', 'oes'); ?></h2>
<div><p><?php _e('If you are using the OES Demo Theme or a WordPress Theme based on the OES Demo Theme you can 
configure and administrate theme options that will be visible on your website (frontend).', 'oes'); ?></p>
    <p><?php _e('A post of a custom post type 
that has been registered via the editorial layer or the backend can be displayed inside the frontend as a 
<strong>single page</strong>, as a <strong>search result</strong> or as part of an <strong>archive</strong> listing all 
posts of a specific custom post type. You need to save your configuration on this page for each post type separately.',
            'oes'); ?></p>
    <h4><?php _e('Single Page', 'oes');?></h4>
    <p><?php _e('If displayed as a <strong>single page</strong> the post will 
be divided into two sections: the content and the meta data. The meta data will be displayed as a table at the end of 
the post. You can choose which post field are considered for this table by checking the checkbox 
"<strong>Metadata</strong>" for this 
post type. Empty fields will not be displayed. If you want to display a field with a different label than the label 
used inside the editorial layer, you can use the input box 
"<strong>Label For Frontend</strong>"<br>
You will need to adjust the theme in order to display the content of a 
customized post if the post type is not part of the OES Demo Plugin.', 'oes'); ?></p>
    <h4><?php _e('Archive', 'oes');?></h4>
    <p><?php _e('Post of the same post type are displayed in <strong>archives</strong>, this is a page with a list 
of all posts (of a specific post type). The OES Demo Theme lists the posts with a dropdown for each item. If you click 
the icon (by default this is a plus symbol) on the left to the item you will get a table with some basic information for 
the connected post. You can choose which post field are considered for this table by checking the checkbox 
"<strong>Archive</strong>" for this post type. Empty fields will not be displayed.<br>
By choosing a field for "<strong>Sorting Title (for Lists)</strong>" for a post type you can 
determine which field will be used as title for this posts. Any list will sort the posts by this title alphabetically. 
By checking the checkbox "<strong>Display Archive as List</strong>" the archive view for this post type will be 
displayed as simple list instead of the default accordion view including a dropdown for each post.', 'oes');
    ?></p>
    <h4><?php _e('Search Results', 'oes');?></h4>
    <p><?php _e('By checking the checkbox "<strong>Include in Search</strong>" for a field this field\'s content 
will be included in the search. Currently only text fields can be searched. You can change the sorting order of the 
search results by selecting search columns for "<strong>Sort Search Results By</strong>" and 
"<strong>Secondary Sort Search Results By</strong>". By default the search results will be sorted by name, type and 
then occurrences.', 'oes'); ?></p>
    <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
        <div class="settings-display"><?php
            settings_fields(Option::THEME_SEARCH);
            \OES\Option\oes_do_settings_sections(Option::THEME_SEARCH, 'plain', ['id' => 'oes-presentation-table']);
            ?>
        </div>
        <div class="oes-settings-submit"><?php
            submit_button(); ?>
        </div>
    </form>
    <hr><?php

    /* loop through all post types */
    foreach (OES_Project_Config::POST_TYPE_ALL as $postType) :?>
        <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
            <h1><?php echo get_post_type_object($postType)->label ?></h1>
            <div class="settings-wrapper-post-types">
                <div class="settings-display">
                    <?php
                    settings_fields(Option::THEME_TITLE . '-' . $postType . '-title');
                    \OES\Option\oes_do_settings_sections(Option::THEME_TITLE . '-' . $postType . '-title',
                        'plain', ['id' => 'oes-presentation-table']);
                    ?>
                </div>
                <div class="oes-settings-submit"><?php
                    submit_button(); ?>
                </div>
            </div>
        </form>
        <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
            <div class="settings-display"><?php
                settings_fields(Option::THEME . '-' . $postType);
                \OES\Option\oes_do_settings_sections(Option::THEME. '-' . $postType,
                    'table',
                    ['class' => 'wp-list-table widefat fixed striped table-view-list taxonomies']);
                ?>
            </div><?php
            submit_button(); ?>
        </form>
        <hr><?php
    endforeach;
    ?>
</div>