<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DataFixtures\RecordFixtures;
use App\Entity\Record;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class RecordControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    private SerializerInterface $serializer;

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->serializer = $serializer = self::$container->get('serializer');
    }

    public function testListNoParameter()
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $this->client->request('GET', '/api/records');

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $expectedRecords = [
            $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE),
            $referenceRepository->getReference(RecordFixtures::DARK_SIDE_NO_RELEASE_REFERENCE)
        ];

        $expectedResponse = $this->getListSerializedResponse($expectedRecords);

        $this->assertEquals($expectedResponse, $response->getContent());
    }

    /**
     * @dataProvider listPaginatedDataProvider
     *
     * @param string $paginationParams
     * @param string $expectedRecordReference
     */
    public function testListPaginated(string $paginationParams, string $expectedRecordReference)
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $this->client->request('GET', '/api/records?' . $paginationParams);

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(1, count(json_decode($response->getContent())->records));

        $expectedRecords = [$referenceRepository->getReference($expectedRecordReference)];

        $expectedResponse = $this->getListSerializedResponse($expectedRecords);

        $this->assertEquals($expectedResponse, $response->getContent());
    }

    public function listPaginatedDataProvider(): array
    {
        return [
            ['limit=1', RecordFixtures::APPETITE_REFERENCE],
            ['limit=1&offset=1', RecordFixtures::DARK_SIDE_NO_RELEASE_REFERENCE]
        ];
    }

    public function testListPaginationValidationReturnsError()
    {
        $this->client->request('GET', '/api/records?limit=abc');

        $response = $this->client->getResponse();

        $responseContent = json_decode($response->getContent());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertObjectHasAttribute('validationErrors', $responseContent);
        $this->assertEquals('limit', $responseContent->validationErrors[0]->field);
        $this->assertObjectHasAttribute('message', $responseContent->validationErrors[0]);
    }

    public function testShow()
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $appetiteRecord = $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE);

        $this->client->request('GET', '/api/records/' . $appetiteRecord->getId());

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $expectedResponse = $this->serializer->serialize($appetiteRecord, 'json');

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

    public function testCreate()
    {
        $this->loadFixtures([]);

        $record = $this->getValidRecordAsArray();

        $this->client->request(
            'POST',
            '/api/records',
            $record,
        );

        $response = $this->client->getResponse();

        $returnedRecord = json_decode($response->getContent());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(1, $returnedRecord->id);

        foreach ($record as $field => $value) {
            $this->assertEquals($value, $returnedRecord->$field);
        }
    }

    public function testCreateValidation()
    {
        $this->loadFixtures([]);

        $this->client->request(
            'POST',
            '/api/records',
            [],
        );

        $response = $this->client->getResponse();

        $responseContent = json_decode($response->getContent());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertObjectHasAttribute('validationErrors', $responseContent);
        // Minimum 3 errors, one for each required parameter
        $this->assertGreaterThanOrEqual(3, count($responseContent->validationErrors));
    }

    public function testUpdate()
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        /** @var Record $appetiteRecordBeforeUpdate */
        $appetiteRecordBeforeUpdate = $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE);

        $record = $this->getValidRecordAsArray();

        $this->client->request(
            'PUT',
            '/api/records/' . $appetiteRecordBeforeUpdate->getId(),
            $record,
        );

        $response = $this->client->getResponse();

        $returnedRecord = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $returnedRecord->id);

        foreach ($record as $field => $value) {
            $this->assertEquals($value, $returnedRecord->$field);
        }
    }

    public function testUpdateNotFound()
    {
        $this->loadFixtures([]);

        $this->client->request(
            'PUT',
            '/api/records/123',
            $this->getValidRecordAsArray(),
        );

        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"message":"Record not found"}', $response->getContent());
    }

    public function testUpdateValidation()
    {
        $this->loadFixtures([]);

        $this->client->request(
            'PUT',
            '/api/records/123',
            [],
        );

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertObjectHasAttribute('validationErrors', $responseContent);
        // Minimum 3 errors, one for each required parameter
        $this->assertGreaterThanOrEqual(3, count($responseContent->validationErrors));
    }

    private function getListSerializedResponse(array $records): string
    {
        $recordsList = ['records' => $records];

        return $this->serializer->serialize($recordsList, 'json');
    }

    /**
     * @return array
     */
    private function getValidRecordAsArray(): array
    {
        return [
            'name' => 'Record Name',
            'artist' => 'Artist Name',
            'price' => 15.99,
            'releasedYear' => '1990'
        ];
    }
}
