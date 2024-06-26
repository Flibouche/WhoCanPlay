<?php

namespace App\Repository;

use App\Entity\Subtype;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subtype>
 */
class SubtypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subtype::class);
    }

    // Méthode qui me permet de rechercher les Subtypes par States
    public function findSubtypesByStates(array $states): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.state IN (:states)')
            ->setParameter('states', $states);
        
        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Subtype[] Returns an array of Subtype objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Subtype
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}