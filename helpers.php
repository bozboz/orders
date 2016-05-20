<?php

if (!function_exists('format_money')) {
    function format_money($amount)
    {
        setlocale(LC_MONETARY, 'en_GB.UTF-8');
        return money_format('%.2n', $amount/100);
    }
}

if (!function_exists('generate_random_alphanumeric_string')) {
    function generate_random_alphanumeric_string($stringLength = 8)
    {
        $string = '';
        //Alphanumeric characters, with similar looking characters excluded
        $characters = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $characterCount = strlen($characters);
        for ($i = 0; $i < $stringLength; $i++) {
            $stringIndex  = mt_rand(0, $characterCount - 1);
            $string .= $characters[$stringIndex];
        }

        return $string;
    }
}
