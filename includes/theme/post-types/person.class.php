<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Person
 */
if (class_exists('OES_Post')) :

    class Demo_Person extends OES_Post
    {

        //Overwrite parent
        function modify_metadata_array(array $metaFields): array
        {
            /* add biographical data */
            $birthValue = $this->fields['field_demo_person__birth']['value'];
            $birthPlaceValue = $this->fields['field_demo_person__birth_place']['value'];
            $deathValue = $this->fields['field_demo_person__death']['value'];
            $deathPlaceValue = $this->fields['field_demo_person__death_place']['value'];
            if (!empty($birthValue) || !empty($birthPlaceValue) || !empty($deathValue) || !empty($deathPlaceValue)) {

                $biographicalData = [];

                /* birth */
                if (!empty($birthValue) || !empty($birthPlaceValue)) {

                    $birth = '';
                    if (!empty($birthValue)) $birth .= strftime("%d.%m.%Y", strtotime($birthValue));

                    /* place of birth */
                    if (!empty($birthPlaceValue)) $birth .= (!empty($birth) ? ', ' : '') . $birthPlaceValue;

                    $biographicalData[] = $birth;
                }

                /* death */
                if (!empty($deathValue) || !empty($deathPlaceValue)) {

                    $death = '';
                    if (!empty($deathValue)) $death .= strftime("%d.%m.%Y", strtotime($deathValue));

                    /* place of death */
                    if (!empty($deathPlaceValue)) $death .= (!empty($death) ? ', ' : '') . $deathPlaceValue;

                    $biographicalData[] = $death;
                }

                if (!empty($biographicalData))
                    $metaFields[] = [
                        'label' => __('Biographical Data'),
                        'value' => implode(' — ', $biographicalData)
                    ];
            }
            return $metaFields;
        }


        // Overwrite parent function
        function modify_metadata($field, $loop): array
        {
            /* replace gnd ID with shortcode */
            if ($field['key'] == 'field_demo_person__gnd_id')
                $field['value-display'] = sprintf('<a href="%s" target="_blank">%s</a>%s',
                    'https://d-nb.info/gnd/' . $field['value'],
                    'https://d-nb.info/gnd/' . $field['value'],
                    '[gndlink id="' . $field['value'] . '" label=""]'
                );
            return $field;
        }
    }
endif;