<?php

namespace App\Controller;

use App\Entity\Post;
use App\Normalizer\ConstraintViolationNormalizer;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
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
            AbstractNormalizer::GROUPS => ['main'],
        ]);
    }

    /**
     * @Route("/{id}", methods="GET", requirements={"id": "\d+"})
     */
    public function show(Post $post): Response
    {
        return $this->json($post, Response::HTTP_OK, [], [
            AbstractNormalizer::GROUPS => ['main', 'detail'],
        ]);
    }

    /**
     * @Route("/", methods="POST")
     */
    public function new(
        Request $request,
        ConstraintViolationNormalizer $constraintViolationNormalizer,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $post = $serializer->deserialize($request->getContent(), Post::class, 'json');
        $post->setCreatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($post);

        if ($errors->count() > 0) {
            return $this->json(
                $constraintViolationNormalizer->normalize($errors),
                Response::HTTP_PRECONDITION_FAILED
            );
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
        ConstraintViolationNormalizer $constraintViolationNormalizer,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $serializer->deserialize($request->getContent(), Post::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $post,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['createdAt'],
        ]);

        $errors = $validator->validate($post);

        if ($errors->count() > 0) {
            return $this->json(
                $constraintViolationNormalizer->normalize($errors),
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $manager->flush($post);

        $content = ['location' => $this->generateUrl('app_post_show', ['id' => $post->getId()])];

        return $this->json($content);
    }
}
