<?php

/**
 * Functions for html representations.
 */

/**
 * Get html anchor tag representation of link.
 *
 * @param string $title A string containing the anchor title.
 * @param string $permalink A string containing the permalink.
 * @param boolean|string $class Optional string containing the anchor css class.
 * @param boolean|string $id Optional string containing the anchor css id.
 * @param boolean|string $target Optional string containing the target parameter.
 * @return string Return string containing a html anchor tag.
 */
function oes_get_html_anchor($title, $permalink = '', $id = false, $class = false, $target = false){

    $classText = $class ? ' class="' . $class . '"' : '';
    $targetText = $target ? ' target="' . $target . '"' : '';
    $idText = $id ? ' id="' . $id . '"' : '';

    return sprintf('<a href="%1$s" %2$s %3$s %4$s>%5$s</a>',
            $permalink,
            $classText,
            $idText,
            $targetText,
            $title);
}


/**
 * Get html img tag representation of image.
 *
 * @param string $src A string containing the image source.
 * @param boolean|string $alt Optional string containing the image alt identifier.
 * @param boolean|string $id Optional string containing the image css id.
 * @param boolean|string $class Optional string containing the image css class.
 * @return string Return string containing a html img tag.
 */
function oes_get_html_img($src, $alt = false, $id = false, $class = false)
{
    $altText = $alt ? ' alt="' . $alt . '"' : '';
    $idText = $id ? ' id="' . $id . '"' : '';
    $classText = $class ? ' class="' . $alt . '"' : ' class="img-fluid mb-3 img-thumbnail mr-3"';

    return sprintf('<img src="%1$s" %2$s %3$s %4$s>',
            $src,
            $altText,
            $idText,
            $classText
        );
}


/**
 * Get html ul representation of list.
 *
 * @param array $listItems An array containing the list items.
 * @param boolean|string $id Optional string containing the list css id.
 * @param boolean|string $class Optional string containing the list css class.
 * @return string Return string containing a html ul tag.
 */
function oes_get_html_array_list($listItems, $id = false, $class = false)
{
    /* open list */
    $returnString = sprintf('<ul %1$s %2$s>',
        $id ? ' id="' . $id . '"' : '',
        $class ? ' class="' . $class . '"' : ''
    );

    /* loop through list items */
    foreach($listItems as $item) $returnString .= '<li>' . $item . '</li>';

    /* close list */
    $returnString .= '</ul>';

    return $returnString;
}


/**
 * Replace umlaute in string for html display.
 *
 * @param string $input A string where umlaute should be replaced.
 * @return string Return string with replaced umlaute.
 */
function oes_replace_umlaute_for_html($input)
{
    $replacedStringParams = [
        "ß" => "&szlig;",
        "ä" => "&auml;",
        "ö" => "&ouml;",
        "ü" => "&uuml;",
        "Ä" => "&Auml;",
        "Ö" => "&Ouml;",
        "Ü" => "&Uuml;",
    ];

    return str_replace(array_keys($replacedStringParams), array_values($replacedStringParams), $input);
}