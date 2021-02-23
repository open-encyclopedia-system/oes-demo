<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Admin_Initialize_Assets')) :

    /**
     * Class OES_Admin_Initialize_Assets
     *
     * Register and enqueue styles and scripts.
     */
    class OES_Admin_Initialize_Assets
    {

        /** @var array Array containing the scripts to be registered */
        var $scripts = [];

        /** @var array Array containing the styles to be registered */
        var $styles = [];


        /**
         * OES_Admin_Initialize_Assets constructor.
         */
        function __construct()
        {
            add_action('init', [$this, 'register_scripts_and_styles']);
            add_action('wp_enqueue_scripts', [$this, 'load_css'], 0);
            add_action('wp_enqueue_scripts', [$this, 'load_js'], 0);
        }


        /**
         * Add script to be registered.
         *
         * @param string $handle A string containing the name of the script.
         * @param string $src A string containing the full url of the script. If false, script is alias.
         * @param array $depends Optional array containing registered script handles that this script depends on.
         * @param string|boolean $ver Optional string containing the script version number.
         * @param boolean $in_footer Optional boolean indicating whether to enqueue the script before body.
         */
        function add_script($handle, $src, $depends = [], $ver = false, $in_footer = false){
            $this->scripts[$handle] = [
                'handle' => $handle,
                'src' => $src,
                'depends' => $depends,
                'ver' => $ver,
                'in_footer' => $in_footer
            ];
        }


        /**
         * Add style to be registered.
         *
         * @param string $handle A string containing the name of the style.
         * @param string $src A string containing the full url of the style. If false, style is alias.
         * @param array $deps Optional array containing registered style handles that this style depends on.
         * @param string|boolean $ver Optional string containing the style version number.
         * @param string $media Optional string containing the media for which this stylesheet has been defined.
         */
        function add_style($handle, $src, $deps = [], $ver = false, $media = 'all'){
            $this->styles[$handle] = [
                'handle' => $handle,
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'media' => $media
            ];
        }


        /**
         * Register all scripts and styles.
         */
        function register_scripts_and_styles()
        {
            foreach($this->scripts as $script){
                wp_register_script($script['handle'], $script['src'], $script['depends'], $script['ver'], $script['in_footer']);
                wp_enqueue_script($script['handle']);
            }

            foreach($this->styles as $style){
                wp_register_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
                wp_enqueue_style($style['handle']);
            }
        }


        /**
         * Enqueue scripts and styles.
         */
        function enqueue_scripts(){
            add_action('wp_enqueue_styles', [$this, 'load_css'], 0);
            add_action('wp_enqueue_scripts', [$this, 'load_js'], 0);
        }


        /**
         * Load css styles.
         */
        function load_css()
        {
            foreach($this->styles as $style){
                wp_enqueue_style($style['handle']);
            }
        }


        /**
         * Load js scripts.
         */
        function load_js(){

            foreach($this->scripts as $script){
                wp_enqueue_script($script['handle']);
            }
        }
    }

    /* instantiate */
    OES()->assets = new OES_Admin_Initialize_Assets();
    OES()->assets->add_style('oes-admin', OES_PATH_RELATIVE .  '/assets/css/oes-admin.css', [], oes_get_version(), 'all');
    OES()->assets->add_script('oes-admin', OES_PATH_RELATIVE .  '/assets/js/oes-admin.js', 'jquery', oes_get_version(), true);

endif;