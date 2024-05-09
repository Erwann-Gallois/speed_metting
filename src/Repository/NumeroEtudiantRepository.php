<?php

namespace App\Repository;

use App\Entity\NumeroEtudiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NumeroEtudiant>
 *
 * @method NumeroEtudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method NumeroEtudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method NumeroEtudiant[]    findAll()
 * @method NumeroEtudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NumeroEtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NumeroEtudiant::class);
    }

//    /**
//     * @return NumeroEtudiant[] Returns an array of NumeroEtudiant objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NumeroEtudiant
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
