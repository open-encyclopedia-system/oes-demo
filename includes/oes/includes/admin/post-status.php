<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Post_Status')) :

    /**
     * Class Post_Status
     */
    class Post_Status
    {

        var $status = [];


        function __construct()
        {



            /* register new status */
            add_action('init', [$this, 'oes_register_post_status'], 5);
            add_action('admin_footer-edit.php', [$this, 'oes_add_status_in_quick_edit']);
            add_action('admin_footer-post.php', [$this, 'oes_add_status_in_post_page']);
            add_action('admin_footer-post-new.php', [$this, 'oes_add_status_in_post_page']);

        }

        function oes_register_post_status()
        {
            /* get all stati */
            global $oes;

            $registerParametersDefault = [
                'label' => __('New Label', 'oes'),
                'public' => true,
                'internal' => false,
                'protected' => false,
                'private' => false,
                'publicly_queryable' => false,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'date_floating' => false,
                'label_count' => _n_noop('New Label', 'New Label', 'oes'),
            ];

            foreach($oes->post_status as $postStatusKey => $postStatus){

                /* array merge with defaults */
                //array_merge($postStatus, $registerParametersDefault);

                /* register status */
                register_post_status($postStatusKey, array_merge($registerParametersDefault, $postStatus));

            }
        }

        function oes_add_status_in_quick_edit()
        {
            echo "<script>
                jQuery(document).ready( function() {
                    jQuery('select[name=\"_status\"]' ).append( '<option value=\"oes-locked\">OES Locked</option>');   
                }); 
                </script>";
        }

        function oes_add_status_in_post_page()
        {
            echo "<script>
                jQuery(document).ready( function() {        
                    jQuery('select[name=\"post_status\"]' ).append( '<option value=\"oes-locked\">OES Locked</option>');
                });
                </script>";
        }

    }

    /* instantiate */
    new Post_Status();

endif;