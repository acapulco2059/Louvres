<?php


namespace App\Services;
use Symfony\Component\Yaml\Yaml;


class DateManager
{
    private $openDay;
    private $holiday = [];

    public function __construct(){
        $dates = Yaml::parseFile('../configDate.yaml');
        $this->openDay = $dates["open"];
        foreach ($dates["holiday"] as $key => $value) {
            $valueTmp = explode("/",$value);
            if (count($valueTmp === 2)) $value = date("Y").'/'.$value;
            array_push($this->holiday, $value);
        }
    }


    /**
     * check if the museum is opened this day
     * @param  DateTime $date [description]
     * @return boolean        [description]
     */
    public function isOpened(\DateTime $date)
    {
        $day = date_format($date, 'l');
        $opened = $this->openDay[$day];
        $reservationDate = $date->format('Y/m/d');
        if ($opened) {
            if (!in_array($reservationDate, $this->holiday)) return true;
        }
        return false;
    }
}

// DateManager->isOpened(dateDuFomulaire)