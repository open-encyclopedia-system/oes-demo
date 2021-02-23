<?php

namespace OES\Option;


use WP_Error;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Table')) :

    /**
     * Class Table
     *
     * Generating a table object.
     */
    class Table
    {

        /**
         * @var array Array containing the table data
         *
         * $table = [   'columns'   => [ 'row' => 'x'       'header1' => 'Header 1',    'header2' => 'Header 2'],
         *              'row1'      => [ 'row' => 'Row 1'   'header1' => 'Value 1 1',   'header2' => 'Value 1 2'],
         *              'row2'      => [ 'row' => 'Row 2'   'header1' => 'Value 2 1',   'header2' => 'Value 2 2'],
         */
        var $table = [];

        /** @var WP_Error|null Array storing errors. */
        var $errors = null;


        /**
         * OES_Includes_Admin_Table constructor.
         */
        function __construct()
        {
            /* initialize table */
            $this->table['columns'] = ['row' => ''];
            $this->errors = new WP_Error();
        }


        /**
         * Return class variable table.
         *
         * @return array Return class variable table.
         */
        function get_table()
        {
            return $this->table;
        }


        /**
         * Return all table columns.
         *
         * @return mixed Return all table columns.
         */
        function get_all_columns()
        {
            return $this->table['columns'];
        }


        /**
         * Return all table rows.
         *
         * @return array Return all table rows.
         */
        function get_all_rows()
        {
            return array_column($this->table, 'row');
        }


        /**
         * Get column key and label from column identifier.
         * Return false if key does not exist.
         * Return null if key is forbidden.
         *
         * @param string $keyInput A string containing the column identifier.
         * @return mixed Return column key and label, false if key does not exist and null if key is forbidden.
         */
        function get_column_key_and_label($keyInput)
        {
            $allColumns = $this->get_all_columns();
            $key = isset($allColumns[$keyInput]) ? $keyInput : false;
            if (!$key) {
                $sanitizedKey = $this->sanitize_key($keyInput);
                $key = isset($allColumns[$sanitizedKey]) ? $sanitizedKey : false;
            }

            /* exclude 'row' */
            if ($key) {
                if ($key == 'row') {
                    $this->errors->add('forbidden_column_name', 'Column name can not be "row".');
                    return null;
                }
            }

            /* return key and label */
            $return = false;

            if ($key) {
                $return['key'] = $key;
                $return['label'] = $this->table['columns'][$key];
            }

            return $return;
        }


        /**
         * Add column to table.
         *
         * @param string $keyInput A String containing column identifier.
         * @return mixed Return column key. Return false if key does not exist and null if key is forbidden.
         */
        function add_column($keyInput)
        {
            /* check if key already exists */
            $keyExists = $this->get_column_key_and_label($keyInput);
            if (isset($keyExists['key'])) {
                $key = $keyExists['key'];
            } else {
                $key = $this->sanitize_key($keyInput);
                $this->table['columns'][$key] = $keyInput;
            }

            return $key;
        }


        /**
         * Get row key and label from row identifier.
         * Return false if key does not exist.
         * Return null if key is forbidden.
         *
         * @param string $keyInput A string containing the row identifier.
         * @return mixed Return row key and label, false if key does not exist and null if key is forbidden.
         */
        function get_row_key_and_label($keyInput)
        {
            /* check if key exists as key */
            $key = isset($this->table[$keyInput]) ? $keyInput : false;
            if (!$key) {
                $sanitizedKey = $this->sanitize_key($keyInput);
                $key = isset($this->table[$sanitizedKey]) ? $sanitizedKey : false;
            }

            /* exclude 'columns' */
            if ($key) {
                if ($key == 'columns') {
                    $this->errors->add('forbidden_row_name', 'Row name can not be "columns".');
                    return null;
                }
            }

            /* check if key exists in column 'row' */
            if (!$key) {
                $allRows = $this->get_all_rows();
                $key = array_search($key, $allRows);
            }

            /* return key and label */
            $return = false;
            if ($key) {
                $return['key'] = $key;
                $return['label'] = $this->table[$key]['row'];
            }

            return $return;
        }


        /**
         * Add row to table.
         *
         * @param string $rowKeyInput A String containing row identifier.
         * @param array $fields Optional array containing the fields for this row.
         * @return mixed Return row key. Return false if key does not exist and null if key is forbidden.
         */
        function add_row($rowKeyInput, $fields = [])
        {
            /* check if key already exists */
            $rowKeyExists = $this->get_row_key_and_label($rowKeyInput);
            if (isset($rowKeyExists['rowKey'])) {
                $rowKey = $rowKeyExists['rowKey'];
            } else {
                /* add row */
                $rowKey = $this->sanitize_key($rowKeyInput);
                $this->table[$rowKey]['row'] = $rowKeyInput;
            }

            /* add fields */
            if (!empty($fields)) {
                $this->add_fields_array($rowKey, $fields);
            }

            return $rowKey;
        }


        /**
         * Add row with fields to table.
         *
         * @param string $rowKey A string containing the row identifier.
         * @param array $fields An array containing the fields for this row.
         */
        function add_fields_array($rowKey, $fields)
        {
            foreach ($fields as $columnKey => $value) {
                $this->add_field($rowKey, $columnKey, $value);
            }
        }


        /**
         * Add field to table
         *
         * @param string $rowKey A string containing the row identifier.
         * @param string $columnKey A string containing the column identifier.
         * @param string $value A string containing the field value.
         * @return boolean Return if field could be added to table.
         */
        function add_field($rowKey, $columnKey, $value)
        {
            /* check if row exists and add row if it doesn't */
            $existingRow = $this->get_row_key_and_label($rowKey);

            /* bail early if key is forbidden */
            if (is_null($existingRow)) {
                return false;
            }

            /* add row if necessary */
            if (isset($existingRow['key'])) {
                $rowKey = $existingRow['key'];
            } else {
                $rowKey = $this->add_row($rowKey);
            }

            /* check if column exists and add column if it doesn't */
            $existingColumn = $this->get_column_key_and_label($columnKey);
            if (isset($existingColumn['key'])) {
                $columnKey = $existingColumn['key'];
            } else {
                $columnKey = $this->add_column($columnKey);
            }

            /* add field */
            $this->table[$rowKey][$columnKey] = $value;

            return true;
        }


        /**
         * Display the table and return errors
         *
         * @param string|null $tableClass Optional String containing the table class.
         * @param string|null $tableID Optional String containing the table class.
         * @param string|null $tableCaption Optional String containing the table class.
         * @return WP_Error|null Return array with errors that occurred during table creation.
         */
        function html($tableClass = null, $tableID = null, $tableCaption = null)
        {
            echo '<table';
            if ($tableClass) echo ' class="' . $tableClass . '"';
            if ($tableID) echo ' id="' . $tableID . '"';
            echo ' role="presentation">';
            if ($tableCaption) echo '<caption>' . $tableCaption . '</caption>';

            /* thead = column headings */
            $columns = $this->get_all_columns();
            if (count($columns) > 1) {
                echo '<thead><tr>';
                foreach ($columns as $columnKey => $column) {
                    if ($columnKey == 'row') {
                        echo '<th scope="row"></th>';
                    } else {
                        echo '<th scope="row" id="column-header">' . $column . '</th>';
                    }
                }
                echo '</tr></thead>';
            }

            /* tbody = rows */
            echo '<tbody>';
            foreach ($this->table as $rowKey => $row) {
                if ($rowKey != 'columns') {

                    echo '<tr>';

                    foreach ($columns as $columnKey => $column) {

                        /* field does not exists */
                        if (!isset($row[$columnKey])) {
                            echo '<td></td>';
                        } else {

                            $value = $row[$columnKey];

                            /* field contains callback function */
                            if (is_array($value)) {

                                $callback = isset($value['callback']) ? $value['callback'] : null;
                                $args = isset($value['args']) ? $value['args'] : null;
                                if ($callback) {
                                    echo '<td>';
                                    call_user_func($callback, $args);
                                    echo '</td>';
                                } else {
                                    echo '<td>Unidentified array with callback function.</td>';
                                }
                            } /* field is row label */
                            elseif ($columnKey == 'row') {
                                echo '<th scope="row" class="row-label">' . $row[$columnKey] . '</th>';
                            } /* display field */
                            else {
                                echo '<td>' . $row[$columnKey] . '</td>';
                            }
                        }

                    }

                    echo '</tr>';
                }
            }
            echo '</tbody>';

            /* tfoot unused */
            echo '<tfoot></tfoot>';

            echo '</table>';

            return $this->errors;

        }


        /**
         * Sanitize identifier for table
         *
         * @param string $key A string containing the identifier.
         * @return string Returns sanitized key.
         */
        function sanitize_key($key)
        {
            $return = str_replace(' ', '_', $key);
            $return = strtolower($return);
            return preg_replace('/[^A-Za-z0-9\_]/', '', $return);
        }

    }
endif;
