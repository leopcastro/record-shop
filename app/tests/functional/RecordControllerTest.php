<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\DataFixtures\RecordFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecordControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testGetRecords()
    {
        $client = static::createClient();

        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $client->request('GET', '/api/record');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $serializer = self::$container->get('serializer');

        $expectedResponse = $serializer->serialize(
            [
                $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE),
                $referenceRepository->getReference(RecordFixtures::DARK_SIDE_NO_RELEASE_REFERENCE)
            ],
            'json'
        );

        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testGetRecord()
    {
        $client = static::createClient();

        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $appetiteRecord = $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE);

        $client->request('GET', '/api/record/' . $appetiteRecord->getId());

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $serializer = self::$container->get('serializer');

        $expectedResponse = $serializer->serialize($appetiteRecord, 'json');

        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testGetRecordNotFound()
    {
        $client = static::createClient();

        $this->loadFixtures([]);

        $client->request('GET', '/api/record/1');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
