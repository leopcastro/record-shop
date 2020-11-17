<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HelloRepository;
use App\Entity\Hello;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Class HelloController
 * @Route("/hello")
 */
class HelloController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/")
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
     * @return View
     */
    public function getHello(HelloRepository $helloRepository): View
    {
        return $this->view($helloRepository->findAll());
    }
}
