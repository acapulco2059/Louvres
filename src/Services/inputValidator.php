<?php


namespace App\Services;


class inputValidator
{
    function isEmail($email)
    {
        $at = strrpos($email, '@');
        if (!$at || $at === 0) return false;
        $email = substr($email, $at);
        $dot = strpos($email, '.');
        if (!$dot || $dot < 2) return false;
        return true;
    }
}