<?php

namespace App\Controller;

use App\Entity\Category;
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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    private const PAGE_LENGTH = 3;

    /**
     * @Route("/", methods="GET")
     */
    public function index(
        Request $request,
        ConstraintViolationNormalizer $constraintViolationNormalizer,
        PostRepository $repository,
        ValidatorInterface $validator
    ): Response {
        $pageNumber = $request->query->get('page', 1);
        $errors = $validator->validate($pageNumber, new Assert\Positive());

        if ($errors->count() > 0) {
            return $this->json(
                $constraintViolationNormalizer->normalize($errors),
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $posts = $repository->findBy([], ['createdAt' => 'DESC'], self::PAGE_LENGTH, ($pageNumber - 1) * self::PAGE_LENGTH);

        return $this->json($posts, Response::HTTP_OK, [], [
            AbstractNormalizer::GROUPS => ['main'],
        ]);
    }

    /**
     * @Route("/{month}", methods="GET", requirements={"month": "\d{4}-\d{2}"}))
     */
    public function indexByMonth(\DateTimeImmutable $month, PostRepository $repository): Response
    {
        $posts = $repository->findByMonth($month);

        return $this->json($posts, Response::HTTP_OK, [], [
            AbstractNormalizer::GROUPS => ['main'],
        ]);
    }

    /**
     * @Route("/by-category/{id}", methods="GET", requirements={"id": "\d+"})
     */
    public function indexByCategory(Category $category, PostRepository $repository): Response
    {
        $posts = $repository->findBy(['classedBy' => $category]);

        return $this->json($posts, Response::HTTP_OK, [], [
            AbstractNormalizer::GROUPS => ['main'],
        ]);
    }

    /**
     * @Route("/search-category-name/{keyword}", methods="GET")
     */
    public function searchByCategoryName(string $keyword, PostRepository $repository): Response
    {
        $posts = $repository->findByCategoryName($keyword);

        return $this->json($posts, Response::HTTP_OK, [], [
            AbstractNormalizer::GROUPS => ['main'],
        ]);
    }

    /**
     * @Route("/{id}", methods="GET", requirements={"id": "\d+"})
     */
    public function show(Post $post): Response
    {
        // Exemple d'appel à une autre méthode de contrôleur
        $jsonRelatedPosts = $this->forward(self::class.'::indexByCategory', [
            'id' => $post->getClassedBy()->getId(),
        ]);

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

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
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

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }
}
