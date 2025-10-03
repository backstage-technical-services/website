<?php

namespace App\Helpers;

class Redact
{
    public static function email(string $email): string
    {
        $mail_parts = explode('@', $email);
        $domain_parts = explode('.', $mail_parts[1]);

        $mail_parts[0] = self::mask($mail_parts[0], 2, 1); // show first 2 letters and last 1 letter
        $domain_parts[0] = self::mask($domain_parts[0], 2, 1); // same here
        $mail_parts[1] = implode('.', $domain_parts);

        return implode('@', $mail_parts);
    }

    private static function mask(string $str, int $first, int $last): string
    {
        $len = strlen($str);
        $toShow = $first + $last;
        return substr($str, 0, $len <= $toShow ? 0 : $first) .
            str_repeat('*', $len - ($len <= $toShow ? 0 : $toShow)) .
            substr($str, $len - $last, $len <= $toShow ? 0 : $last);
    }
}
