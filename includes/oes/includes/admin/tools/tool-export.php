<?php

namespace OES\Admin\Tools;


use WP_Query;
use function OES\ACF\get_all_post_type_fields;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Tools_Export')) :

    /**
     * Class Tools_Export
     *
     * Tool for exporting post data to csv or json files.
     */
    class Tools_Export extends Tools
    {

        /** @var array Stores the data for the output file. */
        var $data = [];


        /**
         * Initialize class parameters
         */
        function initialize_parameters()
        {
            $this->name = 'export';
            $this->formAction = admin_url('admin-post.php');
        }


        /**
         * Display the tools parameters for form.
         */
        function html()
        {

            /* get all post types */
            $choices = [];
            $postTypes = get_post_types(['public' => true], 'objects');
            if ($postTypes) {
                foreach ($postTypes as $postType) {
                    if (!in_array($postType->name, ['post', 'attachment'])) $choices[$postType->name] = $postType->label;
                }
            }
            ?>
            <div id="tools">
                <div>
                    <p><strong><?php _e('Select Post Type', 'oes'); ?></strong></p>
                    <?php
                    /* display radio boxes to select from all custom post types */
                    foreach ($choices as $postTypeName => $postTypeLabel) :?>
                        <input type="radio" id="<?php echo $postTypeName; ?>" name="post_types[]"
                               value="<?php echo $postTypeName; ?>">
                        <label for="<?php echo $postTypeName; ?>"><?php echo $postTypeLabel; ?></label><br>
                    <?php endforeach; ?>
                </div>
                <div>
                    <p><strong><?php _e('Generate Template', 'oes'); ?></strong></p>
                    <input type="checkbox" id="import_template" name="import_template">
                    <label for="import_template"><?php
                        _e('Generate an import template for the selected post type.', 'oes'); ?></label>
                </div>
                <div>
                    <p><strong><?php _e('Select Output Type', 'oes'); ?></strong></p>
                    <label for="output_type"><?php //empty by design ?></label>
                    <select name="output_type" id="output_type">
                        <option value="csv">csv File</option>
                        <option value="json">json File</option>
                    </select>
                </div>
            </div>
            <?php submit_button(__('Download File', 'oes')); ?>
            <?php
        }


        /**
         * Runs when admin post request for the given action.
         *
         * TODO @2.0 Roadmap : implement switch: one or multiple files as zip archive
         * TODO @2.0 Roadmap : error handling concerning old data fields (might crash display of values...)
         * TODO @2.0 Roadmap : oes_notice for error
         */
        function admin_post_tool_action()
        {
            /* get post type -----------------------------------------------------------------------------------------*/

            /* get post type array from form */
            $postType = $_POST['post_types'];

            /* skip if no post type selected */
            if (!$postType) return; //TODO @2.0 Roadmap : oes_notice for error


            /* create file -------------------------------------------------------------------------------------------*/

            /* clean memory */
            ob_clean();

            /* get output type string */
            $fileType = $_POST['output_type'] ? $_POST['output_type'] : 'csv';

            /* create file name */
            $postTypeForFileName = is_int($postType) ? '' : $postType[0];


            /* check if template */
            if ($_POST['import_template']) {

                /* create file name */
                $fileName = 'oes-template-' . $postType[0] . '-' . date('Y-m-d') . '.' . $fileType;

                /* get data array */
                $data = $this->create_template_array($postTypeForFileName);

            } else {

                /* get data */
                $this->get_selected_data([$postType]);

                /* skip if post type has no data */
                if ($this->data === []) return; //TODO @2.0 Roadmap : oes_notice for error

                /* create file name */
                $fileName = 'oes-export-' . $postType[0] . '-' . date('Y-m-d') . '.' . $fileType;

                /* get data array */
                $data = $this->create_data_array();
            }

            /* get output type */
            switch ($fileType) {

                /* write csv file */
                case 'csv':

                    /* open raw memory as file so no temp files needed, might run out of memory though */
                    $csvFile = fopen('php://memory', 'w');

                    /* write content */
                    foreach ($data as $row) fputcsv($csvFile, $row, ';');

                    /* reset the file pointer to the start of the file */
                    fseek($csvFile, 0);

                    /* set browser information to save file instead of displaying it */
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment; filename="' . $fileName . '";');

                    /* process file */
                    fpassthru($csvFile);
                    fclose($csvFile);

                    break;

                /* write json file */
                case 'json':

                    /* set browser information to save file instead of displaying it */
                    header('Content-type: application/json');
                    header('Content-Disposition: attachment; filename="' . $fileName . '";');

                    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                    break;

                default:
                    break;
            }

            /* output file */
            flush();

            /* status update -----------------------------------------------------------------------------------------*/
            $this->toolMessage[] = ['type' => 'success', 'text' => __('File creation successful.', 'oes')];
        }


        /**
         * Get data for selected post types.
         *
         * @param array $postTypes An array containing the selected post types.
         */
        function get_selected_data($postTypes)
        {
            /* bail early if $postType has wrong type */
            if (!is_array($postTypes) || !$postTypes) \OES\Admin\oes_notice("No post type selected.", 'warning');

            /* loop through post types and store data */
            $postTypeData = [];
            foreach ($postTypes as $postType) {

                /* skip if not a string */
                if(is_array($postType) && !is_string($postType[0])) continue;

                /* get all posts of post type */
                $queryPosts = new WP_Query(['post_type' => $postType]);

                if ($queryPosts->have_posts()) {

                    /* add count to messages */
                    $messages[$postType[0]]['number_of_posts'] = $queryPosts->post_count;

                    /* loop through all post of this type */
                    while ($queryPosts->have_posts()) {

                        $queryPosts->the_post();
                        $readPost = get_post();

                        /* get meta data and collect data in $readPostArray */
                        $metaData = get_post_meta($readPost->ID);
                        $readPostArray = $readPost->to_array();

                        /* loop through meta data */
                        foreach ($metaData as $key => $field) {

                            /* skip _fields */
                            if (!oes_starts_with($key, '_')) {

                                /* check if database value */
                                if (preg_match_all("/(?:(?:\"(?:\\\\\"|[^\"])+\")|(?:'(?:\\\'|[^'])+'))/is",
                                    $field[0], $matches)) {

                                    /* avoid quotes in database values */
                                    array_walk_recursive($matches, 'oes_replace_double_quote');

                                    $readPostArray['fields'][$key] = oes_array_to_string_flat($matches);
                                } else {
                                    $readPostArray['fields'][$key] = $field[0];
                                }
                            }
                        }

                        /* add data to data collector */
                        $postTypeData[] = $readPostArray;
                    }

                    /* reset query */
                    wp_reset_postdata();

                } else {
                    /* add message that no post where found */
                    $messages[$postType]['number_of_posts'] = 0;
                    $messages[$postType]['warning'][] = 'The post type ' . $postType . ' has no existing posts.';
                }

                /* add data to class variable */
                if (array_key_exists('all', $this->data)) {
                    $this->data['all'] = array_merge($this->data['all'], $postTypeData);
                } else {
                    $this->data['all'] = $postTypeData;
                }
            }
        }


        /**
         * Create an array containing post type parameters and fields and all posts of this post type.
         *
         * @return mixed Returns an array containing post type parameters and fields and all posts of this post type.
         */
        function create_data_array()
        {
            /* prepare data arrays */
            $columnHeader = [];
            $dataArray = [];

            /* loop through single posts */
            foreach ($this->data['all'] as $singlePost) {

                /* check for fields */
                if (isset($singlePost['fields'])) {

                    /* loop through fields */
                    foreach ($singlePost['fields'] as $fieldKey => $singleField) {

                        /* TODO @2.0 Roadmap : implement switch: skip content */
                        /* skip content fields */
                        if ($fieldKey != 'content' && 'text_content') {
                            $singlePost[$fieldKey] = oes_cast_to_string($singleField);
                        }
                    }
                    unset($singlePost['fields']);
                }

                /* check if field key is already part of column header, if not, add header key */
                foreach ($singlePost as $entryKey => $singleEntry) {
                    if (!in_array($entryKey, $columnHeader)) $columnHeader[] = $entryKey;
                }

                /* build row */
                $dataArrayRow = [];
                foreach ($columnHeader as $column) {

                    /* add value if post has a value for this field */
                    if (isset($singlePost[$column])) {

                        /* cast field value to string */
                        $rowData = oes_cast_to_string($singlePost[$column]);

                        /* add field value to row data (replace characters that break csv display) */
                        $dataArrayRow[] = oes_csv_escape_string($rowData);
                    }

                    /* else leave empty */
                    else $dataArrayRow[] = null;
                }

                /* add row data to return variable */
                $dataArray[] = $dataArrayRow;
            }

            return array_merge([$columnHeader], $dataArray);
        }


        /**
         * Create an array containing post type parameters and fields
         *
         * @param string $postType A string containing the post type.
         * @return mixed Returns an array containing post type parameters and fields.
         */
        function create_template_array($postType)
        {

            /* prepare field data */
            $fieldData = [
                'operation',
                'post_type',
                'ID',
                'post_title',
                'post_author',
                'post_status',
                'post_parent',
                'post_name',
                'post_parent'
            ];

            /* get all acf fields for post type */
            foreach (get_all_post_type_fields($postType, false) as $fieldKey => $field) {

                /* skip message fields */
                if($field['type'] == 'message') continue;

                $fieldData[] = $fieldKey;
            }

            /* add first rows */
            return [
                $fieldData,
                ['insert', $postType],
                ['update', $postType],
                ['delete', $postType]
            ];
        }


    }

// initialize
    oes_register_admin_tool('\OES\Admin\Tools\Tools_Export');

endif;