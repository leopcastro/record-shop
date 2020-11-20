<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Record;
use App\Repository\RecordRepository;
use App\RequestParameters\Pagination;
use App\RequestParameters\RecordParameters;
use Doctrine\ORM\EntityManagerInterface;

class RecordService
{
    /**
     * @var RecordRepository
     */
    private RecordRepository $recordRepository;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(RecordRepository $recordRepository, EntityManagerInterface $entityManager)
    {
        $this->recordRepository = $recordRepository;
        $this->entityManager = $entityManager;
    }

    public function getRecord(int $recordId): ?Record
    {
        return $this->recordRepository->find($recordId);
    }

    public function getRecords(Pagination $pagination): \ArrayIterator
    {
        return $this->recordRepository->findAllPaginated($pagination);
    }

    public function createRecord(RecordParameters $recordParameters): Record
    {
        $newRecord = new Record(
            $recordParameters->getName(),
            $recordParameters->getArtist(),
            $recordParameters->getPrice()
        );

        $newRecord->setReleasedYear($recordParameters->getReleasedYear());

        $this->entityManager->persist($newRecord);
        $this->entityManager->flush();

        return $newRecord;
    }
}
