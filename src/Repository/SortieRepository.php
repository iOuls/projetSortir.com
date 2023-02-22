<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function filtreSorties($site,
                                  $motsclefs,
                                  $datedebut,
                                  $datefin,
                                  $user,
                                  $organisateur,
                                  $inscrit,
                                  $noninscrit,
                                  $passe)
    {
        // mise en place des jointures
        $queryBuilder =
            $this->createQueryBuilder('sortie')
                ->innerJoin(Site::class, 'site', Join::WITH, 'sortie.site = site.id');

        // site
        if ($site != '' || $site != null) {
            $queryBuilder->andWhere('site.id = :site')
                ->setParameter(':site', $site);
        }

        // mots clefs
        if ($motsclefs != '') {
            $queryBuilder->andWhere('sortie.nom LIKE :motsclefs')
                ->setParameter(':motsclefs', '%' . $motsclefs . '%');
        }

        // date début
        if ($datedebut != null) {
            $queryBuilder->andWhere('sortie.dateHeureDebut >= :datedebut')
                ->setParameter(':datedebut', $datedebut);
        }

        // date fin
        if ($datefin != null) {
            $queryBuilder->andWhere('sortie.dateHeureDebut <= :datefin')
                ->setParameter(':datefin', $datefin);
        }

        // organisateur
        if ($organisateur) {
            $queryBuilder->andWhere('sortie.organisateur = :user')
                ->setParameter(':user', $user);
        }

        if (!($inscrit && $noninscrit)) {
            // inscription
            if ($inscrit) {
                $queryBuilder->andWhere(':user MEMBER OF sortie.participant')
                    ->setParameter(':user', $user);
            }

            // non inscrit
            if ($noninscrit) {
                $queryBuilder->andWhere(':user NOT MEMBER OF sortie.participant')
                    ->setParameter(':user', $user);
            }
        }

        // passées
        if ($passe) {
            $queryBuilder->andWhere('sortie.dateHeureDebut < :date')
                ->setParameter(':date', new \DateTime());
        }

        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }


//    /**
//     * @return Sortie[] Returns an array of Sortie objects
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

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
