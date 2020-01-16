<?php


namespace App\Services;


class inputValidator
{

    /**
     * @param $email
     * @return bool
     */
    function isValidEmail($email)
    {
        if (preg_match("/^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/" , $email))
        {
            return true;
        } return false;
    }

    /**
     * @param $numberOfTicket
     * @return bool
     */
    function isValidNumberOfTicket($numberOfTicket){
        if(preg_match("/^[0-9]{1,2}$/", $numberOfTicket)){
            return true;
        } return false;
    }
}