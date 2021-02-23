<?php

namespace OES\ACF;


/**
 * Containing functions to customize acf, process acf fields and display acf fields
 */

// exit if file is called directly
if (!defined('ABSPATH')) exit;


if (!class_exists('OES_ACF')) :

    /**
     * Class OES_ACF
     */
    class OES_ACF
    {

        /**
         * OES_ACF constructor.
         */
        public function __construct(){

            /* modify acf filter to exclude self reference */
            add_filter('acf/fields/relationship/query', [$this, 'modify_query_self_reference'], 10, 3);

            //TODO @2.0 Roadmap : Include custom field types
            //add_action( 'acf/include_field_types', 'oes_acf_include_field_types' );
        }


        function modify_query_self_reference($args, $field, $post){
            $args['post__not_in'] = [$post];
            return $args;
        }

    }

OES()->acf = new OES_ACF();

endif;


/**
 * Main function to get field values.
 *
 * TODO @2.0 Roadmap : modify function for solr?
 *
 * @param $fieldName (string) the field name or key
 * @param $postID (mixed) the post_id of which the value is saved against
 *
 * @return mixed Return field value.
 */
function get_acf_field($fieldName, $postID = false)
{
    return get_field($fieldName, $postID);
}


/**
 * Main function to get field object.
 *
 * TODO @2.0 Roadmap : modify function for solr?
 *
 * @param $fieldName (string) the field name or key
 *
 * @return mixed Return field object.
 */
function get_acf_field_object($fieldName)
{
    return get_field_object($fieldName);
}


/**
 * Get all fields connected to a post type. Optional filtered by field types.
 *
 * @param string $postType A string containing the post type key.
 * @param string[] $fieldTypes Optional array containing the considered field types.
 * @return array Returns an array containing the fields.
 */
function get_all_post_type_fields($postType, $fieldTypes = ['text', 'textarea', 'wysiwyg', 'url']){

    $postTypeFields = [];

    /* loop through all acf field groups connected to this post type */
    foreach (acf_get_field_groups(['post_type' => $postType]) as $acfGroup) {

        /* loop through the fields of this field group */
        foreach (acf_get_fields($acfGroup) as $field) {

            /* skip if field type is tab or message */
            if($field['type'] == 'tab' || $field['type'] == 'message') continue;

            /* skip field if it does not match filter option '$fieldTypes' */
            if($fieldTypes && !in_array($field['type'], $fieldTypes)) continue;

            /* prepare return variable */
            $postTypeFields[$field['name']] = $field;
        }
    }

    return $postTypeFields;
}


/**
 * Get select value from acf field.
 *
 * @param string $fieldName A string containing the field name.
 * @param int|boolean $postID An int containing the post ID.
 * @return mixed|string Returns the selected value.
 */
function get_select_field_value($fieldName, $postID = false)
{
    /* get acf field value and label */
    $valueArray = get_acf_field_object($fieldName);

    /* check if multiple value */
    if($valueArray['multiple']){
        $returnValue = [];

        /* loop through values */
        foreach(get_acf_field($fieldName, $postID) as $singleValue){
            $returnValue[$singleValue] = $valueArray['choices'][$singleValue];
        }
    }

    /* single value */
    else{
        $returnValue = get_acf_field($fieldName, $postID) ?
            $valueArray['choices'][get_acf_field($fieldName, $postID)] :
            '';
    }

    return $returnValue;
}


/**
 * Get html anchor form acf post field.
 *
 * @param string $fieldName A string containing the field name.
 * @param int|boolean $postID An int containing the post ID.
 * @param array $args An array containing further information. Valid parameters are:
 *  'title'     : A string containing the anchor title.
 *  'permalink' : A string containing the permalink.
 *  'class'     : A string containing the anchor css class.
 *  'id'        : A string containing the anchor css id.
 *  'target'    : A string containing the target parameter.
 * The title and permalink will be set from acf field if not given in $args.
 * @return string Return string containing a html anchor tag.
 */
function get_field_html_anchor($fieldName, $postID = false, $args = [])
{
    /* get field value */
    $item = get_acf_field($fieldName, $postID);

    /* get further arguments */
    $title = isset($args['title']) ? $args['title'] : get_the_title($item->ID);
    $permalink = isset($args['permalink']) ? $args['permalink'] : get_permalink($item->ID);
    $class = isset($args['class']) ? $args['class'] : false;
    $id = isset($args['id']) ? $args['id'] : false;
    $target = isset($args['target']) ? $args['target'] : false;

    return $item ? oes_get_html_anchor($title, $permalink, $class, $id, $target) : '';
}


/**
 * Get html anchor from acf url field.
 *
 * @param string $fieldName A string containing the field name.
 * @param int|boolean $postID An int containing the post ID.
 * @param array $args An array containing further information. Valid parameters are:
 *  'title'     : A string containing the anchor title.
 *  'permalink' : A string containing the permalink.
 *  'class'     : A string containing the anchor css class.
 *  'id'        : A string containing the anchor css id.
 *  'target'    : A string containing the target parameter.
 * The title, permalink and target will be set from acf field if not given in $args.
 * @return string Return string containing a html anchor tag or empty if field not found.
 */
function get_url_field_html_anchor($fieldName, $postID = false, $args = [])
{
    /* get link */
    $link = get_acf_field($fieldName, $postID);

    /* return empty if field not found */
    if (!$link) return '';

    /* get further arguments */
    $title = isset($args['title']) ? $args['title'] : esc_url($link['title']);
    $permalink = isset($args['permalink']) ? $args['permalink'] : esc_url($link['url']);
    $class = isset($args['class']) ? $args['class'] : false;
    $id = isset($args['id']) ? $args['id'] : false;
    $target = isset($args['target']) ? $args['target'] : esc_url($link['target']);

    return oes_get_html_anchor($title, $permalink, $class, $id, $target);
}


/**
 * Get html img tag from acf image field.
 *
 * @param string $fieldName A string containing the field name.
 * @param array $args An array containing further information. Valid parameters are:
 *  'src'    : A string containing the image source.
 *  'alt'    : A string containing the image alt identifier.
 *  'id'     : A string containing the image css id.
 *  'class'  : A string containing the image css class.
 * The src and alt will be set from acf field if not given in $args.
 *  @param int|boolean $postID An int containing the post ID.
 *
 * @return mixed|string Returns url field as html anchor. Return empty string if url field not found.
 */
function get_image_field_html_src($fieldName, $args, $postID = false)
{
    /* get image field */
    $image = get_acf_field($fieldName, $postID);

    /* return empty if field not found */
    if (!$image) return '';

    $src = isset($args['src']) ? $args['src'] : esc_url($image['url']);
    $alt = isset($args['alt']) ? $args['alt'] : esc_url($image['alt']);
    $id = isset($args['id']) ? $args['id'] : false;
    $class = isset($args['class']) ? $args['class'] : false;

    return oes_get_html_img($src, $alt, $id, $class);
}


/**
 * Get a html list by transforming the values of an acf relationship field.
 *
 * @param string $fieldName A string containing the field name.
 * @param string|boolean $ulID A string containing the css ul id.
 * @param int|false $postID An int containing the post id.
 * @param bool $permalink A boolean indicating if the list items should be displayed as permalink.
 * @return string Returns string containing the html list.
 */
function get_relationship_list($fieldName, $ulID = false, $postID = false, $permalink = true)
{
    /* get relationship field */
    $listItems = get_acf_field($fieldName, $postID);

    return $listItems ? oes_display_post_array_as_list($listItems, $ulID, $permalink) : '';
}


/**
 * Get value, value for frontend display, value key and value information of an acf field.
 *
 * @param string $fieldName A string containing the field name.
 * @param int|boolean $postID An int containing the post ID.
 * @param array $args An array containing further information. Valid parameters are:
 *  'value-is-link' : A boolean identifying if value is to be displayed as link.
 *  'list-id'       : A string containing the list css id.
 * @return array Returns an array containing 'value', 'value-display', 'value-key', 'display-information'
 */
function get_field_value_for_display($fieldName, $postID, $args){

    /* merge with default parameters */
    $args = array_merge(['value-is-link' => false, 'list-id' => false], $args);

    /* get field value */
    $value = get_acf_field($fieldName, $postID);

    /* switch field type */
    switch (get_field_object($fieldName, $postID)['type']) {

        case 'text' :
        case 'textarea' :
        case 'wysiwyg' :
            return ['value' => $value, 'value-display' => $value, 'display' => 'simple'];

        case 'date_picker' :
            return ['value' => $value,
                'value-display' => empty($value) ? '' :
                    date("j F Y", strtotime(str_replace('/', '-', $value))),
                'display' => 'date'];

        case 'relationship' :
            return ['value' => $value,
                'value-display' => oes_display_post_array_as_list($value, $args['list-id']), 'display' => 'list'];

        case 'select' :
        case 'link' :

            /* get selected value(s) */
            $selectedValue = !empty($value) ? get_select_field_value($fieldName, $postID) : '';

            /* multiple values */
            if(is_array($selectedValue)){

                $modifySelectedValue = [];

                /* modify values if they are to be links */
                if($args['value-is-link']) {
                    foreach($selectedValue as $singleValueKey => $singleSelectedValue) {
                        $modifySelectedValue[] =  sprintf('<a href="%1s" target="_blank">%2s</a>',
                            $singleValueKey,
                            $singleSelectedValue);
                    }
                }
                else $modifySelectedValue = $selectedValue;

                $displayValue = implode(', ', $modifySelectedValue);
            }

            /* single value */
            else{
                $displayValue = $args['value-is-link'] ?
                    sprintf('<a href="%1s" target="_blank">%2s</a>', $value, $selectedValue) :
                    $selectedValue;
            }

            return ['value' => $value, 'value-display' => $displayValue, 'display' => 'select'];

        case 'url' :
            $displayValue = '<a href="' . $value . '"  target="_blank">' . $value. '</a>';
            return ['value' => $value, 'value-display' => $displayValue, 'display' => 'select'];

        case 'range' :
        case 'button_group' :
        case 'accordion' :
        case 'checkbox' :
        case 'color_picker' :
        case 'date_time_picker' :
        case 'email' :
        case 'file' :
        case 'google_map' :
        case 'image' :
        case 'number' :
        case 'post_object' :
        case 'radio' :
        case 'taxonomy' :
        case 'time_picker' :
        case 'true_false' :

            //TODO @2.0 Roadmap : code display value for different field types
            return ['value' => $value, 'value-display' => $value, 'display' => 'simple'];

        default :
            return [];
    }
}