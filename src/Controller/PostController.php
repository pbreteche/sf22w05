<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        return $this->json($posts, Response::HTTP_OK, [], [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['body'],
        ]);
    }

    /**
     * @Route("/{id}", methods="GET", requirements={"id": "\d+"})
     */
    public function show(Post $post): Response
    {
        return $this->json($post);
    }

    /**
     * @Route("/", methods="POST")
     */
    public function new(
        Request $request,
        EntityManagerInterface $manager,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        $post = (new Post())
            ->setTitle($data['title'])
            ->setBody($data['body'])
            ->setCreatedAt(new \DateTimeImmutable())
        ;

        $errors = $validator->validate($post);

        if ($errors->count() > 0) {
            $content = [];
            foreach ($errors as $error) {
                $content[] = [
                    'path' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json($content, Response::HTTP_PRECONDITION_FAILED);
        }

        $manager->persist($post);
        $manager->flush($post);

        $content = ['location' => $this->generateUrl('app_post_show', ['id' => $post->getId()])];

        return $this->json($content, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", methods="PUT", requirements={"id": "\d+"})
     */
    public function update(
        Post $post,
        Request $request,
        EntityManagerInterface $manager,
        ValidatorInterface $validator
    ): Response {
        $data = json_decode($request->getContent(), true);

        $post
            ->setTitle($data['title'])
            ->setBody($data['body'])
        ;

        $errors = $validator->validate($post);

        if ($errors->count() > 0) {
            $content = [];
            foreach ($errors as $error) {
                $content[] = [
                    'path' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json($content, Response::HTTP_PRECONDITION_FAILED);
        }

        $manager->flush($post);

        $content = ['location' => $this->generateUrl('app_post_show', ['id' => $post->getId()])];

        return $this->json($content);
    }
}
