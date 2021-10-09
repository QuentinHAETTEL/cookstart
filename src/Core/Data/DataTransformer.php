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
}
