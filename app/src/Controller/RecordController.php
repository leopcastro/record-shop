<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Record;
use App\Repository\RecordRepository;
use App\RequestParameters\Pagination;
use App\RequestParameters\RecordParameters;
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
            return $this->getValidationErrorResponse($validationErrors, Response::HTTP_BAD_REQUEST);
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
     * @Route(path="", methods={"POST"})
     * @OA\RequestBody(
     *     description="Fields avaialble to create a Record",
     *     required=true,
     *     @OA\MediaType(
     *          mediaType="application/x-www-form-urlencoded",
     *          @OA\Schema(
     *              required={"name", "artist", "price"},
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  maxLength=100
     *              ),
     *             @OA\Property(
     *                  property="artist",
     *                  type="string",
     *                  maxLength=100
     *              ),
     *              @OA\Property(
     *                  property="price",
     *                  type="number",
     *                  maximum="9999999",
     *                  minimum="0",
     *                  description="Integer or maximum 2 decimals"
     *              ),
     *              @OA\Property(
     *                  property="releasedYear",
     *                  type="integer",
     *                  maximum="9999",
     *                  minimum="0",
     *                  description="Current year is the maximum value"
     *              )
     *          )
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Creates a Record and returns it.",
     *     @OA\JsonContent(ref=@Model(type=Record::class))
     * )
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
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $parameters = $request->request->all();

        $recordParameters = new RecordParameters(
            $parameters['name'] ?? '',
            $parameters['artist'] ?? '',
            $parameters['price'] ?? null,
            $parameters['releasedYear'] ?? null
        );

        $validationsErrors = $this->validateParameters($recordParameters);

        if ($validationsErrors) {
            return $this->getValidationErrorResponse($validationsErrors, Response::HTTP_BAD_REQUEST);
        }

        $newRecord = new Record(
            $recordParameters->getName(),
            $recordParameters->getArtist(),
            $recordParameters->getPrice()
        );

        $newRecord->setReleasedYear($recordParameters->getReleasedYear());

        $doctrineManager = $this->getDoctrine()->getManager();

        $doctrineManager->persist($newRecord);
        $doctrineManager->flush();

        return $this->getResponse($newRecord, Response::HTTP_CREATED);
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
