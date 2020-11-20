<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Record;
use App\RequestParameters\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class RecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }

    public function findAllPaginated(Pagination $pagination): \ArrayIterator
    {
        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.id');

        if ($pagination->getLimit()) {
            $qb->setMaxResults($pagination->getLimit());
        }

        if ($pagination->getOffset()) {
            $qb->setFirstResult($pagination->getOffset());
        }

        $paginator = new Paginator($qb);

        return $paginator->getIterator();
    }
}
