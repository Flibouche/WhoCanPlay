<?php

namespace App\Repository;

use App\Entity\Feature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feature>
 */
class FeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feature::class);
    }

    // Méthode qui me permet de rechercher les Features par States
    public function findFeatures(): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f.id', 'f.state', 'f.submissionDate', 'f.slug', 'f.updatedAt',
                    'd.id as disability_id', 'd.name as disability', 'd.icon',
                    'g.id as game',
                    'u.id as user_id', 'u.pseudo as user',
                    'f.id_game_api')
            ->leftJoin('f.disability', 'd')
            ->leftJoin('f.game', 'g')
            ->leftJoin('f.user', 'u')
            ->orderBy('f.state', 'ASC');

            /*
                SELECT f0_.id AS id_0, f0_.state AS state_1, f0_.submission_date AS submission_date_2, f0_.slug AS slug_3, f0_.updated_at AS updated_at_4, d1_.id AS id_5, d1_.name AS name_6, d1_.icon AS icon_7, g2_.id AS id_8, u3_.id AS id_9, u3_.pseudo AS pseudo_10, f0_.id_game_api AS id_game_api_11 
                FROM feature f0_ LEFT JOIN disability d1_ ON f0_.disability_id = d1_.id 
                LEFT JOIN game g2_ ON f0_.game_id = g2_.id 
                LEFT JOIN user u3_ ON f0_.user_id = u3_.id 
                ORDER BY f0_.state ASC
            */
            
        return $qb->getQuery()->getResult();
    }

    // Méthode qui me permet de compter les Fonctionnalités par État
    public function countByState(): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f.state', 'COUNT(f.id) as total')
            ->groupBy('f.state');
        
        return $qb->getQuery()->getResult();
    }

    public function nbFeaturesByDisability(): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('d.name as disability', 'COUNT(f.id) as features')
            ->innerJoin('f.disability', 'd')
            ->groupBy('d.name');

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Feature[] Returns an array of Features objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Feature
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}