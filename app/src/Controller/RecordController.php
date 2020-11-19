<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Record;
use App\Repository\RecordRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/records")
 */
class RecordController extends AbstractController
{
    /**
     * @var RecordRepository
     */
    private RecordRepository $recordRepository;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(RecordRepository $recordRepository, SerializerInterface $serializer)
    {
        $this->recordRepository = $recordRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route(path="", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="List of Records",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Record::class))
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $records = $this->recordRepository->findAll();

        return new JsonResponse(
            $this->serializer->serialize(['records' => $records], 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route(path="/{id}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns a Record",
     *     @OA\JsonContent(ref=@Model(type=Record::class))
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     @OA\Schema(type="integer")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $record = $this->recordRepository->find($request->get('id'));

        if (!$record) {
            return new JsonResponse(
                $this->serializer->serialize(['message' => 'Record not found'], 'json'),
                Response::HTTP_NOT_FOUND,
                [],
                true
            );
        }

        return new JsonResponse(
            $this->serializer->serialize($record, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
