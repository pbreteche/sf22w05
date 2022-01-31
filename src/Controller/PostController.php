<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", methods="GET")
     */
    public function index(PostRepository $repository)
    {
        $posts = $repository->findAll();

        return $this->json($posts);
    }
}
