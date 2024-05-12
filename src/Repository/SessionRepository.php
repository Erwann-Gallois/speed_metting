<?php

namespace App\Repository;

use App\Entity\Session;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    public function findUniqueSession (int $id_pro, DateTimeInterface $date, int $id_eleve): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.pro = :id_pro')
            ->andWhere('s.heure = :date')
            ->andWhere('s.eleve = :id_eleve')
            ->setParameter('id_pro', $id_pro)
            ->setParameter('date', $date)
            ->setParameter('id_eleve', $id_eleve)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUniqueSessionProEleve (int $id_pro, int $id_eleve): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.pro = :id_pro')
            ->andWhere('s.eleve = :id_eleve')
            ->setParameter('id_pro', $id_pro)
            ->setParameter('id_eleve', $id_eleve)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllSessionProForOneHour (int $id_pro, DateTimeInterface $heure): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.pro = :id_pro')
            ->andWhere('s.heure = :heure')
            ->setParameter('id_pro', $id_pro)
            ->setParameter('heure', $heure)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllSessionPro (int $id_pro): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.pro = :id_pro')
            ->setParameter('id_pro', $id_pro)
            ->orderBy('s.heure', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllSessionEleve (int $id_eleve): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.eleve = :id_eleve')
            ->setParameter('id_eleve', $id_eleve)
            ->getQuery()
            ->getResult()
        ;
    }

    public function SearchReservation (int $id_eleve = null, int $id_pro = null, DateTimeInterface $heure = null): array
    {
        $query = $this->createQueryBuilder('s');

        if ($id_eleve) {
            $query->andWhere('s.eleve = :id_eleve')
                ->setParameter('id_eleve', $id_eleve);
        }

        if ($id_pro) {
            $query->andWhere('s.pro = :id_pro')
                ->setParameter('id_pro', $id_pro);
        }

        if ($heure) {
            $query->andWhere('s.heure = :heure')
                ->setParameter('heure', $heure);
        }

        return $query->getQuery()->getResult();
    }
}
