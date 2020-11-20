<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HelloRepository;
use App\Entity\Hello;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class HelloController
 * @Route("/hello")
 */
class HelloController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route(path="", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="List of Hello messages",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Hello::class))
     *     )
     * )
     *
     * @param HelloRepository $helloRepository
     *
     * @return JsonResponse
     */
    public function getHello(HelloRepository $helloRepository): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($helloRepository->findAll(), 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
