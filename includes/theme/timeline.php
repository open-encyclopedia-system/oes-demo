<?php

/**
 * Filter the  date.
 *
 * @param string $label The value.
 * @param string $date The date value.
 * @return string Returns the modified date.
 */
function oes_demo__timeline_date(string $label, string $date): string
{
    return empty($label) ?
        str_replace('/', '.', $date):
        $label;
}