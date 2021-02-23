<?php

namespace OES\Admin\Tools;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Tools')) :

    /**
     * Class Tools
     *
     * A class to register and display tools.
     */
    class Tools
    {

        /** @var array An array containing all registered tools. */
        var $oesTools = [];

        /** @var string A string containing the name for the tool. */
        var $name = NULL;

        /** @var string String containing an option name for an option on the page. */
        var $optionName = NULL;

        /** @var string String containing the request action for a form on the page. */
        var $action = NULL;

        /** @var bool Boolean indicating if tool has added action after form submit */
        var $addAction = true;

        /** @var string String containing an action name for the form. */
        var $formAction = '';

        /** @var string String containing further parameters for the form. */
        var $formParameters = '';

        /** @var string messaging TODO @2.0 Roadmap : move to OES_Notice */
        var $toolMessage = NULL;


        /**
         * Tools constructor.
         */
        function __construct()
        {
            $this->initialize_parameters();
            $this->validate_parameters();

            if ($this->addAction) add_action("admin_post_$this->action", [$this, 'admin_post']);

            /* call admin notices */
            add_action('admin_notices', [$this, 'display_messages'], 99);
        }


        /**
         * Initialize class parameters
         */
        function initialize_parameters()
        {
        }


        /**
         * Validate class parameters
         */
        function validate_parameters()
        {
            if (!$this->name) $this->addAction = false;
            if (!$this->optionName) $this->optionName = $this->name;
            if (!$this->action) $this->action = $this->name;
        }


        /**
         * Register a tool.
         *
         * @param string $class A string containing the tool class name.
         */
        function register_tool($class)
        {
            $instance = new $class();
            $this->oesTools[$instance->name] = $instance;
        }


        /**
         * Get a tool by the tool name.
         *
         * @param string $name A string containing the tool class name.
         * @return mixed|null Returns the tool class instance or null.
         */
        function get_tool($name)
        {
            return isset($this->oesTools[$name]) ? $this->oesTools[$name] : null;
        }


        /**
         * Get all registered tools.
         *
         * @return array Return an array of all registered tools.
         */
        function get_tools()
        {
            return $this->oesTools;
        }


        /**
         * Display the tool interface as a form.
         *
         * @param string $name A string containing the tool name.
         */
        function display_tool($name)
        {
            /* get the tool by tool name */
            $tool = $this->get_tool($name);

            /* redirect form to current page */
            $redirect = urlencode($_SERVER['REQUEST_URI']);

            /*
            Create form
               - add action input to link to specific tool,
               - create nonce for security,
               - redirect to current page,
               - call form parameters from specific tool.
            */
            ?>
            <form action="<?php echo $tool->formAction; ?>" method="POST"<?php echo $tool->formParameters;?>>
                <input type="hidden" name="action" value="<?php echo $tool->action; ?>">
                <?php wp_nonce_field($tool->action, $tool->optionName . '_nonce', FALSE); ?>
                <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                <?php $tool->html(); ?>
            </form>
            <?php
        }


        /**
         * Display the tool messages as admin notice.
         * TODO @2.0 Roadmap : include oes_notice, wpautop($message['text']);
         */
        function display_messages(){

            /* get the messages */
            if($this->toolMessage){

                foreach($this->toolMessage as $message){

                    /* validate message parameters  */

                    /* type in array */
                    if (!in_array($message['type'], ['info', 'warning', 'error', 'success'])) $message['type'] = 'info';

                    /* text string ends with punctuation */
                    if (substr($message['text'], -1) !== '.' && substr($message['text'], -1) !== '>') {
                        $message['text'] .= '.';
                    }

                    /* validate dismissible parameter */
                    if(!is_bool($message['dismissible'])) $message['dismissible'] = true;

                    \OES\Admin\add_oes_notice_after_refresh($message['text'],
                        $message['type'],
                        $message['dismissible']
                    );

                }
            }
        }


        /**
         * Runs when admin post request for the given action.
         *
         */
        function admin_post()
        {
            /* get the tool by tool name */
            $tool = OES()->adminTools->get_tool($this->name);

            /* validate nonce */
            if (!wp_verify_nonce($_POST[$tool->optionName . '_nonce'], $tool->action))
                die('Invalid nonce.' . var_export($_POST, true));

            /* get tool action */
            $tool->admin_post_tool_action();

            /* check if form has redirection */
            if (!isset ($_POST['_wp_http_referer'])) die('Missing target.');

            /* redirect to tool page */
            add_action('template_redirect', function(){
                if(isset($_POST['_wp_http_referer'])){
                   /* TODO @2.0 Roadmap : wp_safe_redirect(urldecode($_POST['_wp_http_referer'])); */
                }
            });
            wp_safe_redirect(urldecode($_POST['_wp_http_referer']));

            exit;
        }


        /**
         * Tool specific action.
         */
        function admin_post_tool_action()
        {
        }


        /**
         * Display the tools parameters for form.
         */
        function html()
        {
        }

    }

// initialize
    OES()->adminTools = new Tools();

endif;


/**
 * Register a tool.
 *
 * @param string $class A string containing the tool class name.
 * @return mixed Returns the registered instance of the Tool class.
 */
function oes_register_admin_tool($class)
{
    return OES()->adminTools->register_tool($class);
}