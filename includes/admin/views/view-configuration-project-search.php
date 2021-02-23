<?php use OES\Config\Option;?>
<div>
    <div class="oes-accordion-body" id="info">
        <div class="oes-accordion-item-wrapper">
            <span><h2 id="oes-settings"><?php _e('Search Results Settings', 'oes'); ?></h2></span>
            <span class="oes-accordion" id="info">
            </span>
        </div>
        <div class="oes-accordion-panel">
            <p><?php _e('You can change the sorting order of the search results by choosing from the options for 
"<strong>Sort Search Results By</strong>" and "<strong>Secondary Sort Search Results By</strong>". By default the 
search results will be sorted by name, type and then occurrences. If you want to customize the fields that should be 
considered while searching, switch to the "<strong>Data</strong>" tab.', 'oes'); ?></p>
        </div>
    </div>
    <div class="oes-settings-form-wrapper">
        <form method="POST" class="settings-post-types-x-taxonomies" action="options.php">
            <div class="settings-display"><?php
                settings_fields(Option::THEME_SEARCH);
                \OES\Option\oes_do_settings_sections(Option::THEME_SEARCH,
                    'plain', ['id' => 'oes-presentation-table']);
                ?>
            </div>
            <div class="oes-settings-submit"><?php
                submit_button(); ?>
            </div>
        </form>
    </div>
</div>
