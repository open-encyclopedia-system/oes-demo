<?php

/**
 * Containing functions for text processing.
 */


/**
 * Returns true if given string starts with needle.
 *
 * @param string $string A string containing the input string.
 * @param string $needle A string containing the needle.
 * @return bool Returns true if the input string does start with needle.
 */
function oes_starts_with($string, $needle)
{
    $length = strlen($needle);
    return (substr($string, 0, $length) === $needle);
}


/**
 * Returns true if given string ends with needle.
 *
 * @param string $string A string containing the input string.
 * @param string $needle A string containing the needle.
 * @return bool Returns true if the input string does end with needle.
 */
function oes_ends_with($string, $needle)
{
    $length = strlen($needle);
    if (!$length) return true;
    return substr($string, -$length) === $needle;
}


/**
 * Cast input into an array and returns array.
 *
 * @param mixed $input Input that is to be cast to array.
 * @param bool $ignoreEmpty Optional boolean if empty input should be ignored.
 * @return array|null[] Returns array or empty array.
 */
function oes_cast_to_array($input, $ignoreEmpty = false)
{
    /* $input is already array */
    if (is_array($input)) return $input;

    /* $input is missing or null */
    if (!isset($input) || is_null($input)) return [];

    /* ignore $input empty array */
    if ($ignoreEmpty && empty($input)) return [];

    return [$input];
}


/**
 * Cast input into string. This function can be used to turn database values into strings for output formats.
 *
 * @param mixed $input Input that is to be cast to string.
 * @param bool $ignoreEmpty Optional boolean if empty input should be ignored.
 * @return string Returns string.
 */
function oes_cast_to_string($input = null, $ignoreEmpty = false)
{

    /* $input is already string */
    if (is_string($input)) return $input;

    /* $input is missing or null */
    if (!isset($input) || is_null($input)) return '';

    /* ignore $input empty array */
    if ($ignoreEmpty && empty($input)) {
        return '';
    }

    switch (gettype($input)) {

        case 'boolean' :
            return $input ? 'true' : 'false';

        case 'integer' :
        case 'double' :
            return strval($input);

        case 'array' :
            $arrayString = '';
            foreach ($input as $entry) {
                if (gettype($entry) == 'array') $arrayString .= '[' . oes_array_to_string_flat($entry) . ']';
                else $arrayString .= oes_cast_to_string($entry);
                $arrayString .= ';';
            }
            return '[' . substr($arrayString, 0, -1) . ']';

        case 'object' :
        case 'resource' :
        case 'NULL' :
        case 'unknown type' :
        default :
            //TODO @2.0 Roadmap : implement more types
            return '';
    }
}


/**
 * Cast input into string without type differentiation.
 *
 * @param mixed $input Input that is to be cast to string.
 * @return string Returns string.
 */
function oes_array_to_string_flat($input = null)
{

    /* $input is not an array */
    if (!is_array($input)) return '';

    /* $input is missing or null */
    if (!isset($input) || is_null($input)) return '';

    $returnString = '';

    foreach ($input as $entry) {
        if (is_array($entry)) $returnString .= '[' . oes_array_to_string_flat($entry) . ']';
        else $returnString .= oes_cast_to_string($entry);
        $returnString .= ',';
    }

    return substr($returnString, 0, -1);
}


/**
 * Replace all double spaces in string to \t tabs. Replace spaces before brackets for arrays.
 *
 * @param string $input A string where double spaces are to be replaced.
 * @return string|string[] Return string without double spaces.
 */
function oes_double_space_to_tab($input)
{

    $replaceParams = [
        "  " => "\t",
        "'!!__(!!\'" => "__('",
        "!!\', !!\'" => "', '",
        "!!\')!!'" => "')",
        "array (" => "array("
    ];

    return str_replace(array_keys($replaceParams), array_values($replaceParams), $input);
}


/**
 * Replace double quote by single quote in string.
 *
 * @param string $value A string where double quotes are to be replaced.
 * @param $key
 */
function oes_replace_double_quote(&$value, $key)
{
    $value = str_replace('"', "'", $value);
}


/**
 * Escape encoding for csv output.
 *
 * @param string $input A string containing the text input.
 * @param string $separator Optional string containing the separator. Default is ';'.
 * @param string $inputEncoding Optional string containing the encoding of the input string. Default is 'utf-8'.
 * @param string $encoding Optional string containing the encoding. Default is 'windows-1251//TRANSLIT'.
 * @return false|mixed|string Returns encoded string.
 *
 * TODO @2.0 Roadmap : fix encoding problem for excel csv with Windows-1251.
 *
 * If you append the string //TRANSLIT to to_encoding transliteration is activated. This means that when a character
 * can't be represented in the target charset, it can be approximated through one or several similarly looking
 * characters. If you append the string //IGNORE, characters that cannot be represented in the target charset are
 * silently discarded. Otherwise, E_NOTICE is generated and the function will return false.
 */
function oes_csv_escape_string($input, $separator = ';', $inputEncoding = 'utf-8', $encoding = "windows-1251")
{
    $returnString = $input;

    if (preg_match('/[\r\n"' . preg_quote($separator, '/') . ']/', $returnString)) {
        return '"' . str_replace('"', '""', $returnString) . '"';
    } else return $returnString;
}


/**
 * Modify wp_text_diff.
 * Displays a readable html representation of the difference between two strings.
 * See https://developer.wordpress.org/reference/functions/wp_text_diff/.
 *
 * @param string $leftString String containing the "old" version.
 * @param string $rightString String containing the "new" version.
 * @param array|null $args Array containing further parameters ('title', 'title_left', 'title_right', 'show_split_view')
 * @param string $tableID String containing the table id for the html representation.
 * @return string
 */
function oes_wp_text_diff($leftString, $rightString, $args = null, $tableID = 'diff-table')
{
    /* add default values */
    $defaults = [
        'title' => '',
        'title_left' => '',
        'title_right' => '',
        'show_split_view' => true,
    ];
    $args = wp_parse_args($args, $defaults);

    /* include wordpress class */
    if (!class_exists('WP_Text_Diff_Renderer_Table', false)) require ABSPATH . WPINC . '/wp-diff.php';

    /* prepare input by normalizing EOL characters and strip duplicate whitespaces. */
    $leftString = normalize_whitespace($leftString);
    $rightString = normalize_whitespace($rightString);

    /* prepare input by splitting in arrays */
    $leftLines = explode("\n", $leftString);
    $rightLines = explode("\n", $rightString);

    /* get text differences (returns table with differences) */
    $textDiff = new Text_Diff($leftLines, $rightLines);
    $renderer = new WP_Text_Diff_Renderer_Table($args);
    $diff = $renderer->render($textDiff);

    /* exit if no result */
    if (!$diff) return '';

    /* open table */
    $returnString = '<table class="text-differences" id="' . $tableID . '">';

    /* add columns */
    if (!empty($args['show_split_view'])) {
        $returnString .= '<col class="split-left"/><col class="split-middle"/><col class="split-right"/>';
    } else {
        $returnString .= '<col class="content"/>';
    }

    /* add table header and titles */
    if ($args['title'] || $args['title_left'] || $args['title_right']) $returnString .= '<thead>';
    if ($args['title']) $returnString .= '<tr class="diff-title"><th colspan="3">' . $args['title'] . '</th></tr>';

    if ($args['title_left'] || $args['title_right']) {
        $returnString .= '<tr class="diff-sub-title">';
        $returnString .= '<th>' . $args['title_left'] . '</th>';
        $returnString .= '<td></td>';
        $returnString .= '<th>' . $args['title_right'] . '</th>';
        $returnString .= '</tr>';
    }
    if ($args['title'] || $args['title_left'] || $args['title_right']) $returnString .= '</thead>';

    /* modify diff */
    //TODO @2.0 Roadmap : this is a workaround! Change in WP_Text_Diff_Renderer_Table?
    $diffModify = str_replace("<span class='screen-reader-text'>Unchanged: </span>", '', $diff);
    $diffModify = str_replace("<td>&nbsp;</td><td>&nbsp;</td><td class='diff-addedline'>",
        "<td>&nbsp;</td><td>&nbsp;</td><td class='diff-addedline-new'>", $diffModify);
    $diffModify = str_replace("</td><td>&nbsp;</td><td>&nbsp;</td>",
        "</td><td>&nbsp;</td><td class='diff-addedline-removed'></td>", $diffModify);

    /* evaluate html tags */
    $diffModify = str_replace('&quot;', '"', $diffModify);
    $diffModify = str_replace('&lt;', '<', $diffModify);
    $diffModify = str_replace('&gt;', '>', $diffModify);

    /* main table */
    $returnString .= '<tbody>' . $diffModify . '</tbody>';

    /* close table */
    $returnString .= '</table>';

    return $returnString;
}


/**
 * Scan string for search term and return string with highlighted search term for html display.
 *
 * TODO @2.0 Roadmap : validate needle and content.
 * TODO @2.0 Roadmap : search for multiple terms or sentences.
 * TODO @2.0 Roadmap : padding options for search result.
 *
 * @param string $needle A string containing the search term.
 * @param string $content A string containing the content to be searched.
 * @param array $args An array containing additional search parameter. Valid parameter are:
 *  'max-string-length'     : An integer defining the maximum string length for the result text.
 *  'case-sensitive'        : A boolean identifying if the search is case sensitive.
 *
 * @return array Returns an array with highlighted search results.
 */
function oes_get_highlighted_search($needle, $content, $args = [])
{

    /* set default values */
    $args = array_merge(['max-string-length' => 100, 'case-sensitive' => false], $args);

    $returnArrayString = [];

    /* get all keys --------------------------------------------------------------------------------------------------*/
    $keys = [$needle];

    /* strip content of tags and replace line breaks */
    $content = strip_tags($content);
    $content = preg_replace('/\n/', ' ', $content);

    /* explode content into sentences */
    $sentences = explode('. ', $content);

    /* loop through sentences */
    if (count($sentences) > 1 || !empty($sentences[0])) {
        foreach ($sentences as $sentenceKey => $sentence) {

            $position = null;
            if (count($sentences) == 1) $position = 'single';
            elseif ($sentenceKey == 0) $position = 'first';
            elseif ($sentenceKey == count($sentences) - 1) $position = 'last';

            /* loop through keys */
            foreach ($keys as $key) {

                if (!$args['case-sensitive']) {
                    $key = strtolower($key);
                    $searchSentence = strtolower($sentence);
                } else $searchSentence = $sentence;

                /* check if occurrence in sentence -------------------------------------------------------------------*/
                if (strpos($searchSentence, $key) !== false) {

                    /* check length */
                    if (strlen($sentence) < $args['max-string-length']) {

                        /* prepare return string */
                        $returnString = $sentence;

                        /* add next sentences */
                        $restCharacters = $args['max-string-length'] - strlen($sentence);

                        /* add whole sentences */
                        $sentenceKeyNext = $sentenceKey + 1;
                        while (($sentenceKeyNext < count($sentences))
                            && (strlen($sentences[$sentenceKeyNext]) + 2 < $restCharacters)) {
                            $returnString .= '. ' . $sentences[$sentenceKeyNext];
                            $restCharacters -= (strlen($sentences[$sentenceKeyNext]) + 2);
                            $sentenceKeyNext++;
                        }

                        /* add stop */
                        if (!$position == 'last') {
                            $returnString .= '.';
                            $restCharacters++;
                        }

                        /* add parts of next sentences */
                        if (($sentenceKeyNext < count($sentences)) && $restCharacters > 0) {
                            $sentenceParts = explode(' ', $sentences[$sentenceKeyNext]);
                            $sentencePartKey = 0;
                            while (($sentencePartKey < count($sentenceParts))
                                && (strlen($sentenceParts[$sentencePartKey]) + 1 < $restCharacters + 1)) {
                                $returnString .= ' ' . $sentenceParts[$sentencePartKey];
                                $restCharacters -= (strlen($sentenceParts[$sentencePartKey]) + 1);
                                $sentencePartKey++;
                            }
                        }

                        $highlightedSentence = preg_replace('/(' . $key . ')/iu',
                            '<p class="search-excerpt">\0</p>', $returnString);
                        $returnArrayString[] = ['sentence' => $highlightedSentence, 'position' => $position];
                    }
                    else {
                        $highlightedSentence = preg_replace('/(' . $key . ')/iu',
                            '<p class="search-excerpt">\0</p>', $sentence . '.');
                        $returnArrayString[] = ['sentence' => $highlightedSentence, 'position' => $position];
                    }
                }
            }
        }
    }

    return $returnArrayString;
}