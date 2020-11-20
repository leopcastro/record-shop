<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Record;
use App\Repository\RecordRepository;
use App\RequestParameters\Pagination;
use App\RequestParameters\RecordFilters;
use App\RequestParameters\RecordParameters;
use App\RequestParameters\Validatable;
use App\Service\RecordService;
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

    /**
     * @var RecordService
     */
    private RecordService $recordService;

    public function __construct(
        RecordRepository $recordRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RecordService $recordService
    ) {
        parent::__construct($serializer);

        $this->recordRepository = $recordRepository;
        $this->validator = $validator;
        $this->recordService = $recordService;
    }

    /**
     * @Route(path="", methods={"GET"})
     *
     * @OA\Parameter(
     *     name="title",
     *     in="query",
     *     @OA\Schema(type="string", maxLength=100),
     *     description="Title of the record to search"
     * )
     * @OA\Parameter(
     *     name="artist",
     *     in="query",
     *     @OA\Schema(type="string", maxLength=100),
     *     description="Artist of the record to search"
     * )
     * * @OA\Parameter(
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
     *     description="List of Records ordered by Artist and Title",
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
        $pagination = new Pagination($request->get('offset'), $request->get('limit'));
        $violations = $this->validateParameters($pagination);

        $filter = new RecordFilters($request->get('title'), $request->get('artist'));
        $violations = array_merge($violations, $this->validateParameters($filter));

        if ($violations) {
            return $this->getValidationErrorResponse($violations, Response::HTTP_BAD_REQUEST);
        }

        $records = $this->recordService->getRecords($pagination, $filter);

        return $this->getResponse(['records' => $records], Response::HTTP_OK);
    }

    /**
     * @Route(path="/{id}", methods={"GET"})
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Record id to return",
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns a Record",
     *     @OA\JsonContent(ref=@Model(type=Record::class))
     * )
     * @OA\Response(
     *     response=404,
     *     description="Not found",
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
        $record = $this->recordService->getRecord((int) $request->get('id'));

        if (!$record) {
            return $this->getNotFoundResponse();
        }

        return $this->getResponse($record, Response::HTTP_OK);
    }

    /**
     * @Route(path="", methods={"POST"})
     *
     * @OA\RequestBody(
     *     description="Fields avaialble to create a Record",
     *     required=true,
     *     @OA\MediaType(
     *          mediaType="application/x-www-form-urlencoded",
     *          @OA\Schema(
     *              required={"title", "artist", "price"},
     *              @OA\Property(
     *                  property="title",
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
     *
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $recordParameters = new RecordParameters(
            $request->get('title'),
            $request->get('artist'),
            $request->get('price'),
            $request->get('releasedYear')
        );

        $validationsErrors = $this->validateParameters($recordParameters);

        if ($validationsErrors) {
            return $this->getValidationErrorResponse($validationsErrors, Response::HTTP_BAD_REQUEST);
        }

        $newRecord = $this->recordService->createRecord($recordParameters);

        return $this->getResponse($newRecord, Response::HTTP_CREATED);
    }

    /**
     * @Route(path="/{id}", methods={"PUT"})
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Record id to update",
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\RequestBody(
     *     description="Fields avaialble to create a Record",
     *     required=true,
     *     @OA\MediaType(
     *          mediaType="application/x-www-form-urlencoded",
     *          @OA\Schema(
     *              required={"title", "artist", "price"},
     *              @OA\Property(
     *                  property="title",
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
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns the updated Record",
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
     * @OA\Response(
     *     response=404,
     *     description="Not found",
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
    public function update(Request $request): JsonResponse
    {
        $recordParameters = new RecordParameters(
            $request->get('title'),
            $request->get('artist'),
            $request->get('price'),
            $request->get('releasedYear')
        );

        $validationsErrors = $this->validateParameters($recordParameters);

        if ($validationsErrors) {
            return $this->getValidationErrorResponse($validationsErrors, Response::HTTP_BAD_REQUEST);
        }

        $updatedRecord = $this->recordService->updateRecord((int) $request->get('id'), $recordParameters);

        if (!$updatedRecord) {
            return $this->getNotFoundResponse();
        }

        return $this->getResponse($updatedRecord, 200);
    }

    /**
     * @Route(path="/{id}", methods={"DELETE"})
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Record id to delete",
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Response(
     *     response=204,
     *     description="Record deleted"
     * ),
     * @OA\Response(
     *     response=404,
     *     description="Not found",
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
    public function delete(Request $request)
    {
        $record = $this->recordService->deleteRecord((int) $request->get('id'));

        if (!$record) {
            return $this->getNotFoundResponse();
        }

        return $this->getResponse(null, Response::HTTP_NO_CONTENT);
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
