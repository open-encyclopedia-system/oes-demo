<?php

/**
 * Class OES_Includes_Project
 *
 * TODO @afterRefactoring : This implements Oes_Project_Config_Base to modify abstract functions getAllPostTypes and
 * getMainHtmlAppId. After refactoring this will be moved to the parent class.
 */
class OES_Includes_Project extends Oes_Project_Config_Base
{

    function __construct($projectPluginBaseDir)
    {
        parent::__construct($projectPluginBaseDir);

        /* add data processing before save */
        add_filter('save_post', [$this, 'save_post_data_processing'], 10, 2);
    }


    function save_post_data_processing($post_id, $post){
    }

    /**
     * @return mixed
     */
    function getAllPostTypes()
    {
        return OES_Project_Config::POST_TYPE_ALL;
    }

    /**
     * @return string
     */
    function getMainHtmlAppId()
    {
        return 'oes-demo';
    }
}