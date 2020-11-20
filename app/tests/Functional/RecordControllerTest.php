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

    public function testListNoParameterAndOrdering()
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $this->client->request('GET', '/api/records');

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $expectedRecords = [
            $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE),
            $referenceRepository->getReference(RecordFixtures::ILLUSION_REFERENCE),
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
            ['limit=1&offset=2', RecordFixtures::DARK_SIDE_NO_RELEASE_REFERENCE]
        ];
    }

    /**
     * @dataProvider listFilteredDataProvider
     *
     * @param string $recordReference
     * @param string $urlFilter
     * @param int $expectedQty
     */
    public function testListFiltered(string $recordReference, string $urlFilter, int $expectedQty)
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();
        $expectedRecord = $referenceRepository->getReference($recordReference);

        $this->client->request('GET', '/api/records?' . $urlFilter);

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount($expectedQty, $responseContent->records);
        $this->assertEquals($expectedRecord->getTitle(), $responseContent->records[0]->title);
    }

    public function listFilteredDataProvider()
    {
        return[
            [RecordFixtures::APPETITE_REFERENCE, 'title=appetite', 1],
            [RecordFixtures::DARK_SIDE_NO_RELEASE_REFERENCE, 'artist=floyd', 1],
            [RecordFixtures::APPETITE_REFERENCE, 'artist=roses', 2],
            [RecordFixtures::ILLUSION_REFERENCE, 'title=use%20your&artist=roses', 1]
        ];
    }

    public function testListFilteredNoResult()
    {
        $this->loadFixtures();

        $this->client->request('GET', '/api/records?title=not_existent');

        $response = $this->client->getResponse();
        $responseContent = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(0, $responseContent->records);
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
        $this->loadFixtures();

        $this->client->request('GET', '/api/records/1');

        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"message":"Record not found"}', $response->getContent());
    }

    public function testCreate()
    {
        $this->loadFixtures();

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
        $this->loadFixtures();

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
        $this->loadFixtures();

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
        $this->loadFixtures();

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

    public function testDelete()
    {
        $referenceRepository = $this->loadFixtures([RecordFixtures::class])->getReferenceRepository();

        $appetiteRecord = $referenceRepository->getReference(RecordFixtures::APPETITE_REFERENCE);

        $this->client->request('DELETE', '/api/records/' . $appetiteRecord->getId());

        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testDeleteNotFound()
    {
        $this->loadFixtures();

        $this->client->request('DELETE', '/api/records/123');

        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"message":"Record not found"}', $response->getContent());
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
            'title' => 'Record Title',
            'artist' => 'Artist Name',
            'price' => 15.99,
            'releasedYear' => '1990'
        ];
    }
}
