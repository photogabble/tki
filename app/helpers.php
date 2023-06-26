<?php

if (!function_exists('array_from_enum')) {
    /**
     * @param UnitEnum[] $cases
     * @return array
     */
    function array_from_enum(array $cases) : array {
        return array_map(fn($e) => $e->value, $cases);
    }
}