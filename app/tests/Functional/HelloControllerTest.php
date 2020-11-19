<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DataFixtures\HelloFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HelloControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testGetHellos()
    {
        $client = static::createClient();

        $referenceRepository = $this->loadFixtures([HelloFixtures::class])->getReferenceRepository();

        $client->request('GET', '/api/hello');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse()->getContent();

        $expectedHello = $referenceRepository->getReference('hello-1');

        $serializer = self::$container->get('serializer');

        $this->assertEquals($serializer->serialize([$expectedHello], 'json'), $response);
    }
}
