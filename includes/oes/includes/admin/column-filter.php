<?php

namespace OES\Admin;


use OES\Config\Post_Type;
use function OES\ACF\get_acf_field;
use function OES\ACF\get_all_post_type_fields;
use function OES\ACF\get_select_field_value;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Column_Filter')) :

    /**
     * Class Column_Filter
     *
     * This class adds custom column filter in list view inside admin layer.
     */
    class Column_Filter
    {

        var $postTypeConf;

        /**
         * Column_Filter constructor.
         */
        function __construct()
        {

            /* prepare filter columns */
            global $oes;

            /* check if filter for any field */
            if ($oes->filter['any']) {
                add_action('restrict_manage_posts', [$this, 'add_column_filter_any_post_field'], 20, 3);
                add_filter('parse_query', [$this, 'apply_column_filter_any_post_field']);
            }

            /* check if custom filter */
            if ($oes->filter['custom']) {

                /* loop through post types and add action and filter for post type that have defined columns */
                foreach ($oes->postTypes as $postType => $postTypeConfiguration) {

                    /* add columns in admin navigation */
                    add_filter('manage_' . $postType . '_posts_columns', [$this, 'add_post_column']);
                    add_action('manage_' . $postType . '_posts_custom_column', [$this, 'display_post_column_value'], 10, 2);

                    /* make columns sortable */
                    add_filter('manage_edit-' . $postType . '_sortable_columns', [$this, 'make_columns_sortable']);

                }

                /* sort columns */
                add_action('pre_get_posts', [$this, 'sort_columns']);

                /* add column filter html */
                add_action('restrict_manage_posts', [$this, 'add_column_filter'], 10, 2);

                /* apply filter*/
                add_filter('parse_query', [$this, 'apply_column_filter']);

            }
        }


        /**
         * Add extra columns to the list display of post types in the wp admin area.
         *
         * @param string $columns The columns for the post type that is being displayed.
         * @return array|string Returns the columns to be displayed.
         */
        function add_post_column($columns)
        {
            /* get global parameter for post type */
            global $oes;
            global $post_type;

            /* bail if no configuration for this post type exists */
            if (!isset($oes->postTypes[$post_type])) return $columns;

            /* prepare new columns */
            $newColumns = [];

            /* check for other columns to be displayed */
            foreach ($oes->postTypes[$post_type]['list_columns'] as $columnKey => $column) {

                switch ($columnKey) {

                    /* check if checkbox is to be displayed */
                    case 'cb' :
                        $newColumns['cb'] = $columns['cb'];
                        break;

                    /* check if title is to be displayed */
                    case'title' :
                        $newColumns['title'] = $columns['title'];
                        break;

                    /* check if title is to be displayed */
                    case 'date' :
                        $newColumns['date'] = $columns['date'];
                        break;

                    default :
                        $newColumns[$columnKey] = $column['label'];

                }
            }
            return $newColumns;
        }


        /**
         * Display values for column in the list display of post types in the wp admin area.
         *
         * @param $column String containing the column name.
         * @param $post_id String containing the post ID.
         */
        function display_post_column_value($column, $post_id)
        {
            /* get global parameter for post type */
            global $oes;
            global $post_type;

            /* check for configurations, return empty String if not found */
            if (!isset($oes->postTypes[$post_type]['list_columns'][$column]['type'])) {
                echo '';
            } else {

                /* get column value depending on field type */
                switch ($oes->postTypes[$post_type]['list_columns'][$column]['type']) {

                    /* get acf field */
                    case 'acf_field':
                        echo get_acf_field($column, $post_id);
                        break;

                    /* get acf select field */
                    case 'acf_field_select' :
                        echo get_select_field_value($column, $post_id);
                        break;

                    /* get post status */
                    case 'status' :
                        echo get_post_status_object(get_post_status($post_id))->label;
                        break;

                    /* get link to master post */
                    case 'master':

                        /* get master post id */
                        $masterID = get_post_meta($post_id, Post_Type::FIELD_MASTER_ID)[0];

                        if ($masterID) {
                            $masterPost = get_post($masterID);
                            $permalink = get_edit_post_link($masterID);
                            echo '<a href="' . $permalink . '">' . $masterPost->post_title . '</a>';
                        } else echo '';
                        break;
                }
            }
        }


        /**
         * Make columns sortable in the list display of post types in the wp admin area.
         *
         * @param array $columns Array containing the columns which are to be displayed.
         * @return mixed Return columns.
         *
         * TODO @2.0 Roadmap : as for now, can only sort acf fields... not post meta data...
         */
        function make_columns_sortable($columns)
        {
            /* get global parameter for post type */
            global $oes;
            global $post_type;

            /* bail if no configuration for this post type exists */
            if (!isset($oes->postTypes[$post_type])) return $columns;

            /* check for other columns to be displayed */
            foreach ($oes->postTypes[$post_type]['list_columns'] as $columnKey => $column) {
                if (!in_array($column['type'], ['acf_field', 'acf_field_select'])) continue;
                $columns[$columnKey] = $columnKey;
            }

            return $columns;
        }


        /**
         * Hook into query after query variable is defined but not yet fired to add sorting columns.
         *
         * @param $query String containing the query.
         *
         * TODO @2.0 Roadmap : as for now, can only sort fields of the post and not e.g. master title field
         */
        function sort_columns($query)
        {
            /* bail early if not main query or not part of wp admin */
            if (!is_admin() || !$query->is_main_query()) return $query;

            /* get global parameter for post type */
            global $oes;
            global $post_type;

            /* get column to be sorted */
            $column = $query->get('orderby');

            /* return query as it is if no configuration for this post type exists */
            if (!isset($oes->postTypes[$post_type]['list_columns'][$column])) return $query;

            /* order by column value */
            switch ($oes->postTypes[$post_type]['list_columns'][$column]['type']) {

                case 'acf_field' :
                case 'acf_field_select' :
                    $query->set('orderby', 'meta_value');
                    $query->set('meta_key', $column);
                    $query->set('meta_type', 'CHAR');
                    break;

                case 'master' :

                    global $post_id;

                    $masterID = get_post_meta($post_id, Post_Type::FIELD_MASTER_ID)[0];

                    if ($masterID) {
                        $metaKey = get_post($masterID)->post_title;
                    }

                    $query->set('orderby', 'meta_value_num');
                    $query->set('meta_key', Post_Type::FIELD_MASTER_ID);
                    break;
            }
        }


        /**
         * Fires after the main query vars have been parsed. Apply the selected column filter.
         *
         * @param $query
         *
         * TODO @2.0 Roadmap : as for now, can only sort acf fields... not post meta data...
         */
        function apply_column_filter($query)
        {
            /* get global parameter for post type */
            global $pagenow;
            global $post_type;
            global $oes;

            /* bail early if not main query or not part of wp admin or edit page */
            if (!is_admin() || !$query->is_main_query() || $pagenow != 'edit.php') return;

            /* check for column filter */
            if(isset($oes->postTypes[$post_type]['list_columns'])){
                foreach ($oes->postTypes[$post_type]['list_columns'] as $columnKey => $column) {

                    /* skip if filter option not set */
                    if (!isset($column['filter']) || !$column['filter']) continue;

                    /* skip if filter not set or all selected */
                    if (!isset($_GET[$columnKey]) || $_GET[$columnKey] == '' || $_GET[$columnKey] == '-1') continue;

                    if (!in_array($column['type'], ['acf_field', 'acf_field_select'])) continue;

                    /* modify query */
                    $query->query_vars['meta_key'] = $columnKey;
                    $query->query_vars['meta_value'] = $_GET[$columnKey];
                    $query->query_vars['meta_compare'] = '=';
                }
            }
        }


        /**
         * Add extra filter dropdown box to the list tables.
         *
         * @param string $post_type The post type that is being displayed.
         *
         */
        function add_column_filter($post_type)
        {
            /* get global parameter for post type */
            global $wpdb;
            global $oes;

            /* bail early if not part of wp admin or no configuration for this post type exists*/
            if (!is_admin() || !isset($oes->postTypes[$post_type])) return;

            /* add filter for each column for post type */
            if(isset($oes->postTypes[$post_type]['list_columns'])){
                foreach ($oes->postTypes[$post_type]['list_columns'] as $columnKey => $column) {

                    /* skip if filter option not set */
                    if (!isset($column['filter']) || !$column['filter']) continue;

                    /* get dropdown values from database */
                    $select = 'SELECT DISTINCT pm.meta_value FROM ' . $wpdb->postmeta . ' pm' .
                        ' LEFT JOIN ' . $wpdb->posts . ' p ON p.ID = pm.post_id' .
                        ' WHERE pm.meta_key = "' . $columnKey . '" ' .
                        ' AND p.post_status = "publish"' .
                        ' AND p.post_type = "%s"' .
                        ' ORDER BY "' . $columnKey . '"';

                    $query = $wpdb->prepare($select, $post_type);
                    $results = $wpdb->get_col($query);

                    /* skip if no options */
                    if (empty($results)) continue;

                    /* check for selected value, default is '-1' */
                    $selectedName = (isset($_GET[$columnKey]) && $_GET[$columnKey] != '') ? $_GET[$columnKey] : -1;

                    /* sort results alphabetically */
                    natcasesort($results);

                    /* prepare options */
                    $options = [];

                    /* add title */
                    $options[] = '<option value="-1">' . ($column['label'] ? 'All ' . $column['label'] : 'All') . '</option>';

                    /* loop through results */
                    foreach ($results as $result) :

                        /* create value */
                        $options[] = '<option value="' .
                            (empty($result) ? 'NULL' : esc_attr($result)) .
                            '"' .
                            (($result == $selectedName) ? ' selected' : '') .
                            '>' .
                            (empty($result) ? '<span style="font-style: italic">(empty)</span>' : $result) . '</option>';

                    endforeach;

                    /* create html box */
                    echo '<select id="' . $columnKey . '" name="' . $columnKey . '">';
                    echo join("\n", $options);
                    echo '</select>';

                }
            }
        }


        /**
         * Fires after the main query vars have been parsed. Apply the selected column filter.
         *
         * @param $query
         */
        function apply_column_filter_any_post_field($query)
        {

            /* get global parameter for post type */
            global $pagenow;

            /* bail early if not main query or not part of wp admin or edit page */
            if (!is_admin() || !$query->is_main_query() || $pagenow != 'edit.php') return;

            /* skip if filter option not set */
            if (!isset($_GET['custom_fields']) || $_GET['custom_fields'] == '' || $_GET['custom_fields'] == '-1') return;

            /* modify query */
            $query->query_vars['meta_key'] = $_GET['custom_fields'];

            if (isset($_GET['custom_fields-value']) && $_GET['custom_fields-value'] != '')
                $query->query_vars['meta_value'] = $_GET['custom_fields-value'];

        }


        /**
         * Add extra filter dropdown box to the list tables.
         *
         * @param string $post_type The post type that is being displayed.
         *
         */
        function add_column_filter_any_post_field()
        {
            /* get global parameter for post type */
            global $post_type;

            /* bail early if not part of wp admin */
            if (!is_admin()) return;

            /* get all fields for post type */
            $allFields = get_all_post_type_fields($post_type, false);

            /* display html option box */
            ?>
            <select name="custom_fields">
                <option value=""><?php _e('Filter By Custom Fields', 'oes'); ?></option>
                <?php

                /* loop through found fields */
                foreach ($allFields as $fieldName => $field) {

                    /* add option to dropdown */
                    printf
                    (
                        '<option value="%1$s"%2$s>%3$s</option>',
                        $fieldName,
                        $fieldName == oes_isset_GET('custom_fields') ? ' selected="selected"' : '',
                        $field['label']
                    );
                }
                ?>
            </select>
            <input type="text" name="custom_fields-value" value="<?php echo oes_isset_GET('custom_fields-value'); ?>"
                   placeholder="Value"/>
            <?php
        }
    }

endif;