<?php

namespace App\Repository;

use App\Entity\AppScrapeResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppScrapeResponse>
 *
 * @method AppScrapeResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppScrapeResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppScrapeResponse[]    findAll()
 * @method AppScrapeResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppScrapeResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppScrapeResponse::class);
    }

    public function save(AppScrapeResponse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AppScrapeResponse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AppScrapeResponse[] Returns an array of AppScrapeResponse objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AppScrapeResponse
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
