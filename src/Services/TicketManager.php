<?php


namespace App\Services;


use App\Entity\Ordered;
use App\Repository\OrderedRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class TicketManager
{

    private $date;
    /**
     * TicketManager constructor.
     */
    public function __construct()
    {
        $this->date = Yaml::parseFile(__DIR__.'/../../configDate.yaml');
    }

    /**
     * @param $date
     * @throws \Exception
     */
    public function availabilityCheck($numberOfTicket)
    {
        $capacity = $this->date['capacity'];
        $i = $capacity - $numberOfTicket;

        if($i >= 0){
          return true;
        } return false;
    }
}