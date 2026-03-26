<?php

if (!function_exists('format_ringgit')) {
    function format_ringgit($amount)
    {
        if ($amount === null) {
            return 'RM 0';
        }

        return 'RM' . number_format($amount, 0, '.', '.');
    }
}

if (!function_exists('format_ringgit_report')) {
    function format_ringgit_report($amount)
    {
        if ($amount === null) {
            return 'RM 0.00';
        }

        return 'RM ' . number_format((float) $amount, 2, '.', ',');
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah($amount)
    {
        if ($amount === null) {
            return 'Rp 0';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
