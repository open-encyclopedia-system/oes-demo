<?php

use OES\ACF as ACF;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Project_Config')) :

    /**
     * Interface for OES DEMO, includes project specific processing.
     */
    class OES_Project_Config extends OES_Includes_Project
    {

        /* post type */
        const POST_TYPE_GLOSSARY = 'oes_demo_glossary';
        const POST_TYPE_ARTICLE = 'oes_demo_article';
        const POST_TYPE_CONTRIBUTOR = 'oes_demo_contributor';
        const POST_TYPE_BIBLIOGRAPHY = 'oes_demo_biblio';
        const POST_TYPE_INDEX_PERSON = 'oes_index_person';
        const POST_TYPE_INDEX_INSTITUTE = 'oes_index_institute';
        const POST_TYPE_INDEX_PLACE = 'oes_index_place';
        const POST_TYPE_INDEX_SUBJECT = 'oes_index_subject';
        const POST_TYPE_INDEX_TIME = 'oes_index_time';
        const POST_TYPE_INDEX_ANY = 'oes_demo_index';

        const POST_TYPE_ALL = [
            self::POST_TYPE_ARTICLE,
            self::POST_TYPE_CONTRIBUTOR,
            self::POST_TYPE_BIBLIOGRAPHY,
            self::POST_TYPE_GLOSSARY,
            self::POST_TYPE_INDEX_PERSON,
            self::POST_TYPE_INDEX_INSTITUTE,
            self::POST_TYPE_INDEX_PLACE,
            self::POST_TYPE_INDEX_SUBJECT,
            self::POST_TYPE_INDEX_TIME
        ];

        const POST_TYPE_INDEX = [
            self::POST_TYPE_INDEX_PERSON,
            self::POST_TYPE_INDEX_PLACE,
            self::POST_TYPE_INDEX_SUBJECT,
            self::POST_TYPE_INDEX_TIME,
            self::POST_TYPE_INDEX_INSTITUTE
        ];

        /* used for frontend */
        const POST_TYPE_IDENTIFIER = [
            self::POST_TYPE_GLOSSARY => 'glossary',
            self::POST_TYPE_ARTICLE => 'article',
            self::POST_TYPE_CONTRIBUTOR => 'contributor',
            self::POST_TYPE_BIBLIOGRAPHY => 'bibliography',
            self::POST_TYPE_INDEX_PERSON => 'person',
            self::POST_TYPE_INDEX_PLACE => 'place',
            self::POST_TYPE_INDEX_SUBJECT => 'subject',
            self::POST_TYPE_INDEX_TIME => 'time',
            self::POST_TYPE_INDEX_INSTITUTE => 'institution'
        ];

        const INDEX_AS_TAXONOMY = false;

        const INDEX_PERSON = 'oes_demo_index_person';
        const INDEX_PLACE = 'oes_demo_index_place';
        const INDEX_SUBJECT = 'oes_demo_index_subject';
        const INDEX_TIME = 'oes_demo_index_time';
        const INDEX_INSTITUTE = 'oes_demo_index_institute';

        const INDEX_ALL = [
            self::INDEX_PERSON,
            self::INDEX_PLACE,
            self::INDEX_SUBJECT,
            self::INDEX_TIME,
            self::INDEX_INSTITUTE
        ];


        /* post type values ------------------------------------------------------------------------------------------*/

        /* status */
        const STATUS_NEW = 'new';
        const STATUS_IN_PROCESS = 'in process';
        const STATUS_IN_REVIEW = 'in review';
        const STATUS_READY = 'ready for publication';
        const STATUS_PUBLISHED = 'published';
        const STATUS_LOCKED = 'locked';
        const STATUS_DELETED = 'deleted';
        const SELECT_EDITING_STATUS = [
            self::STATUS_NEW => self::STATUS_NEW,
            self::STATUS_IN_PROCESS => self::STATUS_IN_PROCESS,
            self::STATUS_IN_REVIEW => self::STATUS_IN_REVIEW,
            self::STATUS_READY => self::STATUS_READY,
            self::STATUS_PUBLISHED => self::STATUS_PUBLISHED,
            self::STATUS_LOCKED => self::STATUS_LOCKED,
            self::STATUS_DELETED => self::STATUS_DELETED
        ];

        const COUNTRIES = [
            'AF' => 'Afghanistan',
            'AX' => 'Åland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua & Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AC' => 'Ascension Island',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia & Herzegovina',
            'BW' => 'Botswana',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'IC' => 'Canary Islands',
            'CV' => 'Cape Verde',
            'BQ' => 'Caribbean Netherlands',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'EA' => 'Ceuta & Melilla',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo - Brazzaville',
            'CD' => 'Congo - Kinshasa',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Côte d’Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CW' => 'Curaçao',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DG' => 'Diego Garcia',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong SAR China',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'XK' => 'Kosovo',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macau SAR China',
            'MK' => 'Macedonia',
            'MK@1' => 'Macedonia, The Former Yugoslav Republic Of',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar (Burma)',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territories',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'São Tomé & Príncipe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SX' => 'Sint Maarten',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia & South Sandwich Islands',
            'KR' => 'South Korea',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'BL' => 'St. Barthélemy',
            'SH' => 'St. Helena',
            'KN' => 'St. Kitts & Nevis',
            'LC' => 'St. Lucia',
            'MF' => 'St. Martin',
            'PM' => 'St. Pierre & Miquelon',
            'VC' => 'St. Vincent & Grenadines',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard & Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad & Tobago',
            'TA' => 'Tristan da Cunha',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks & Caicos Islands',
            'TV' => 'Tuvalu',
            'UM' => 'U.S. Outlying Islands',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WF' => 'Wallis & Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ];


        /**
         * Hook into data processing before saving to modify field data.
         *
         * @param int $post_id The current post id.
         * @param WP_Post $post The current post.
         */
        function save_post_data_processing($post_id, $post)
        {

            /* If citation field is empty than compute it from data. */
            if($post->post_type == 'oes_demo_biblio' && empty(ACF\get_acf_field('oes_demo_bibliography_main'))) {
                $this->data_processing_compute_citation($post_id, $post);
            }

        }


        /**
         * Compute citation for bibliographic entry.
         *
         * @param int $post_id The current post id.
         * @param WP_Post $post The current post.
         * @return void
         */
        function data_processing_compute_citation($post_id, $post)
        {

            /* author(s) */
            $newValue = ACF\get_acf_field('oes_demo_bibliography_details_author') ?
                ACF\get_acf_field('oes_demo_bibliography_details_author') . ' ' :
                '[author(s) missing] ';

            /* year */
            $newValue .= ACF\get_acf_field('oes_demo_bibliography_details_date') ?
                '(' . date('Y', strtotime(ACF\get_acf_field('oes_demo_bibliography_details_date'))) . '). ' :
                '[publish date missing]';

            /* title */
            $newValue .= ACF\get_acf_field('oes_demo_bibliography_basic_title') ?
                ACF\get_acf_field('oes_demo_bibliography_basic_title') :
                '[title missing]';

            /* publisher */
            if(!empty(ACF\get_acf_field('oes_demo_bibliography_details_publisher'))){
                $newValue .= ', ' . ACF\get_acf_field('oes_demo_bibliography_details_publisher');
            }

            /* place */
            if(!empty(ACF\get_acf_field('oes_demo_bibliography_details_place'))){
                $newValue .= ', ' . ACF\get_acf_field('oes_demo_bibliography_details_place');
            }

            /* full date */
            if(!empty(ACF\get_acf_field('oes_demo_bibliography_details_date'))){
                $newValue .= ', ' . date('d.m.Y', strtotime(ACF\get_acf_field('oes_demo_bibliography_details_date')));
            }

            /* url */
            if(!empty(ACF\get_acf_field('oes_demo_bibliography_details_url'))){
                $newValue .= ', ' . ACF\get_acf_field('oes_demo_bibliography_details_url');
            }

            /* accessed */
            if(!empty(ACF\get_acf_field('oes_demo_bibliography_details_accessed'))){
                $newValue .= ', ' . date('d.m.Y H:i:s', ACF\get_acf_field('oes_demo_bibliography_details_accessed'));
            }

            update_field('oes_demo_bibliography_main', $newValue, $post_id);
        }

    }
endif;