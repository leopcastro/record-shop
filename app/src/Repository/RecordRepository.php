<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Record;
use App\RequestParameters\Pagination;
use App\RequestParameters\RecordFilters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class RecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }

    public function findByCriterion(Pagination $pagination, RecordFilters $recordFilters): \ArrayIterator
    {
        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.artist, r.title');

        $whereCriteria = [];
        $whereParameters = [];

        if ($recordFilters->getTitle()) {
            $whereCriteria[] = $qb->expr()->like('r.title', ':title');
            $whereParameters[':title'] = '%' . $recordFilters->getTitle() . '%';
        }

        if ($recordFilters->getArtist()) {
            $whereCriteria[] = $qb->expr()->like('r.artist', ':artist');
            $whereParameters[':artist'] = '%' . $recordFilters->getArtist() . '%';
        }

        if ($whereCriteria) {
            $qb->where(...$whereCriteria);
            $qb->setParameters($whereParameters);
        }

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
