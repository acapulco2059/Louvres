<?php


namespace App\Services;
use Symfony\Component\Yaml\Yaml;


class DateManager
{
    private $openDay;
    private $holiday = [];

    public function __construct(){
        $dates = Yaml::parseFile(__DIR__.'/../../configDate.yaml');
        $this->openDay = $dates["open"];
        foreach ($dates["holiday"] as $key => $value) {
            $valueTmp = explode("/",$value);
            if (count($valueTmp) === 2) $value = date("Y").'/'.$value;
            array_push($this->holiday, $value);
        }
    }


    /**
     * check if the museum is opened this day
     * @param \DateTime $date
     * @return bool
     * @throws \Exception
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

    /**
     * @param \DateTime $date
     * @param $halfDayStatus
     * @throws \Exception
     */
    public function halfDay(\DateTime $date, $halfDayStatus)
    {
        $today = new \DateTime();
        $hours = date_format($today, 'H');
        $todaysDate = $today->format('Y/m/d');
        $visitDate = $date->format('Y/m/d');
//        if($todaysDate === $visitDate && !$hours < 15) return false;
        if($todaysDate === $visitDate && $hours > 12){
            if($halfDayStatus){
                return true;
            }
            return false;
        }
        return true;
    }
}