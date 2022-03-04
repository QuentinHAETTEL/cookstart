<?php

namespace App\Core\Data;

class DataTransformer
{
    /**
     * Convert an array written as a string into an array
     * Ex: '["LOREM", "IPSUM"]' into [0 => 'LOREM', 1 => 'IPSUM']
     */
    public function stringArrayToArray(string $value): array
    {
        $search = ['[', ']', '"', '\''];
        return explode(',', str_replace($search, '', $value));
    }


    /**
     * Convert an array into an array written as a string
     * Ex: [0 => 'LOREM', 1 => 'IPSUM'] into '["LOREM", "IPSUM"]'
     */
    public function arrayToStringArray(array $array): string
    {
        $string = '[';
        foreach ($array as $key => $value) {
            $string .= '"' . $value . '"';
            if ($key !== array_key_last($array)) {
                $string .= ', ';
            }
        }
        $string .= ']';

        return $string;
    }


    /**
     * Return an array value by a given string of keys
     * Ex: convert 'lorem.ipsum' into $array['lorem']['ipsum'] and return corresponding value (string or array)
     */
    public function arrayValueByStringKeys(string $separator, string $string, array $array)
    {
        $keys = explode($separator, $string);
        for ($i=0; $i<count($keys); $i++) {
            $array = $array[$keys[$i]];
        }

        return $array;
    }


    /**
     * Return int with a duration in seconds by given a string of time
     * Ex: '02:59:00' into '10799'
     */
    public function stringDurationToSeconds(string $time): int
    {
        $parts = explode(':', $time);
        while (count($parts) < 3) {
            $parts[] = 0;
        }

        return 3600 * $parts[0] + 60 * $parts[1] + $parts[2];
    }
}
