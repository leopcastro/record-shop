<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Record;
use App\Repository\RecordRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * @Route("/record")
 */
class RecordController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("")
     * @OA\Response(
     *     response=200,
     *     description="List of Records",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Record::class))
     *     )
     * )
     *
     * @param RecordRepository $recordRepository
     *
     * @return View
     */
    public function getRecords(RecordRepository $recordRepository): View
    {
        return $this->view($recordRepository->findAll());
    }

    /**
     * @Rest\Get("/{id}")
     * @OA\Response(
     *     response=200,
     *     description="Returns a Record",
     *     @OA\JsonContent(ref=@Model(type=Record::class))
     * )
     *
     * @param int $id
     * @param RecordRepository $recordRepository
     *
     * @return View
     */
    public function getRecord(int $id, RecordRepository $recordRepository): View
    {
        $record = $recordRepository->find($id);

        if (!$record) {
            return $this->view(null, 404);
        }

        return $this->view($record);
    }
}
