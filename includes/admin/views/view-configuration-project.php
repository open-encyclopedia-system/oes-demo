<?php


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
//TODO @2.0 Roadmap : display or hide depending on data source.


/* Get active tab or set to default 'general'. */
$activeTab = oes_isset_GET('tab', 'general');


/* Prepare slug for linking to tabs. */
$slug = 'oes_settings_frontend';


?>
<div class="wrap" id="settings-page">
    <h2 class="nav-tab-wrapper" id="configuration">
        <a href="?page=<?php echo $slug;?>&tab=general"
           class="nav-tab <?php echo $activeTab == 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General', 'oes-demo'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=search"
           class="nav-tab <?php echo $activeTab == 'search' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Search', 'oes-demo'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=frontpage"
           class="nav-tab <?php echo $activeTab == 'frontpage' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Frontpage', 'oes-demo'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=images"
           class="nav-tab <?php echo $activeTab == 'images' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Images', 'oes-demo'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=footnotes"
           class="nav-tab <?php echo $activeTab == 'footnotes' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Footnotes', 'oes-demo'); ?>
        </a>
        <a href="?page=<?php echo $slug;?>&tab=data"
           class="nav-tab <?php echo $activeTab == 'data' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Data', 'oes-demo'); ?>
        </a>
    </h2>
    <?php

    if ($activeTab == 'general') oes_get_view('view-configuration-project-general', [], OES_DEMO_PATH);
    else if ($activeTab == 'search') oes_get_view('view-configuration-project-search', [], OES_DEMO_PATH);
    else if ($activeTab == 'frontpage') oes_get_view('view-configuration-project-frontpage', [], OES_DEMO_PATH);
    else if ($activeTab == 'images') oes_get_view('view-configuration-project-images', [], OES_DEMO_PATH);
    else if ($activeTab == 'footnotes') oes_get_view('view-configuration-project-footnotes', [], OES_DEMO_PATH);
    else if ($activeTab == 'data') oes_get_view('view-configuration-project-data', [], OES_DEMO_PATH);

    ?>
</div>