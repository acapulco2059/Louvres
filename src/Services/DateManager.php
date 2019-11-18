<?php


namespace App\Services;
use Symfony\Component\Yaml\Yaml;


class DateManager
{
    private $openDay;
    private $holiday = [];

    public function __construct(){
        $dates = Yaml::parseFile(__DIR__.'/configDate.yaml');
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
    public function isOpened(DateTime $date){
        $day = date("l", $date);
        $opened = $this->openDay[$day];
        if ($opened) $opened = !$this->isHoliday($date);
        return $opened;
    }

    /**
     * [isHoliday description]
     * @param  DateTime $date [description]
     * @return boolean        [description]
     */
    private function isHoliday(DateTime $date){
        $reservationDate = $date->format('Y/m/d');
		if (array_search($reservationDate, $this->holiday)) return true;
		return false;
	}
}

// DateManager->isOpened(dateDuFomulaire)