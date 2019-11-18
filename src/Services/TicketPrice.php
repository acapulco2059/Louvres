<?php


namespace App\Services;

use App\Entity\User;
use Symfony\Component\Yaml\Yaml;

class TicketPrice
{
    private $price = [];
    private $age = [];

    public function __construct()
    {
        $value = Yaml::parseFile(__DIR__.'/configPrice.yaml');
        $this->price["baby"] = $value["price"]["baby"];
        $this->price["children"] = $value["price"]["children"];
        $this->price["normal"] = $value["price"]["normal"];
        $this->price["senior"] = $value["price"]["senior"];
        $this->price["reduced"] = $value["price"]["reduced"];

        $this->age["baby"] = $value["age"]["baby"];
        $this->age["children"] = $value["age"]["children"];
        $this->age["normal"] = $value["age"]["normal"];
    }


    public function userAge($birth_date)
    {
        $birth = new \datetime($birth_date);
        $today = new \datetime('today');
        $age = $today->diff($birth, true)->y;
        return $age;
    }


    public function userPrice($birth_date, $reduice)
    {

        $age = $this->userAge($birth_date);

        switch (true) {
            case $age < $this->age["baby"]:
                $price = $this->price["baby"];
                break;

            case $age >= $this->age["baby"] AND $this->age["children"]:
                $price = $this->price["children"];
                break;

            case $age >= $this->age["children"] AND $age < $this->age["normal"]:
                if($reduice == true) {
                    $price = $this->price["reduced"];
                } else {
                    $price = $this->price["normal"];
                }
                break;

            case $age > $this->age["normal"]:
                if($reduice == true) {
                    $price = $this->price["reduced"];
                } else {
                    $price = $this->price["senior"];
                }
                break;
        }

        return $price;
    }
}