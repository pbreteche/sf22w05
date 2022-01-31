<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(methods={"GET", "HEAD"}, defaults={"_format": "json"})
 */
class DefaultController extends AbstractController
{

    /**
     * @Route("/")
     */
    public function index(): Response
    {
        return $this->json(['status' => 'OK']);
    }
}
