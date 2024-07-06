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
        ->select('f.state', 'f.name AS featureName', 'f.content', 'd.id', 'd.name AS disabilityName', 'd.icon')
        ->leftJoin('g.features', 'f')
        ->leftJoin('f.disability', 'd')
        ->where('f.state = :processed')
        ->andWhere('g.id = :gameId')
        ->setParameter('processed', 'Processed')
        ->setParameter('gameId', $gameId);
    
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
