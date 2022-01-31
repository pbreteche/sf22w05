<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class DefaultController
{

    public function index(): Response
    {
        return new Response('{ "status": "OK" }', Response::HTTP_OK, [
            'Content-type' => 'application/json',
        ]);
    }
}
