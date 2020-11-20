<?php

namespace App\DataFixtures;

use App\Entity\Record;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RecordFixtures extends Fixture
{
    public const APPETITE_REFERENCE = 'appetite';
    public const DARK_SIDE_NO_RELEASE_REFERENCE = 'darkSide';
    public const ILLUSION_REFERENCE = 'illusion';

    private ObjectManager $objectManager;

    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;

        $appetiteRecord = new Record('Appetite for Destruction', 'Guns N\' Roses', 14.99);
        $appetiteRecord->setReleasedYear(1987);
        $this->persistRecord($appetiteRecord, self::APPETITE_REFERENCE);

        $darkSideNoReleaseRecord = new Record('The Dark Side of the Moon', 'Pink Floyd', 17.99);
        $this->persistRecord($darkSideNoReleaseRecord, self::DARK_SIDE_NO_RELEASE_REFERENCE);


        $illusionRecord = new Record('Use Your Illusion I', 'Guns N\' Roses', 10.99);
        $illusionRecord->setReleasedYear(1991);
        $this->persistRecord($illusionRecord, self::ILLUSION_REFERENCE);

        $manager->flush();
    }

    /**
     * @param Record $appetiteRecord
     * @param string $reference
     */
    private function persistRecord(Record $appetiteRecord, string $reference): void
    {
        $this->setReference($reference, $appetiteRecord);

        $this->objectManager->persist($appetiteRecord);
    }
}
