<?php

namespace App\DataFixtures;

use App\Entity\Record;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RecordFixtures extends Fixture
{
    public const APPETITE_REFERENCE = 'appetite';
    public const DARK_SIDE_NO_RELEASE_REFERENCE = 'darkSide';

    public function load(ObjectManager $manager)
    {
        $appetiteRecord = new Record('Appetite for Destruction', 'Guns N\' Roses', 14.99);
        $appetiteRecord->setReleasedYear(1987);

        $this->setReference(self::APPETITE_REFERENCE, $appetiteRecord);

        $manager->persist($appetiteRecord);

        $darkSideNoReleaseRecord = new Record('The Dark Side of the Moon', 'Pink Floyd', 17.99);

        $this->setReference(self::DARK_SIDE_NO_RELEASE_REFERENCE, $darkSideNoReleaseRecord);

        $manager->persist($darkSideNoReleaseRecord);

        $manager->flush();
    }
}
