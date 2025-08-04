<?php

if (!defined('ABSPATH')) exit;// Exit if accessed directly.

if (class_exists('\OES\Timeline\Event')) :

    /**
     * Class Demo_Timeline_Event
     *
     * Extends the \OES\Timeline\Event class to implement custom label handling.
     */
    class Demo_Timeline_Event extends \OES\Timeline\Event
    {

        /** @inheritDoc */
        protected function set_start_label(string $startLabel): void
        {
            $this->start_label = $this->get_clean_label($startLabel, $this->start);
        }

        /** @inheritDoc */
        protected function set_end_label(string $endLabel): void
        {
            $this->end_label = $this->get_clean_label($endLabel, $this->end);
        }

        /**
         * Return a cleaned label. If the label is empty, format the date as a fallback.
         *
         * @param string $label The label to clean.
         * @param string $date The fallback date.
         *
         * @return string The cleaned label.
         */
        protected function get_clean_label(string $label, string $date): string
        {
            return empty($label) ? str_replace('/', '.', $date) : $label;
        }
    }

endif;
