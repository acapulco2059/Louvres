<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Ordered;
use App\Entity\Ticket;
use App\Entity\User;
use App\Services\DateManager;
use App\Services\inputValidator;
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
//    /**
//     * @Route("/", name="home")
//     */
//    public function index()
//    {
//        return $this->render('index.html.twig', [
//            'title' => "Musée du Louvre - Accueil",
//        ]);
//    }

    /**
     * @Rest\Get(
     *     "/"
     * )
     * @Rest\View
     */
    public function home()
    {
        $priceValue = Yaml::parseFile("../configPrice.yaml");
        $dateValue = Yaml::parseFile("../configDate.yaml");

        $value = $priceValue + $dateValue;

        $view = $this->view($value, 200)
            ->setTemplate('index.html');
        return $this->handleView($view);
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
            $inputValidator = new inputValidator();

            $dateManager->isOpened(new \DateTime($request->get('visit_day')));

            //Count of ticket by date
            $getCountTicket = $this->getDoctrine()
                ->getRepository(Ordered::class)
                ->countNumberOfTicket(new \DateTime($request->get('visit_day')));
            $getCountTicket = intval($getCountTicket['totalTicket']);

            //Get All Country for the view
            $allCountry = $this->getDoctrine()
                ->getManager()
                ->getRepository(Country::class);
            $allCountry = $allCountry->findAll();

            //check if the date selected and available
            if( !$dateManager->isOpened(new \DateTime($request->get('visit_day')))) return "Date non disponible";
            //check if the number of remaining tickets is sufficient
            if( !$ticketManager->availabilityCheck($getCountTicket, $request->get('number_of_ticket'))) return "Pas assez de place";
            // Validate the email
            if( !$inputValidator->isValidEmail($request->get('email'))) return "Email non valide";
            // Check if the var number of ticket is a number
            if( !$inputValidator->isValidNumberOfTicket($request->get('number_of_ticket'))) return "Ceci n'est pas un nombre";
            // halfDay Control
            if( !$dateManager->halfDay(new \DateTime($request->get('visit_day')), $request->get('half_day'))) return "Billet Journée non disponible à cet heure de la journée, veuillez selectionner un billet demi-journée";

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

            // Creating the Countrys array for the View
            $countrys = array();
            foreach($allCountry as $value) {
                $insertCountry = array('code' => $value->getCode(),
                    'name_fr_fr' => $value->getNameFrFr());
                array_push($countrys, $insertCountry);
            }

            //prepares the information to be transmitted
            $data = [
                "number_of_ticket" => $ordered->getNumberOfTicket(),
                "ordered_unique_id" => $ordered->getUniqueId(),
                "country" => $countrys
            ];

            $view = $this->view($data, 201);
            return $this->handleView($view);

        } catch (\Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', 'a+'), date('d-m-Y') . " : initOrder - " . $e->getMessage()) . "\n";
            echo 'Exception reçue : ', $e->getMessage(), "\n";
        }
    }


    /**
     * @Rest\Post(
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

            if( !isset($ordered)) throw $this->createNotFoundException(sprintf('No Ordered for the id ', $request->get('ordered_unique_id')));
            for ($i = 0; $i < $visitorNumber; $i++) {
                $user = new User();
                $ticket = new Ticket();
                $ticketPrice = new TicketPrice();

                $birthday = new \DateTime($visitor[$i]['birthday']);
                $userPrice = $ticketPrice->userPrice($visitor[$i]['birthday'], $visitor[$i]['reduice'], $ordered->getHalfDay());

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
            for ($k = 0; $k < $numberOfTicket; $k++) {
                $price = $getTicket[$k]->getPrice();
                array_push($prices, $price);
            }
            $totalPrice = array_sum($prices);

            // Create a intent Payment in Stripe
            \Stripe\Stripe::setApiKey('sk_test_N902uxPZfI67qNRHX75vvdLc00L7Kv9Eo3');

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $totalPrice * 100,
                'currency' => 'eur',
                'payment_method_types' => ['card'],
            ]);

            // update Ordered
            $ordered->setTotalPrice($totalPrice)
                ->setNumberOfTicket($numberOfTicket)
                ->setStripeId($intent->client_secret)
                ->setState(2);

            // Set ordered in BDD
            $em->persist($ordered);
            $em->flush();

            // Creating the Users array for the View
            $users = array();
            for ($j = 0; $j < $numberOfTicket; $j++) {
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
                'users' => $users,
                'stripe_id' => $intent->id,
                'stripe_client_secret' => $intent->client_secret
            ];

            $view = $this->view($data, 201);
            return $this->handleView($view);



        } catch (\Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', "a+"), date('d-m-Y') . " : validOrder - " . $e->getMessage() . "\n");
            echo 'Exception reçue : ', $e->getMessage(), "\n";
        }
    }


    /**
     * @Rest\Post(
     *     "/payment"
     * )
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @Rest\View
     */
    public function payment(Request $request, \Swift_Mailer $mailer)
    {
        try {
            $em = $this->getDoctrine()->getManager();

            //Init ordered with unique_id
            $uniqueId = $request->get('ordered_unique_id');
            $paymentIntentId = $request->get('payment_intent_id');

            if (!empty($uniqueId)) {
                $ordered = $this->getDoctrine()
                    ->getRepository(Ordered::class)
                    ->findOneBy(array("uniqueId" => $request->get("ordered_unique_id")));

                \Stripe\Stripe::setApiKey('sk_test_N902uxPZfI67qNRHX75vvdLc00L7Kv9Eo3');

                $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

                if ($intent->status == "succeeded") {
                    $message = (new \Swift_Message('Billeterie du Louvre'))
                        ->setFrom('mobyteck@gmail.com')
                        ->setTo($ordered->getEmail())
                        ->setBody(
                            $this->renderView(
                                'emails/receipt.html.twig',
                                ['ordered' => $ordered]
                            ),
                            'text/html'
                        );
                    $mailer->send($message, $failures);
                }

                $ordered->setState(3);
                $em->persist($ordered);
                $em->flush();

                $data = [
                    "status" => $intent->status,
                    "email" => $ordered->getEmail()
                ];


                $view = $this->view($data, 201);
                return $this->handleView($view);
            }
        } catch (\Exception $e) {
            fwrite(fopen('../src/errors/frontErrors.txt', "a+"), date('d-m-Y') . " : Payment - " . $e->getMessage());
            echo 'Exception reçue : ', $e->getMessage(), "\n";
        }
    }
}