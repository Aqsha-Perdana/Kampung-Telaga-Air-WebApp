<?php

if (!function_exists('format_ringgit')) {
    function format_ringgit($amount, $showSymbol = true)
    {
        $formatted = number_format($amount, 2);
        return $showSymbol ? 'RM ' . $formatted : $formatted;
    }
}