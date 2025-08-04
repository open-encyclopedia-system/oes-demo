<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Demo_Person
 */
if (class_exists('Demo_Post')) :

    class Demo_Person extends Demo_Post
    {
        /** @inheritdoc */
        function modify_metadata_array(array $metaFields): array
        {
            global $oes_language;

            // Add biographical data
            $birthValue = $this->fields['field_demo_person__birth']['value'];
            $birthPlaceValue = $this->fields['field_demo_person__birth_place']['value'];
            $deathValue = $this->fields['field_demo_person__death']['value'];
            $deathPlaceValue = $this->fields['field_demo_person__death_place']['value'];

            if (!empty($birthValue) || !empty($birthPlaceValue) || !empty($deathValue) || !empty($deathPlaceValue)) {
                $biographicalData = [];

                if (is_string($birthValue)) {
                    if ($birth = $this->concatenate_date_and_place($birthValue, $birthPlaceValue)) {
                        $biographicalData[] = $birth;
                    }
                }

                if (is_string($deathValue)) {
                    if ($death = $this->concatenate_date_and_place($deathValue, $deathPlaceValue)) {
                        $biographicalData[] = $death;
                    }
                }

                if (!empty($biographicalData)) {
                    $metaFields[] = [
                        'label' => $this->theme_labels['single__metadata__biographical_data'][$oes_language] ??
                            __('Biographical Data', 'oes-demo'),
                        'value' => implode(' — ', $biographicalData)
                    ];
                }
            }

            return $metaFields;
        }

        /**
         * Format date and place to one string.
         *
         * @param string $date The date.
         * @param string $place The place.
         * @return string Return concatenated string.
         */
        function concatenate_date_and_place(string $date, string $place): string
        {
            $return = '';
            if (!empty($date)) {
                $return .= oes_convert_date_to_formatted_string($date, '', 2);
            }
            if (!empty($place)) {
                $return .= (!empty($return) ? ', ' : '') . $place;
            }
            return $return;
        }
    }
endif;
