<?php


namespace App\Services;

use App\Entity\User;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Yaml\Yaml;

class TicketPrice
{
    private $price = [];
    private $age = [];
    private $userPrice = "";

    public function __construct()
    {
        $value = Yaml::parseFile(__DIR__.'/../../configPrice.yaml');
        $this->price["baby"] = $value["price"]["baby"];
        $this->price["children"] = $value["price"]["children"];
        $this->price["normal"] = $value["price"]["normal"];
        $this->price["senior"] = $value["price"]["senior"];
        $this->price["reduced"] = $value["price"]["reduced"];
        $this->price["halfday"] = $value["price"]["halfday"];

        $this->age["baby"] = $value["age"]["baby"];
        $this->age["children"] = $value["age"]["children"];
        $this->age["normal"] = $value["age"]["normal"];

        $userPrice = $this->userPrice;
    }


    public function userAge($birth_date)
    {
        $birth = new \datetime($birth_date);
        $today = new \datetime('today');
        $age = $today->diff($birth, true)->y;
        return $age;
    }


    /**
     * @param $birth_date
     * @param $reduice
     * @param $halfday
     * @return float|int|mixed
     */
    public function userPrice($birth_date, $reduice, $halfday)
    {
        $age = $this->userAge($birth_date);

        switch (true) {
            case $age < $this->age["baby"]:
                $this->priceCalc($halfday, $reduice, $this->price["baby"], $this->price["baby"]);
                break;

            case $age >= $this->age["baby"] AND $age < $this->age["children"]:
                $this->priceCalc($halfday, $reduice, $this->price["children"], $this->price["children"]);
                break;

            case $age >= $this->age["children"] AND $age < $this->age["normal"]:
                $this->priceCalc($halfday, $reduice, $this->price["reduced"], $this->price["normal"]);
                break;

            case $age > $this->age["normal"]:
                $this->priceCalc($halfday, $reduice, $this->price["reduced"], $this->price["senior"]);
                break;
        }

        return $this->userPrice;
    }

    private function priceCalc($halfday, $reduice, $reduicePrice, $userPrice)
    {
        if($halfday && $userPrice){
            if($reduice) {
                $price = $reduicePrice * $this->price["halfday"];
            } else {
                $price = $userPrice * $this->price["halfday"];
            }
        } else {
            if($reduice) {
                $price = $reduicePrice;
            } else {
                $price = $userPrice;
            }
        }
        $this->userPrice = $price;
    }
}