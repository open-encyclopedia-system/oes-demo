<?php use OES\Config\Option;?>
<div class="oes-settings-form-wrapper">
    <h2 id="oes-settings"><?php
        _e('Configuring OES', 'oes'); ?></h2>
    <p>
        <?php _e('OES provides functionalities for custom WordPress post types. An initial configuration of custom 
        post types is part of this OES Demo Plugin. 
        OES post types can be configured in the backend or the editorial layer (WordPress administration GUI, /wp-admin). 
        A configuration in the backend is to be preferred, 
        but in some circumstances you may prefer a configuration via the editorial layer, for instance  
        if you want to design, test or export configurations without editing the source code itself. 
        You do not even need any programming skills to do this. You might, however, need some understanding of 
        WordPress and post types or do it the old trial-and-error way.', 'oes'); ?>
    </p>
    <p>
        <?php _e('You can change OES settings by clicking check boxes or by filling the input fields on the OES 
        settings pages. Make sure to click the save button after doing so. Upon saving, the configurations get stored as 
        WordPress settings. 
        This means that your configuration will remain as long as the plugin is active and the database online 
        (which stresses the point that a configuration in the backend is to be preferred). ', 'oes'); ?>
    </p>
    <p>
        <?php _e('None of the configurations will have any impact on the data itself! This is WordPress logic and 
        OES bows to their fundamental conception of editors\' needs regarding data consistency. This means that if you 
        add additional data to your post type (e.g. a new field, a taxonomy, a relation to another post...), this data 
        will be stored in the database until you actively remove it by e.g. deleting the field value, deleting the 
        relation or deleting the post itself. Let us consider the following example:', 'oes'); ?>
    </p>
    <p>
        <?php _e('Your project uses the OES post type "article". You added some articles but now you want to remove 
        the field "DOI" from the post type "article". Hence, you delete the field "DOI" from the configuration. 
        As a result, you cannot see the "DOI" value in the editorial layer although it still exists for all articles 
        in the database.', 'oes'); ?>
    </p>
    <p>
        <?php _e('We are constantly working on the OES Demo Plugin to enable you to experience all OES features 
        without any programming on your part. Check out our release plan on github to get information about the next 
        features (or contact us if you need help implementing it yourself).', 'oes'); ?>
    </p>
    <h2><?php _e('Select Configuration Source', 'oes'); ?></h2>
    <div>
        <p><?php
            _e('The OES plugin can be configured via the config files inside the OES project plugin or via this 
            editorial layer, see detailed information above. You can change the configuration source at any time.',
                'oes'); ?>
        </p>
        <p><?php
            _e('By selecting a configuration source you determine which configuration will override other 
            configurations. Note that the configurations only affect the display of post types inside the editorial 
            layer and the frontend but not the database itself. 
            By default, the configurations are set by the backend.', 'oes'); ?>
        </p>
    </div>
    <form method="POST" class="post-types-data-source" action="options.php">
        <?php
        settings_fields(Option::POST_TYPE_GENERAL);
        do_settings_sections(Option::POST_TYPE_GENERAL);
        submit_button();
        ?>
    </form>
    <div<?php global $display;
    echo $display; ?>>
        <p><?php
            _e('We recommend storing your configurations made via this editorial layer with the 
        "Export Default Options" button to your backend. Clicking the button will automatically create a file with your 
        customized configurations. You can restore these saved configuration with the "Import Default Options" button.',
                'oes'); ?>
        </p>
        <p style="font-style: italic"><?php
            printf(__('The option were last saved on %1s GMT.', 'oes'),
                date('d M Y H:i:s', filemtime(WP_CONTENT_DIR . '/uploads/option-defaults.json'))); ?>
        </p>
        <div id="configuration-tools">
            <?php
            /* display tools */
            OES()->adminTools->display_tool('import-default-options');
            OES()->adminTools->display_tool('export-default-options');
            ?>
        </div>
    </div>
</div>