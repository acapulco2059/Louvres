<?php

namespace App\Controller;

use App\Entity\Ordered;
use App\Repository\OrderRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
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
        $dateValue= Yaml::parseFile("../configDate.yaml");

        $value = $priceValue + $dateValue;

        return $value;
    }

    /**
     * @Post(
     *     "/initOrder"
     * )
     * @param Request $request
     * @return View
     */
    public function initOrder(Request $request)
    {
        $ordered = new Ordered();
        $ordered->setEmail($request->get('email'));
        $ordered->setNumberOfTicket($request->get('number_of_ticket'));
        //$ordered->setVisitDate($request->get("date"));



        $em = $this->getDoctrine()->getManager();

        $em->persist($ordered);
        $em->flush();

        return View::create($ordered, Response::HTTP_CREATED);
    }

}