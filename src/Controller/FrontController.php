<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Ordered;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Services\DateManager;
use App\Services\TicketPrice;
use App\Services\TicketManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;


class FrontController extends AbstractFOSRestController
{
    /**
     * @Get(
     *     path="/",
     *     name="home"
     * )
     * @View
     */
    public function home()
    {
        $priceValue = Yaml::parseFile("../configPrice.yaml");
        $dateValue = Yaml::parseFile("../configDate.yaml");

        $value = $priceValue + $dateValue;

        return $value;
    }

    /**
     * @Rest\Post(
     *     "/initOrder"
     * )
     * @param Request $request
     * @Rest\View
     */
    public function initOrder(Request $request)
    {
        try {
            $ordered = new Ordered();
            $dateManager = new DateManager();

            $verifDate = $dateManager->isOpened(new \DateTime($request->get('visit_day')));

            if ($verifDate) {
                if ($verifDate) {
                    $ordered->setEmail($request->get('email'))
                        ->setNumberOfTicket($request->get('number_of_ticket'))
                        ->setVisitDay(new \DateTime($request->get('visit_day')))
                        ->setTotalPrice($request->get('total_price'))
                        ->setHalfDay($request->get('half_day'))
                        ->setState(1);


                    $em = $this->getDoctrine()->getManager();

                    $em->persist($ordered);
                    $em->flush();
                }
            }


            $data = [
                "number_of_ticket" => $ordered->getNumberOfTicket(),
                "ordered_unique_id" => $ordered->getUniqueId()
            ];

            $view = $this->view($data, 201);
            return $this->handleView($view);

        } catch (Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', 'a+'), date(d - m - Y) . " : " . $e->getMessage());
            echo 'Exception reçue : ', $e->getMessage(), "\n";
        }
    }


    /**
     * @Post(
     *     "/validOrder"
     * )
     * @param Request $request
     * @Rest\View
     */
    public function validOrdered(Request $request)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $user = new User();
            $ticket = new Ticket();
            $country = new Country();
            $ticketPrice = new TicketPrice();
            $ticketManager = new TicketManager();
            $visitor = $request->get('visitor');
            

            //Init ordered with unique_id
            $ordered = $this->getDoctrine()
                ->getRepository(Ordered::class)
                ->findOneBy(array("uniqueId" => $request->get("ordered_unique_id")));

            $visitorNumber = count($request->get('visitor'));

            if (isset($ordered)) {
                for ($i = 1; $i < $visitorNumber; $i++) {
                    $birthday = new \DateTime($visitor[$i]['birthday']);
                    $userPrice = $ticketPrice->userPrice($visitor[$i]['birthday'], $visitor[$i]['reduice']);

                    $user->setFirstname($visitor[$i]['firstname'])
                        ->setLastname($visitor[$i]['lastname'])
                        ->setBirthDate($birthday)
                        ->setCountry($visitor[$i]['country']);

                    $ticket->setUser($user)
                        ->setPrice($userPrice);

                    $ordered->addTicket($ticket)
                        ->setState(2);

                    //setting in BDD with doctrine
                    $em->persist($ordered);
                    $em->flush();
                }

                $orderedId = $ordered->getId();

                $data = [
                    "total_price" => $ordered->getTotalPrice()
                ];

                $view = $this->view($data, 201);
                return $this->handleView($view);
            }
        } catch (Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', 'a+'), date(d - m - Y) . " : " . $e->getMessage());
            echo 'Exception reçue : ', $e->getMessage(), "\n";
        }

    }
}