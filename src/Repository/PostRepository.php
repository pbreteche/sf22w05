<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[]
     */
    public function findByMonth(\DateTimeImmutable $month)
    {
        return $this
            ->createQueryBuilder('post')
            ->andWhere('post.createdAt >= :from')
            ->andWhere('post.createdAt < :to')
            ->addOrderBy('post.createdAt', 'DESC')
            ->getQuery()
            ->setParameters([
                'from' => $month->modify('first day of this month midnight'),
                'to' => $month->modify('first day of next month midnight'),
            ])
            ->getResult()
        ;
    }

    /**
     * @return Post[]
     */
    public function findByMonthDQLVersion(\DateTimeImmutable $month)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT post FROM '.Post::class.' post WHERE 1 '.
            'AND post.createdAt >= :from '.
            'AND post.createdAt < :to '
        )
            ->setParameters([
                'from' => $month->modify('first day of this month midnight'),
                'to' => $month->modify('first day of next month midnight'),
            ])
            ->getResult()
        ;
    }

    /**
     * @return Post[]
     */
    public function findByCategoryName(string $keyword)
    {
        return $this
            ->createQueryBuilder('post')
            ->innerJoin('post.classedBy', 'category')
            ->addSelect('category')
            ->andWhere('category.name LIKE :pattern')
            ->getQuery()
            ->setParameter('pattern', $keyword.'%')
            ->getResult()
        ;
    }
}
