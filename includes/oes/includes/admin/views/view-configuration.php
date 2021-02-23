<?php

use OES\Config\Option;

/* Check if user has rights to manage options. */
if (!current_user_can('manage_options') && !current_user_can('manage_options_oes')) :
    ?><div class="notice notice-error">
        <p><?php _e('Sorry, you are not allowed to manage options for this site.', 'oes'); ?></p>
    </div><?php
    wp_die();
endif;


/* Display errors after first h2-header. */
settings_errors();


/* Check if configuration source is set as admin panel (editorial layer). */
$source = get_option(Option::POST_TYPE_GENERAL);
$display = ($source['data_source'] == 'panel') ? '' : ' style="display:none"';


/* Get active tab or set to default 'general'. */
$activeTab = oes_isset_GET('tab', 'general');


/* Prepare slug for linking to tabs. */
$slug = 'oes_settings_backend';


?>
<div class="wrap" id="settings-page">
    <h2 class="nav-tab-wrapper" id="configuration">
        <a href="?page=<?php echo $slug;?>&tab=general"
           class="nav-tab <?php echo $activeTab == 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General', 'oes'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=post_types"
           class="nav-tab <?php echo $activeTab == 'post_types' ? 'nav-tab-active' : ''; ?>" <?php echo $display; ?>>
            <?php _e('Post Types', 'oes'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=inheritance"
           class="nav-tab <?php echo $activeTab == 'inheritance' ? 'nav-tab-active' : ''; ?>" <?php echo $display; ?>>
            <?php _e('Inheritance', 'oes'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=taxonomies"
           class="nav-tab <?php echo $activeTab == 'taxonomies' ? 'nav-tab-active' : ''; ?>" <?php echo $display; ?>>
            <?php _e('Taxonomies', 'oes'); ?>
        </a>
    </h2>
    <?php

    if ($activeTab == 'general') oes_get_view('view-configuration-general', [], OES_PATH_TEMP);
    else if ($activeTab == 'post_types') oes_get_view('view-configuration-post_types', [], OES_PATH_TEMP);
    else if ($activeTab == 'inheritance') oes_get_view('view-configuration-inheritance', [], OES_PATH_TEMP);
    else if ($activeTab == 'taxonomies') oes_get_view('view-configuration-taxonomies', [], OES_PATH_TEMP);

    ?>
</div>