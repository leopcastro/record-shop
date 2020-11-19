<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DataFixtures\RecordFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecordControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    public function testList()
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $this->client->request('GET', '/api/records');

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $serializer = self::$container->get('serializer');

        $recordsList = [
            'records' => [
                $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE),
                $referenceRepository->getReference(RecordFixtures::DARK_SIDE_NO_RELEASE_REFERENCE)
            ]
        ];

        $expectedResponse = $serializer->serialize($recordsList, 'json');

        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testShow()
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $appetiteRecord = $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE);

        $this->client->request('GET', '/api/records/' . $appetiteRecord->getId());

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $serializer = self::$container->get('serializer');

        $expectedResponse = $serializer->serialize($appetiteRecord, 'json');

        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function testShowNotFound()
    {
        $this->loadFixtures([]);

        $this->client->request('GET', '/api/records/1');

        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"message":"Record not found"}', $response->getContent());
    }
}
