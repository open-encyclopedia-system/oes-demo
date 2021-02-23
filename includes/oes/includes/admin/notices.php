<?php

namespace OES\Admin;


use OES\Config\Option;

if (!defined('ABSPATH')) exit;


/**
 * Store an OES notice to options to display it after page refresh.
 *
 * @param string $notice A string containing the OES notice.
 * @param string $type  A string containing the OES notice type. Valid values are 'info', 'warning', 'error', 'success'.
 *                      The default is 'info'.
 * @param boolean $dismissible A boolean indicating if the notice is dismissible. Default is true.
 *
 * @return void
 */
function add_oes_notice_after_refresh($notice, $type = 'info', $dismissible = true)
{
    /* get option with saved notice */
    $notices = get_option(Option::OES_MESSAGE, []);

    /* validate notice type */
    $type = in_array($type, ['info', 'warning', 'error', 'success']) ? $type : 'info';

    /* add notice */
    array_push($notices, [
        'notice' => $notice,
        'type' => $type,
        'dismissible' => ($dismissible ? 'is-dismissible' : '')
    ]);

    /* update option */
    update_option(Option::OES_MESSAGE, $notices);
}

/**
 * Display the messages that are stored in the option for oes messages.
 *
 * @return void
 */
function display_oes_notices_after_refresh()
{
    /* get option */
    $notices = get_option(Option::OES_MESSAGE, []);

    /* display all notices */
    foreach ($notices as $notice) {
        printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $notice['type'],
            $notice['dismissible'],
            $notice['notice']
        );
    }

    /* reset option and delete message */
    if (!empty($notices)) delete_option(Option::OES_MESSAGE);
}

add_action('oes_admin_notices', 'OES\Admin\display_oes_notices_after_refresh', 12);