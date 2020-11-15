<?php

declare(strict_types=1);

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;

/**
 * Class HelloController
 * @Route("/hello")
 */
class HelloController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/")
     */
    public function getHello(): View
    {
        return $this->view(['message' => 'Hello Record Shop']);
    }
}