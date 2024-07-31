<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findProcessedFeaturesByGame($gameId)
    {
        $qb = $this->createQueryBuilder('g')
            ->select('f.id', 'f.state', 'f.name AS featureName', 'f.content', 'd.name AS disabilityName', 'd.icon', 'i.url', 'i.altText', 'i.title', 'i.description', 'i.submissionDate')
            ->leftJoin('g.features', 'f')
            ->leftJoin('f.disability', 'd')
            ->leftJoin('f.images', 'i')
            ->where('f.state = :processed')
            ->andWhere('g.id = :gameId')
            ->setParameter('processed', 'Processed')
            ->setParameter('gameId', $gameId);

            /*
            SELECT f0_.id AS id_0, f0_.state AS state_1, f0_.name AS name_2, f0_.content AS content_3, d1_.name AS name_4, d1_.icon AS icon_5, i2_.url AS url_6, i2_.alt_text AS alt_text_7, i2_.title AS title_8, i2_.description AS description_9, i2_.submission_date AS submission_date_10 
            FROM game g3_ LEFT JOIN feature f0_ ON g3_.id = f0_.game_id 
            LEFT JOIN disability d1_ ON f0_.disability_id = d1_.id 
            LEFT JOIN image i2_ ON f0_.id = i2_.feature_id 
            WHERE f0_.state = 'Processed' AND g3_.id = ?
            */

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Game[] Returns an array of Game objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Game
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
