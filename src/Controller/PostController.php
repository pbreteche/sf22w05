<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", methods="GET")
     */
    public function index(PostRepository $repository): Response
    {
        $posts = $repository->findAll();

        $normalizedPosts = array_map(function (Post $post) {
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'created_at' => $post->getCreatedAt()->format('c'),
            ];
        }, $posts);

        return $this->json($normalizedPosts);
    }

    /**
     * @Route("/{id}", methods="GET", requirements={"id": "\d+"})
     */
    public function show($id, PostRepository $repository): Response
    {
        $post = $repository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('No post found with id '.$id);
        }

        $normalizedPost = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'created_at' => $post->getCreatedAt()->format('c'),
            'body' => $post->getBody(),
        ];

        return $this->json($normalizedPost);
    }
}
