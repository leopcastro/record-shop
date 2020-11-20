<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Record;
use App\Repository\RecordRepository;
use App\RequestParameters\Pagination;
use App\RequestParameters\Validatable;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/records")
 */
class RecordController extends ApiController
{
    /**
     * @var RecordRepository
     */
    private RecordRepository $recordRepository;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    public function __construct(
        RecordRepository $recordRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($serializer);

        $this->recordRepository = $recordRepository;
        $this->validator = $validator;
    }

    /**
     * @Route(path="", methods={"GET"})
     *
     * @OA\Parameter(
     *     name="offset",
     *     in="query",
     *     @OA\Schema(type="integer", minimum="0"),
     *     description="Offset from the beginning of the Record list."
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     @OA\Schema(type="integer", minimum="0", maximum="100", default="10"),
     *     description="Maximum number of Records returned."
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="List of Records",
     *     @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="records",
     *                   type="array",
     *                   @OA\Items(ref=@Model(type=Record::class))
     *               )
     *           )
     *       )
     * )
     *
     * @OA\Response(
     *     response="400",
     *     description="Validation Error",
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="validationErrors",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="field",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="message",
     *                          type="string"
     *                      )
     *                  )
     *              )
     *          )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $queryParams = $request->query->all();

        $pagination = new Pagination($queryParams['offset'] ?? null, $queryParams['limit'] ?? 10);

        $validationErrors = $this->validateParameters($pagination);

        if ($validationErrors) {
            return $this->getResponse(['validationErrors' => $validationErrors], Response::HTTP_BAD_REQUEST);
        }

        $records = $this->recordRepository->findAllPaginated($pagination);

        return $this->getResponse(['records' => $records], Response::HTTP_OK);
    }

    /**
     * @Route(path="/{id}", methods={"GET"})
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns a Record",
     *     @OA\JsonContent(ref=@Model(type=Record::class))
     * )
     * @OA\Response(
     *     response=404,
     *     description="Returns a Record",
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *     )
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
            return $this->getResponse(['message' => 'Record not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->getResponse($record, Response::HTTP_OK);
    }

    /**
     * @param Validatable $validatable
     *
     * @return array
     */
    private function validateParameters(Validatable $validatable): array
    {
        $violationList = $this->validator->validate($validatable);

        $validationErrors = [];

        foreach ($violationList as $violation) {
            $validationErrors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage()
            ];
        }

        return $validationErrors;
    }
}
