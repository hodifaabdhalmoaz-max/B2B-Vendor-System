<?php

if (!function_exists('format_price')) {
    /**
     * Format price to Yemeni Riyal with thousand separator
     *
     * @param mixed $amount
     * @return string
     */
    function format_price($amount)
    {
        // Remove any existing commas if present (just in case)
        if (is_string($amount)) {
            $amount = str_replace(',', '', $amount);
        }
        
        return number_format(floatval($amount), 0, '.', ',') . ' ريال جديد';
    }
}
