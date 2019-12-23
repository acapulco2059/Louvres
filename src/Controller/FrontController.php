<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Ordered;
use App\Entity\Ticket;
use App\Entity\User;
use App\Services\DateManager;
use App\Services\TicketPrice;
use App\Services\TicketManager;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;


class FrontController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(
     *     "/"
     * )
     * @View
     */
    public function home()
    {
        $priceValue = Yaml::parseFile("../configPrice.yaml");
        $dateValue = Yaml::parseFile("../configDate.yaml");

        $value = $priceValue + $dateValue;

        $view = $this->view($value, 200)
            ->setTemplate('public/index.html');
        return $view;
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
            //instantiation of entities
            $ordered = new Ordered();
            $dateManager = new DateManager();
            $ticketManager = new TicketManager();

            $dateManager->isOpened(new \DateTime($request->get('visit_day')));

            //Count of ticket by date
            $getCountTicket = $this->getDoctrine()
                ->getRepository(Ordered::class)
                ->countNumberOfTicket(new \DateTime($request->get('visit_day')));
            $getCountTicket = intval($getCountTicket['totalTicket']);

            //check if the date selected and available
            if ($dateManager->isOpened(new \DateTime($request->get('visit_day'))))
            {
                //check if the number of remaining tickets is sufficient
                if ($ticketManager->availabilityCheck($getCountTicket))
                {
                    $ordered->setEmail($request->get('email'))
                        ->setNumberOfTicket($request->get('number_of_ticket'))
                        ->setVisitDay(new \DateTime($request->get('visit_day')))
                        ->setTotalPrice($request->get('total_price'))
                        ->setHalfDay($request->get('half_day'))
                        ->setState(1);

                    $em = $this->getDoctrine()->getManager();

                    //set Order in BDD with doctrine
                    $em->persist($ordered);
                    $em->flush();

                    //prepares the information to be transmitted
                    $data = [
                        "number_of_ticket" => $ordered->getNumberOfTicket(),
                        "ordered_unique_id" => $ordered->getUniqueId()
                    ];

                    $view = $this->view($data, 201);
                    return $this->handleView($view);

                } return "Pas assez de place";

            } return "Date non disponible";


        } catch (\Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', 'a+'), date('d-m-Y') . " : initOrder - " . $e->getMessage()) ."\n";
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
            $visitor = $request->get('visitor');

            //Init ordered with unique_id
            $ordered = $this->getDoctrine()
                ->getRepository(Ordered::class)
                ->findOneBy(array("uniqueId" => $request->get("ordered_unique_id")));

            $visitorNumber = count($request->get('visitor'));

            if (isset($ordered)) {
                for ($i = 0; $i < $visitorNumber; $i++) {
                    $user = new User();
                    $ticket = new Ticket();
                    $ticketPrice = new TicketPrice();

                    $birthday = new \DateTime($visitor[$i]['birthday']);
                    $userPrice = $ticketPrice->userPrice($visitor[$i]['birthday'], $visitor[$i]['reduice']);

                    $user->setFirstname($visitor[$i]['firstname'])
                        ->setLastname($visitor[$i]['lastname'])
                        ->setBirthDate($birthday)
                        ->setCountryId($visitor[$i]['country'])
                        ->setReduice($visitor[$i]['reduice']);

                    $ticket->setUser($user)
                        ->setPrice($userPrice);

                    $ordered->addTicket($ticket);
                }
                $ordered->setState(2);
                //setting in BDD with doctrine
                $em->persist($ordered);
                $em->flush();

                //get values ​​for the next algorithm
                $getTicket = $ordered->getTickets()->getValues();
                $numberOfTicket = $ordered->getTickets()->count();

                // Creating array for calculated the totalPrice
                $prices = array();
                for($k = 0; $k < $numberOfTicket; $k++)
                {
                    $price = $getTicket[$k]->getPrice();
                    array_push($prices, $price);
                }
                $totalPrice = array_sum($prices);

                // update Ordered
                $ordered->setTotalPrice($totalPrice)
                    ->setNumberOfTicket($numberOfTicket)
                    ->setState(2);

                // Set ordered in BDD
                $em->persist($ordered);
                $em->flush();

                // Creating the Users array for the View
                $users = array();
                for($j = 0; $j < $numberOfTicket; $j++)
                {
                    $insertUser = array('firstname' => $getTicket[$j]->getUser()->getFirstname(),
                        'lastname' => $getTicket[$j]->getUser()->getLastname(),
                        'unique_id' => $getTicket[$j]->getUniqueId(),
                        'price' => $getTicket[$j]->getPrice());
                    array_push($users, $insertUser);
                }

                // Array for the View
                $data = [
                    'ordered_unique_id' => $ordered->getUniqueId(),
                    'total_price' => $ordered->getTotalPrice(),
                    'users' => $users
                ];

                $view = $this->view($data, 201);
                return $this->handleView($view);
            } throw $this->createNotFoundException(sprintf('No Ordered for the id ', $request->get('ordered_unique_id')));

        } catch (\Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', "a+"), date('d-m-Y') . " : validOrder - " . $e->getMessage(). "\n");
            echo 'Exception reçue : ', $e->getMessage(), "\n";
        }
    }


    /**
     * @Post(
     *     "/payment"
     * )
     * @param Request $request
     * @Rest\View
     */
    public function payment(Request $request, \Swift_Mailer $mailer)
    {
        try{
            $em = $this->getDoctrine()->getManager();

            //Init ordered with unique_id
            $uniqueId = $request->get('ordered_unique_id');

            if(!empty($uniqueId))
            {
                $ordered = $this->getDoctrine()
                    ->getRepository(Ordered::class)
                    ->findOneBy(array("uniqueId" => $request->get("ordered_unique_id")));

                \Stripe\Stripe::setApiKey('sk_test_N902uxPZfI67qNRHX75vvdLc00L7Kv9Eo3');

                $intent = \Stripe\PaymentIntent::create([
                    'amount' => $ordered->getTotalPrice()*100,
                    'currency' => 'eur',
                    'payment_method_types' => ['card'],
                ]);


                $ordered->setStripeId($intent->id)
                    ->setState(3);
                $em->persist($ordered);
                $em->flush();

                if($intent->status == "succeeded")
                {
                    $message = (new \Swift_Message('Billeterie du Louvre'))
                        ->setFrom('mobyteck@gmail.com')
                        ->setTo($ordered->getEmail())
                        ->setBody(
                            $this->renderView(
                                'emails/receipt.html.twig',
                                ['ordered' => $ordered]
                            ),
                            'text/html'
                        )
                    ;
                    $mailer->send($message,$failures);
                }

                $data = [
                    "status" => $intent->status
                ];


                $view = $this->view($data, 201);
                return $this->handleView($view);
            }
        }
        catch(\Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', "a+"), date('d-m-Y') . " : Payment - " . $e->getMessage());
            echo 'Exception reçue : ', $e->getMessage(), "\n";
        }
    }

    /**
     * @Post(
     *  "/testMail"
     * )
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * Rest\View
     */
    public function testMail(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();

        //Init ordered with unique_id
        $uniqueId = $request->get('ordered_unique_id');

        $ordered = $this->getDoctrine()
            ->getRepository(Ordered::class)
            ->findOneBy(array("uniqueId" => $uniqueId));


        $message = (new \Swift_Message('Billeterie du Louvre'))
            ->setFrom('mobyteck@gmail.com')
            ->setTo($ordered->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/receipt.html.twig',
                    ['ordered' => $ordered]
                ),
                'text/html'
            )
        ;

        $result = $mailer->send($message);

        return $this->render('emails/receipt.html.twig', ['ordered' => $ordered]);
    }

}