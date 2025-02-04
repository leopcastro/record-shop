<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Record;
use App\Repository\RecordRepository;
use App\RequestParameters\Pagination;
use App\RequestParameters\RecordFilters;
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

    public function getRecords(Pagination $pagination, RecordFilters $recordFilters): \ArrayIterator
    {
        return $this->recordRepository->findByCriterion($pagination, $recordFilters);
    }

    public function createRecord(RecordParameters $recordParameters): Record
    {
        $newRecord = new Record(
            $recordParameters->getTitle(),
            $recordParameters->getArtist(),
            $recordParameters->getPrice()
        );

        $newRecord->setReleasedYear($recordParameters->getReleasedYear());

        $this->entityManager->persist($newRecord);
        $this->entityManager->flush();

        return $newRecord;
    }

    public function updateRecord(int $recordId, RecordParameters $recordParameters): ?Record
    {
        $record = $this->recordRepository->find($recordId);

        if (!$record) {
            return null;
        }

        $record->setTitle($recordParameters->getTitle());
        $record->setArtist($recordParameters->getArtist());
        $record->setPrice($recordParameters->getPrice());
        $record->setReleasedYear($recordParameters->getReleasedYear());

        $this->entityManager->flush();

        return $record;
    }

    public function deleteRecord(int $recordId): bool
    {
        $record = $this->recordRepository->find($recordId);

        if (!$record) {
            return false;
        }

        $this->entityManager->remove($record);
        $this->entityManager->flush();

        return true;
    }
}
