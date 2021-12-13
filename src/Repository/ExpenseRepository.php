<?php

namespace App\Repository;

use App\Entity\Expense;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    // /**
    //  * @return Expense[] Returns an array of all Expenses total (ti and te)
    //  */
    public function findTotalExpenses($endDate = null, $startDate = null)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.tax_rate,SUM(e.value_ti) as total_ti, SUM(e.value_te) as total_te');

        if($startDate != null) {
            $qb->Where('e.issued_on > :startDate')
            ->setParameter('startDate', $startDate);
        }

        if($endDate != null) {
            if($endDate == null) {
                $qb->where('e.issued_on < :dateEnd');
            } else {
                $qb->andWhere('e.issued_on < :dateEnd');
            }
            $qb->setParameter('dateEnd', $endDate);
        }

        return $qb->getQuery()
                ->getResult();
    }

    // /**
    //  * @return Expense[] Returns an array of Expense total (ti and te) by category
    //  */
    public function findTotalByCathegory($endDate = null, $startDate = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.category, SUM(e.value_ti) as total_ti, SUM(e.value_te) as total_te')
            ->groupBy('e.category');

        if($startDate != null) {
            $qb->Where('e.issued_on > :startDate')
            ->setParameter('startDate', $startDate);
        }

        if($endDate != null) {
            if($endDate == null) {
                $qb->where('e.issued_on < :dateEnd');
            } else {
                $qb->andWhere('e.issued_on < :dateEnd');
            }
            $qb->setParameter('dateEnd', $endDate);
         }

        $qb->orderBy('e.value_ti', 'DESC');

        return $qb->getQuery()
                ->getResult();
    }

    // /**
    //  * @return Expense[] Returns an array of Expense total (ti and te) by Vehicle
    //  */
    public function findTotalByVehicle($endDate = null, $startDate = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('v.plate_number, SUM(e.value_ti) as total_ti, SUM(e.value_te) as total_te')
            ->innerJoin('e.vehicle', 'v')
            ->groupBy('e.vehicle');

        if($startDate != null) {
            $qb->Where('e.issued_on > :startDate')
            ->setParameter('startDate', $startDate);
        }

        if($endDate != null) {
            if($endDate == null) {
                $qb->where('e.issued_on < :dateEnd');
            } else {
                $qb->andWhere('e.issued_on < :dateEnd');
            }
            $qb->setParameter('dateEnd', $endDate);
         }

        $qb->orderBy('e.value_ti', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()
                ->getResult();
    }

    // /**
    //  * @return Expense[] Returns an array of Expense total (ti and te) by Vehicle
    //  */
    public function findExpensesByVehicle($plateNumber, $endDate = null, $startDate = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e, v')
            ->where('v.plate_number = :plateNumber');

        if($startDate != null) {
            $qb->andWhere('e.issued_on > :startDate')
            ->setParameter('startDate', $startDate);
        }

        if($endDate != null) {
            $qb->andWhere('e.issued_on < :endDate')
            ->setParameter('endDate', $endDate);
        }

        $qb->innerJoin('e.vehicle', 'v')
            ->orderBy('e.value_ti', 'DESC')
            ->setMaxResults(50)
            ->setParameter(':plateNumber', $plateNumber);

        return $qb->getQuery()
        ->getArrayResult();
    }

    // /**
    //  * @return Expense[] Returns an array of Expense objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Expense
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
