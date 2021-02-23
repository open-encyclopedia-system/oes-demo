<?php

namespace OES\Admin\Tools;


use WP_Error;
use WP_Post;
use WP_Term;
use function OES\ACF\get_acf_field;
use function OES\Admin\add_oes_notice_after_refresh;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Tools_Import')) :

    /**
     * Class Tools_Import
     *
     * Import post, terms and relations from csv files.
     */
    /**
     * Class Tools_Import
     * @package OES\Admin\Tools
     */
    /**
     * Class Tools_Import
     * @package OES\Admin\Tools
     */
    class Tools_Import extends Tools
    {
        /** An array containing all parameters for wp_insert_term. */
        const ARGS_WP_INSERT_TERM = ['term', 'taxonomy', 'alias_of', 'description', 'parent', 'slug'];

        /** An array containing all parameters for wp_insert_post. */
        const ARGS_WP_INSERT_POST = ['ID', 'post_type', 'post_title', 'post_status', 'post_author', 'post_date',
            'post_date_gmt', 'post_content', 'post_content_filtered', 'post_excerpt', 'comment_status',
            'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
            'post_parent', 'menu_order', 'post_mime_type', 'guid', 'post_category', 'tags_input', 'tax_input',
            'meta_input'];

        /** @var array An array storing all messages during import. */
        var $messages = [];


        /**
         * Initialize class parameters
         */
        function initialize_parameters()
        {
            $this->name = 'import';
            $this->formAction = admin_url('admin-post.php');
            $this->formParameters = ' enctype="multipart/form-data"';
        }


        /**
         * Display the tools parameters for form.
         */
        function html()
        {
            ?>
            <div id="tools">
                <p><strong><?php _e('Select File:', 'oes'); ?></strong></p>
                <input type="file" id="import_file" name="import_file">
            </div>
            <div id="tools-checkbox">
                <input type="checkbox" id="force_delete" name="force_delete">
                <label for="force_delete"><?php
                    _e('If deleting a post, force delete it permanently (instead of moving the post to trash).',
                        'oes'); ?></label>
            </div>
            <?php
            submit_button(__('Import File', 'oes'));
        }


        /**
         * Runs when admin post request for the given action.
         */
        function admin_post_tool_action()
        {

            /* Open file ---------------------------------------------------------------------------------------------*/

            /* check file size */
            if (empty($_FILES['import_file']['size'])) {
                add_oes_notice_after_refresh(__('No file selected', 'oes'), 'warning');
                return false;
            }

            /* get file data */
            $file = $_FILES['import_file'];

            /* check for errors */
            if ($file['error']) {
                add_oes_notice_after_refresh(__('Error uploading file. Please try again', 'oes'), 'warning');
                return false;
            }

            /* check file type */
            if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
                add_oes_notice_after_refresh(__('Incorrect file type. CSV-File required.', 'oes'), 'warning');
                return false;
            }


            /* Read file ---------------------------------------------------------------------------------------------*/

            /* try to open file */
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle == FALSE) {
                add_oes_notice_after_refresh(__('Can not open file.', 'oes'), 'error');
                return false;
            }

            /* set time limit for import, apply filter to modify limit */
            $timeLimit = 600;
            set_time_limit(apply_filters('oes_import_time_limit', $timeLimit));

            /* check if file is empty */
            if (($firstRow = fgetcsv($handle, 0, ";")) == null) {
                add_oes_notice_after_refresh(sprintf(__('File %s is empty', 'oes'), $file['name']), 'warning');
                return false;
            }

            /* get number of columns and prepare labels */
            $numColumns = count($firstRow);
            $readFieldLabels = [];
            for ($col = 0; $col < $numColumns; $col++) {
                /* strip value of UTF-8 tags (ZWNBSP) */
                $readFieldLabels[$col] = str_replace('?', '', utf8_decode($firstRow[$col]));
            }

            /* filter for matching labels */
            $matchedFieldLabels = $this->match_labels($readFieldLabels);

            /* sort fields into WordPress objects 'post', 'term', (post-/term-) 'meta'. */
            $colMatch = [];
            $operationIncluded = false;
            foreach ($matchedFieldLabels as $key => $label) {
                if ($label == 'operation') $operationIncluded = $key;
                elseif (in_array($label, self::ARGS_WP_INSERT_POST)) $colMatch[$key] = 'post';
                elseif (in_array($label, self::ARGS_WP_INSERT_TERM)) $colMatch[$key] = 'term';
                elseif ($label == 'post_term_ID') $colMatch[$key] = 'post_term';
                else $colMatch[$key] = 'meta';

                /* TODO @2.0 Roadmap : add case that post meta and term meta in same file. */
            }

            /* Loop through rows -------------------------------------------------------------------------------------*/
            $row = 0;
            $actionPosts = []; /* store IDs for after action */
            $actionTerms = []; /* store IDs for after action */
            $afterProcessParents = []; /* prepare data for after processing */

            while (($nextRow = fgetcsv($handle, 0, ";")) !== FALSE) {

                /* TODO @2.0 Roadmap : handle encoding to include umlaute */
                // $nextRow = array_map("utf8_encode", $nextRow);

                /* prepare row */
                $row++;
                $this->messages[$row]['row'] = $row;

                /* read row into value array */
                $values = [];
                for ($col = 0; $col < $numColumns; $col++) {
                    /* skip not matched and operation columns */
                    if ((!$operationIncluded || $col != $operationIncluded) && $matchedFieldLabels[$col])
                        $values[$colMatch[$col]][$matchedFieldLabels[$col]] = $nextRow[$col];
                }

                /* get operation */
                $operation = $operationIncluded ? $nextRow[$operationIncluded] : 'update';

                /* skip if no valid operation */
                if (!in_array($operation, ['insert', 'update', 'delete'])) {
                    $this->messages[$row]['error'] = 'No valid operation type.';
                    break;
                }

                /* trace operation */
                $this->messages[$row]['operation'] = $operation;

                /* optional modify or augment values */
                $values = $this->modify_values($values, $readFieldLabels);

                /* differentiate operations --------------------------------------------------------------------------*/
                switch ($operation) {


                    /* INSERT *****************************************************************************************/
                    case 'insert' :
                    case 'update' :

                        /* check if post data */
                        if (isset($values['post'])) {

                            /* skip insert post */
                            if ($values['skip_insert']) {
                                $post = is_int($values['skip_insert']['post_id']) ?
                                    get_post($values['skip_insert']['post_id']) : false;
                            } /* insert post -------------------------------------------------------------------------*/
                            else {
                                $post = $this->try_insert_post($values['post'], $operation, $row);
                            }

                            /* check if successful */
                            if ($post instanceof WP_Post) {

                                if (!$values['skip_insert']) $actionPosts[] = $post;

                                /* update fields and post meta -------------------------------------------------------*/
                                if (!empty($values['meta'])) {
                                    $this->try_insert_post_meta($post->ID, $values['meta'], $row,
                                        $values['add'] ? $values['add'] : false);
                                }

                                /* update terms ----------------------------------------------------------------------*/
                                /* TODO @2.0 Roadmap : differentiate between append and replace */
                                if (!empty($values['post_term'])) {
                                    $this->try_insert_post_terms($values['post_term'], $post->ID, $row);
                                }

                                /* prepare parent --------------------------------------------------------------------*/
                                if (!empty($values['parent'])) $afterProcessParents[$post->ID] =
                                    array_merge(['object_ID' => $post->ID], $values['parent']);;

                            }
                        } /* check if term data */
                        elseif (isset($values['term'])) {

                            /* insert term or skip */
                            if ($values['skip_insert']) {
                                $term = is_int($values['skip_insert']['term_id']) ?
                                    get_term($values['skip_insert']['term_id']) : false;
                            } else {
                                $term = $this->try_insert_term($values['term'], $row);
                            }

                            /* check if successful */
                            if ($term instanceof WP_Term) {

                                if (!$values['skip_insert']) $actionTerms[] = $term;

                                /* update fields and post meta -------------------------------------------------------*/
                                if (!empty($values['meta'])) {
                                    $this->try_insert_post_meta($term->term_id, $values['meta'], $row, $term->taxonomy);
                                }

                                /* prepare parent --------------------------------------------------------------------*/
                                if (!empty($values['parent']))
                                    $afterProcessParents[$row] =
                                        array_merge(['object_ID' => $term->term_id], $values['parent']);
                            }
                        }

                        break;


                    /* DELETE *****************************************************************************************/
                    case 'delete' :

                        /* delete post */
                        if (isset($values['post']['ID'])) $this->try_delete_post($values['post']['ID'], $row);

                        /* delete term */
                        if (isset($values['term']['term_id'])) {
                            $this->try_delete_term($values['term']['term_id'], $values['term']['taxonomy'], $row);
                        }

                        break;

                    default:
                        $this->messages[$row]['error'] = sprintf(__('Unknown operation for row %1s.', 'oes'), $row);
                        break;

                }

            }

            /* Close file --------------------------------------------------------------------------------------------*/

            fclose($handle);

            /* do after action */
            if (!empty($afterProcessParents)) {
                foreach ($afterProcessParents as $row => $processInfo) {
                    $this->messages[$row]['parent'] = ' ' .
                        call_user_func($processInfo['callback'],
                            array_merge([
                                    'post_ID' => $processInfo['object_ID']
                            ], $processInfo['args']));
                }
            }
            do_action('oes_import_after_processing', $actionPosts, $actionTerms);

            /* generate status message */
            $this->prepare_notice();

            /* return success */
            return true;
        }


        /**
         * Match labels for import.
         *
         * @param array $fieldLabels An array containing the labels (first row of csv file)
         * @return mixed|void Returns error or array with matched labels.
         */
        function match_labels($fieldLabels)
        {
            /* apply filters before */
            $fieldLabels = apply_filters('oes_import_match_labels_before', $fieldLabels);

            /* prepare matched labels */
            $returnLabels = [];

            /* check if in match array */
            $matchArray = apply_filters('oes_import_match_data_array', false, $fieldLabels);
            /* TODO @2.0 Roadmap : validate array */

            if ($matchArray) {

                /* check for fields and loop through fields */
                if (isset($matchArray['fields'])) {
                    foreach ($fieldLabels as $key => $value) {
                        if (isset($matchArray['fields'][$value])) {

                            /* check if field has  callback */
                            $returnLabels[$key] = is_array($matchArray['fields'][$value]) ?
                                $matchArray['fields'][$value]['fieldKey'] :
                                $matchArray['fields'][$value];
                        } /* add to label if key is part of post, term or operation*/
                        elseif ($value == 'operation'
                            || in_array($value, self::ARGS_WP_INSERT_POST)
                            || in_array($value, self::ARGS_WP_INSERT_TERM)) {
                            $returnLabels[$key] = $value;
                        }
                    }
                }
            } else {
                /* no modification */
                $returnLabels = $fieldLabels;
            }

            /* apply filters after */
            $returnLabels = apply_filters('oes_import_match_labels_after', $returnLabels);

            return $returnLabels;
        }


        /**
         * Modify values for import.
         *
         * @param array $values An array containing the values (for this csv-file row).
         * @param array $fieldLabels An array containing the field labels.
         * @return mixed|void Returns error or array with modified values.
         */
        function modify_values($values, $fieldLabels)
        {
            /* prepare return value */
            $returnValues = $values;

            /* add filter to modify or augment values */
            $returnValues = apply_filters('oes_import_modify_values_before', $returnValues, $fieldLabels);

            /* check if in match array */
            $matchArray = apply_filters('oes_import_match_data_array', false, $fieldLabels);
            /* TODO @2.0 Roadmap : validate array */

            if ($matchArray) {

                /* check if additional fields, merge to existing values */
                if (isset($matchArray['additional'])) {

                    /* check for callbacks */
                    foreach ($matchArray['additional'] as $key => $valueArray) {
                        foreach ($valueArray as $field => $value) {

                            /* prepare value */
                            $additionalValue = '';

                            /* check for callback */
                            if (is_array($value)) {
                                if (isset($value['callback'])) {
                                    $args = isset($value['args']) ? $value['args'] : [];
                                    $args = array_merge(['values' => $values], $args);
                                    $additionalValue = call_user_func($value['callback'], $args);
                                }
                            } else {
                                /* TODO @2.0 Roadmap : validate value */
                                $additionalValue = is_string($value) ? $value : '';
                            }
                            $returnValues[$key][$field] = $additionalValue;
                        }
                    }
                }

                /* check for callbacks */
                if (isset($matchArray['fields'])) {

                    /* prepare matching array */
                    foreach ($matchArray['fields'] as $tableKey => $match) {
                        if (is_array($match)) {
                            $fieldKey = $match['fieldKey']; /* TODO @2.0 Roadmap : validate value */
                            if (isset($match['callback'])) $tempStore[$fieldKey] = $match;
                        }
                    }

                    /* store as new value */
                    foreach ($returnValues as $subArray => $subArrayValues) {
                        foreach ($subArrayValues as $fieldKey => $fieldValue) {

                            /* call function*/
                            if (isset($tempStore[$fieldKey])) {
                                /* add original value */
                                $args = array_merge([
                                    'match_value' => $fieldValue,
                                    'field_key' => $fieldKey,
                                    'values' => $values
                                ],
                                    isset($tempStore[$fieldKey]['args']) ? $tempStore[$fieldKey]['args'] : []);

                                /* check if post - term relation */
                                if ($fieldKey == 'post_term_ID') {
                                    $returnValues[$subArray] =
                                        call_user_func($tempStore[$fieldKey]['callback'], $args);
                                } else {
                                    $returnValues[$subArray][$fieldKey] =
                                        call_user_func($tempStore[$fieldKey]['callback'], $args);
                                }
                            }
                        }
                    }
                }

                /* check if skip insert */
                if (isset($matchArray['skip_insert'])) {

                    /* skip post */
                    if ($matchArray['skip_insert'] == 'ID') {
                        $returnValues['skip_insert']['post_id'] = $returnValues['post']['ID'];
                    } elseif ($matchArray['skip_insert'] == 'term_id') {
                        $returnValues['skip_insert']['term_id'] = $returnValues['term']['term_id'];
                    } else { /* TODO @2.0 Roadmap : return error */
                    }
                }

                /* check for parent and store for after processing */
                /* TODO @2.0 Roadmap : validate value */
                if (isset($matchArray['parent'])) $returnValues['parent'] = $matchArray['parent'];

            }

            /* add filter to modify or augment values */
            $returnValues = apply_filters('oes_import_modify_values_after', $returnValues, $fieldLabels);

            return $returnValues;
        }


        /**
         * Try to insert a post and store return messages.
         *
         * @param array $args An array containing post arguments for wp_insert_post.
         * @param string $operation A string containing the operation action.
         * @param int $row An int containing the row index.
         * @return array|false|WP_Post Returns post on success or error string.
         */
        function try_insert_post($args, $operation, $row)
        {

            /* insert post */
            $insertResult = oes_insert_post($args, ($operation == 'insert') ? false : true);

            /* check if successful */
            if (isset($insertResult['post'])) {

                /* get result post ID */
                $postID = $insertResult['post'];

                /* check if error */
                if (!is_int($postID)) {

                    /* error in oes_insert_post */
                    if (is_string($postID)) $this->messages[$row]['error'][] = $postID;

                    /* error in wp_insert_post */
                    elseif (is_wp_error($postID)) {
                        foreach ($postID->get_error_messages() as $error)
                            $this->messages[$row]['error'][] = $error;
                    } /* unknown error */
                    else $this->messages[$row]['error'][] = __('Unknown error.', 'oes');

                    /* set post ID to false */
                    return false;
                } else {
                    /* check if wrong parameter */
                    if (!empty($insertResult['wrong_parameter'])) {
                        $this->messages[$row]['error'][] =
                            sprintf(__('The following fields were ignored: %1s', 'oes'),
                                implode(', ', $insertResult['wrong_parameter']));
                    }
                }
            } else {
                $this->messages[$row]['error'][] =
                    is_string($insertResult) ? $insertResult : __('Unknown error.', 'oes');
                return false;
            }

            /* validate post */
            if ($postID) {

                /* get post */
                $post = get_post($postID);

                /* validate */
                if (empty($post) || is_wp_error($post)) {
                    $this->messages[$row]['error'][] = __('Error while getting post.', 'oes');
                    return false;
                } else {
                    /* trace insert */
                    $this->messages[$row]['post_title'] = $post->post_title;
                    $this->messages[$row]['post_ID'] = $post->ID;
                    $this->messages[$row]['edit_post'] = true;
                }
            } else {
                return false;
            }

            return $post;
        }


        /**
         * Try to insert a term and store return messages.
         *
         * @param array $args An array containing term arguments for wp_insert_term.
         * @param int $row An int containing the row index.
         * @return array|false|WP_Term Returns term on success or error string.
         */
        function try_insert_term($args, $row)
        {
            /* insert term */
            $insertResult = oes_insert_term($args);

            /* check if successful */
            if (isset($insertResult['term'])) {

                /* check if error */
                if (is_wp_error($insertResult['term'])) {
                    foreach ($insertResult['term']->get_error_messages() as $error)
                        $this->messages[$row]['error'][] = $error;
                    return false;
                }

                /* get result post ID */
                $termID = isset($insertResult['term']['term_id']) ? $insertResult['term']['term_id'] : false;

                /* check if error */
                if (!is_int($termID)) {

                    /* error in oes_insert_term */
                    if (is_string($insertResult['term'])) $this->messages[$row]['error'][] = $insertResult['term'];

                    /* unknown error */
                    else $this->messages[$row]['error'][] = __('Unknown error.', 'oes');

                    /* set post ID to false */
                    return false;
                } else {
                    /* check if wrong parameter */
                    if (!empty($insertResult['wrong_parameter'])) {
                        $this->messages[$row]['error'][] =
                            sprintf(__('The following fields were ignored: %1s', 'oes'),
                                implode(', ', $insertResult['wrong_parameter']));
                    }
                }
            } else {
                $this->messages[$row]['error'][] =
                    is_string($insertResult) ? $insertResult : __('Unknown error.', 'oes');
                return false;
            }

            /* validate term */
            if ($termID) {

                /* get post */
                $term = get_term($termID);

                /* validate */
                if (empty($term) || is_wp_error($term)) {
                    $this->messages[$row]['error'][] = __('Error while getting post.', 'oes');
                    return false;
                } else {
                    /* trace insert */
                    $this->messages[$row]['post_title'] = $term->name;
                    $this->messages[$row]['post_ID'] = $term->term_id;
                    $this->messages[$row]['edit_post'] = true;
                    $this->messages[$row]['is_term'] = true;
                }
            } else {
                return false;
            }
            return $term;
        }


        /**
         * Try to insert post meta data.
         *
         * @param int $postID An int containing the post or term ID.
         * @param array $meta An array containing the meta data ['field' => 'value'].
         * @param int $row An int containing the row index.
         * @param boolean|string $taxonomy Optional string containing the term taxonomy.
         */
        function try_insert_post_meta($postID, $meta, $row, $taxonomy = false, $add = true)
        {

            /* add post ID to message in case the insert post has been skipped */
            $this->messages[$row]['post_ID'] = $postID;

            /* update fields and post meta */
            if ($taxonomy) {
                $insertParametersResult = oes_insert_term_meta($postID, $taxonomy, $meta);
            } else {
                $insertParametersResult = oes_insert_post_meta($postID, $meta, $add);
            }


            /* check if successful */
            if ($insertParametersResult) {

                if (is_array($insertParametersResult)) {
                    $this->messages[$row]['imported_fields'] =
                        $insertParametersResult['imported_fields'];
                } elseif (is_string($insertParametersResult)) {
                    $this->messages[$row]['error'][] = $insertParametersResult;
                } else {
                    $this->messages[$row]['error'][] =
                        __('Results on meta data insert not readable.', 'oes');
                }
            } else {
                $this->messages[$row]['error'][] =
                    __('Results on meta data insert not readable.', 'oes');
            }
        }


        /**
         * Try to insert post term relations.
         *
         * @param array $terms An array containing the terms ['taxonomy' => 'term'].
         * @param int $postID An int containing the post ID.
         * @param int $row An int containing the row index.
         */
        function try_insert_post_terms($terms, $postID, $row, $append = true)
        {
            /* add post ID to message in case the insert post has been skipped */
            $this->messages[$row]['post_ID'] = $postID;

            /* loop through terms */
            if ($terms && !empty($terms)) {
                foreach ($terms as $taxonomy => $term) {

                    /* try to insert relation */
                    $insertTermsResults = wp_set_post_terms($postID, $term, $taxonomy, $append);

                    /* check if successful */
                    if ($insertTermsResults) {

                        /* insert successful */
                        if (is_array($insertTermsResults)) {
                            foreach ($insertTermsResults as $resultTermID) {
                                $this->messages[$row]['imported_terms'][] =
                                    sprintf('<a href="%1s" target="_blank">%2s (%3s)</a>',
                                        get_edit_term_link($resultTermID, get_term($resultTermID)->taxonomy),
                                        get_term($resultTermID)->name,
                                        $resultTermID
                                    );
                            }
                        } /* error in wp_set_post_terms */
                        elseif (is_wp_error($insertTermsResults)) {
                            foreach ($insertTermsResults->get_error_messages() as $error)
                                $this->messages[$row]['error'][] = $error;
                        } /* unknown error */
                        else {
                            $this->messages[$row]['error'][] =
                                __('Results on add term not readable.', 'oes');
                        }
                    }
                }
            }
        }


        /**
         * Try to delete a post.
         *
         * @param int $postID An int containing the post ID.
         * @param int $row An int containing the row index.
         * @return WP_Post|WP_Error|bool|string Returns post on success or error messages.
         */
        function try_delete_post($postID, $row)
        {

            /* delete post */
            $post = oes_delete_post($postID, $_POST['force_delete'] ? true : false);

            /* add message information */
            $this->messages[$row]['operation'] = $_POST['force_delete'] ? 'deleted' : 'trashed';

            /* check if operation successful */
            if (is_wp_error($post)) {
                foreach ($post->get_error_messages() as $error)
                    $this->messages[$row]['error'][] = $error;
                return false;
            } elseif (is_string($post)) {
                $this->messages[$row]['error'] =
                    sprintf(__('The post with the Post ID %1s could not be deleted. %2s', 'oes'),
                        $postID['post']['ID'],
                        $post
                    );
                return false;
            }

            return $post;
        }


        /**
         * Try to delete term.
         *
         * @param int $termID An int containing the term ID.
         * @param string $taxonomy A string containing the term taxonomy.
         * @param int $row An int containing the row index.
         * @return WP_Error|object|WP_Term|array|bool|int|null Return term on success or error message.
         */
        function try_delete_term($termID, $taxonomy, $row)
        {
            /* delete post */
            $term = wp_delete_term($termID, $taxonomy);

            /* check if operation successful */
            if (is_wp_error($term)) {
                foreach ($term->get_error_messages() as $error)
                    $this->messages[$row]['error'][] = $error;
                return false;
            }

            return $term;
        }


        /**
         * Prepare the messages for the return message after import.
         */
        function prepare_notice()
        {
            /* prepare messages text */
            $messageError = '';
            $messageInfo = '';

            /* loop through all messages */
            foreach ($this->messages as $row => $message) {

                $row = isset($message['row']) ? $message['row'] : '"row missing"';

                /* Error message(s) */
                if (isset($message['error'])) {
                    $messageError .= sprintf(__('An error occurred for row %1d: "%2s"<br>', 'oes'),
                        $row,
                        implode(' ', $message['error']));
                } /* Successful operation */
                else {

                    /* state that operation was successful*/
                    $messageInfo .= sprintf(__('The row %1d has been successfully processed (%2s).', 'oes'),
                        $row,
                        $message['operation'] ? $message['operation'] : 'unknown operation'
                    );

                    $postID = isset($message['post_ID']) ? $message['post_ID'] : 'Post or term ID not found.';
                    $isTerm = $message['is_term'];

                    switch ($message['operation']) {

                        case 'insert' :
                        case 'update' :

                            /* link to edit post */
                            if (isset($message['edit_post'])) {
                                $messageInfo .= __(' Edit here: ', 'oes') .
                                    sprintf('<a href="%1s" target="_blank">%2s (%3s)</a>',
                                        $isTerm ?
                                            get_edit_term_link($postID, get_term($postID)->taxonomy) :
                                            get_edit_post_link($postID),
                                        $isTerm ? get_term($postID)->name : get_the_title($postID),
                                        $postID
                                    );
                            }

                            /* add information about fields */
                            if (isset($message['imported_fields']) && $message['imported_fields']) {
                                $messageInfo .= sprintf(__(' %1s fields have been updated.', 'oes'),
                                    $message['imported_fields']);
                            }

                            /* add information about terms */
                            if (isset($message['imported_terms']) && $message['imported_terms']) {
                                $messageInfo .= sprintf(__(' The terms %1s have been added to the post %2s.', 'oes'),
                                    implode(',', $message['imported_terms']),
                                    sprintf('<a href="%1s" target="_blank">%2s (%3s)</a>',
                                        $isTerm ?
                                            get_edit_term_link($postID, get_term($postID)->taxonomy) :
                                            get_edit_post_link($postID),
                                        $isTerm ? get_term($postID)->name : get_the_title($postID),
                                        $postID
                                    )
                                );
                            }

                            /* add information about parents */
                            if (isset($message['parent']) && $message['parent']) {
                                $messageInfo .= $message['parent'];
                            }

                            break;

                        case 'delete' :
                            break;

                    }

                    /* new row for next message */
                    $messageInfo .= "<br>";
                }
            }

            /* add status message */
            if (!empty($messageError)) add_oes_notice_after_refresh($messageError, 'error');
            if (!empty($messageInfo)) add_oes_notice_after_refresh($messageInfo);

            /* return warning if no message to be displayed */
            if (empty($messageInfo) && empty($messageError))
                add_oes_notice_after_refresh(__('Import ended without return message', 'oes'), 'warning');
        }

    }

    // initialize
    oes_register_admin_tool('\OES\Admin\Tools\Tools_Import');

endif;


/**
 * Match post ID to a project specific field containing a project specific post ID.
 *
 * @param array $args An array containing matching information.
 *          'post_type'     :   A string containing the post type OR
 *          'taxonomy'      :   A string containing the taxonomy.
 *          'return_all'    :   An optional boolean if all post with matching ID should be included. Default is true.
 *          'field_key'     :   An optional string containing the source field that holds the ID to be matched.
 *          'meta_key'      :   A string containing the target meta field containing the matching ID.
 *          'match_value'   :   A string containing the matching value that is stored in 'meta_key'.
 *
 * TODO @2.0 Roadmap : value validating and error managing.
 *
 * @return array|false|mixed Returns error or the matched ID(s).
 */
function get_matched_IDs($args = [])
{
    /* prepare */
    $postTypes = [];
    $taxonomies = [];

    /* query for int <--> post */
    if (isset($args['post_type'])) {
        $postTypes = $args['post_type'];
        $all = isset($args['return_all']) ? $args['return_all'] : false;
    } /* query for int <--> post */
    elseif (isset($args['taxonomy'])) {
        $taxonomies = $args['taxonomy'];
        $all = isset($args['return_all']) ? $args['return_all'] : false;
    } /* query for ACF fields <--> posts */
    elseif (isset($args['field_key'])) {
        $fieldObject = get_field_object($args['field_key']);

        if (!$fieldObject) return false;
        if ($fieldObject['type'] != 'relationship') return false;
        if (empty($fieldObject['post_type'])) return false;

        /* gather query data */
        $postTypes = $fieldObject['post_type'];
        $all = isset($args['return_all']) ? $args['return_all'] : true;
    } else {
        return false;
    }

    $postStatus = $args['post_status'] ? $args['post_status'] : ['draft', 'publish'];
    $numberPosts = $args['numberposts'] ? $args['numberposts'] : -1;

    /* prepare return */
    $returnIDs = [];

    /* get posts */
    if (!empty($postTypes)) {
        $posts = [];
        foreach ($postTypes as $postType) {

            $thisPosts = get_posts([
                'numberposts' => $numberPosts,
                'post_type' => $postType,
                'post_status' => $postStatus,
                'meta_key' => $args['meta_key'],
                'meta_value' => $args['match_value']
            ]);

            if (!empty($thisPosts)) $posts = array_merge($posts, $thisPosts);
        }
        foreach ($posts as $post) $returnIDs[] = $post->ID;

        return $all ? $returnIDs : (empty($returnIDs) ? [] : $returnIDs[0]);
    }

    /* get terms */
    if (!empty($taxonomies)) {
        $terms = [];
        foreach ($taxonomies as $taxonomy) {

            $thisTerms = get_terms([
                'taxonomy' => $taxonomy,
                'meta_key' => $args['meta_key'],
                'meta_value' => $args['match_value'],
                'hide_empty' => false
            ]);

            if (!empty($thisTerms)) {
                if (isset($terms[$taxonomy])) $terms[$taxonomy] = array_merge($terms[$taxonomy], $thisTerms);
                else $terms[$taxonomy] = $thisTerms;
            }
        }
        foreach ($terms as $taxonomy => $taxonomyTerms) {
            foreach ($taxonomyTerms as $term) $returnIDs[$taxonomy][] = $term->term_id;
        }
        return $all ? $returnIDs : (empty($returnIDs) ? [] : $returnIDs[$taxonomy][0]);
    }
    return [];
}


/**
 * Insert parent for post while matching the parent ID to an acf field holding a project specific ID.
 *
 * @param array $args An array containing information for child and parent.
 *          'post_ID'     :   The post ID.
 *          'field_source'      :   A string containing the field name where the parent ID is stored in the parent post.
 *          'field_target'    :   A string containing the field where the parent ID is stored in the child post.
 *
 * TODO @2.0 Roadmap : value validating and error managing, check if parent already exists.
 *
 * @return array|false|mixed Returns operation message.
 */
function insert_parent($args)
{
    $postID = $args['post_ID'];
    $parentIDMatchField = $args['field_source'];
    $IDMatchField = $args['field_target'];

    $returnMessage = '';

    /* object is post */
    if (get_post($postID)) {

        /* get post */
        $post = get_post($postID);

        $acfParent = get_acf_field($parentIDMatchField, $post->ID);

        /* process parent */
        if (!empty($acfParent)) {

            /* get corresponding parent post */
            $parentPosts = oes_get_wp_query_posts([
                'post_type' => $post->post_type,
                'meta_key' => $IDMatchField,
                'meta_value' => $acfParent
            ]);

            /* parent not found */
            if (empty($parentPosts)) {
                return sprintf(__('Parent post with access ID %1s not found.', 'oes'), $acfParent);
            }

            /* update post by adding parent */
            foreach ($parentPosts as $parentPost) {
                $success = wp_update_post(['ID' => $post->ID, 'post_parent' => $parentPost->ID], true);

                /* check if update successful */
                if (gettype($success) == 'WP_Error' || !$success) {
                    return sprintf(
                        __('Error while inserting parent post (%1s).', 'oes'), $parentPost->post_title
                    );
                } else {
                    return sprintf(__('Insert parent post (%1s).', 'oes'), $parentPost->post_title);
                }
            }
        }
    } /* object is term */
    elseif (get_term($postID)) {

        /* get term */
        $term = get_term($postID);

        $acfParent = get_acf_field($parentIDMatchField, $term->taxonomy . '_' . $term->term_id);

        /* process parent */
        if (!empty($acfParent)) {

            if ($IDMatchField == 'name') {

                /* get corresponding term */
                $parentTerms = [get_term_by('name', $acfParent, $term->taxonomy)];
            } else {
                /* get corresponding parent post */
                $parentTerms = get_terms([
                    'taxonomy' => $term->taxonomy,
                    'meta_key' => $IDMatchField,
                    'meta_value' => $acfParent,
                    'hide_empty' => false
                ]);

            }

            /* parent not found */
            if (empty($parentTerms)) {
                return sprintf(__('Parent post with access ID %1s not found.', 'oes'), $acfParent);
            }

            /* update post by adding parent */
            foreach ($parentTerms as $parentTerm) {

                /* insert term if it does not exists */
                if (!$parentTerm) {
                    $insertTerm = oes_insert_term(['term' => $acfParent, 'taxonomy' => $term->taxonomy]);
                    $parentTermID = $insertTerm['term']['term_id'];
                } else {
                    $parentTermID = $parentTerm->term_id;
                }

                if ($parentTermID) {

                    /* try to insert term */
                    $updateTerm = oes_insert_term([
                        'term_id' => $term->term_id,
                        'taxonomy' => $term->taxonomy,
                        'parent' => $parentTermID],
                        true);

                    /* check if update successful */
                    if (is_array($updateTerm)) {

                        if (isset($updateTerm['term'])) {

                            if (is_wp_error($updateTerm['term'])) {
                                return sprintf(
                                    __('Error while inserting parent term (%1s).%2s', 'oes'),
                                    get_term($parentTermID)->name,
                                    implode(' ', $updateTerm['term']->get_error_messages())
                                );
                            } else {
                                return sprintf(__('Insert parent term (%1s).', 'oes'),
                                    get_term($parentTermID)->name
                                );
                            }
                        } else {
                            return sprintf(
                                __('Error while inserting parent term (%1s).', 'oes'),
                                get_term($parentTermID)->name
                            );
                        }
                    }

                } else {
                    return sprintf(
                        __('Parent term "%1s" not found and could not be inserted.', 'oes'),
                        $acfParent
                    );
                }
            }
        }
    }

    /* return */
    return false;
}
