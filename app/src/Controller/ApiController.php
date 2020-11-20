<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

abstract class ApiController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function getResponse($serializable, int $code): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($serializable, 'json'),
            $code,
            [],
            true
        );
    }

    protected function getValidationErrorResponse($serializable, int $code): JsonResponse
    {
        return $this->getResponse(
            ['validationErrors' => $serializable],
            $code
        );
    }
}
