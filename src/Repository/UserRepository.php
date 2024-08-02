<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    // Méthode pour récupérer les informations publiques de l'utilisateur
    public function findOneByPublicInformations($pseudo): ?array
    {
        // Je récupère l'utilisateur par son pseudo en sélectionnant les informations publiques
        $user = $this->createQueryBuilder('u')
            ->select('u.id', 'u.pseudo', 'u.avatar', 'u.biography', 'u.registrationDate')
            ->where('u.pseudo = :pseudo')
            ->setParameter('pseudo', $pseudo)
            ->getQuery()
            ->getOneOrNullResult();
    
            // Si l'utilisateur existe, alors je récupère ses topics, posts et features
        if ($user) {
            $topics = $this->getEntityManager()->createQueryBuilder()
                ->select('t.id', 't.title', 't.slug', 't.creationDate')
                ->from('App\Entity\Topic', 't')
                ->where('t.user = :userId')
                ->setParameter('userId', $user['id'])
                ->orderBy('t.id', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();
    
            // J'ajoute un tableau de topics à l'utilisateur
            $user['topics'] = $topics;

            $posts = $this->getEntityManager()->createQueryBuilder()
                ->select('p.id')
                ->from('App\Entity\Post', 'p')
                ->where('p.user = :userId')
                ->setParameter('userId', $user['id'])
                ->getQuery()
                ->getResult();

            // J'ajoute un tableau de posts à l'utilisateur
            $user['posts'] = $posts;

            $features = $this->getEntityManager()->createQueryBuilder()
                ->select('f.id', 'f.name', 'f.state', 'f.submissionDate','g.id as game_id', 'g.slug as game_slug')
                ->from('App\Entity\Feature', 'f')
                ->join('f.game', 'g')
                ->where('f.user = :userId')
                ->andWhere('f.state = :processed')
                ->setParameter('userId', $user['id'])
                ->setParameter('processed', 'processed')
                ->getQuery()
                ->getResult();

            // J'ajoute un tableau de features à l'utilisateur
            $user['features'] = $features;
        }
    
        return $user;
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
