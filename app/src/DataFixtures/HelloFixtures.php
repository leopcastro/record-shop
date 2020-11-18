<?php

namespace App\DataFixtures;

use App\Entity\Hello;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class HelloFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $hello = new Hello();
        $hello->setMessage('Hello from record shop db!!!!');

        $this->setReference('hello-1', $hello);

        $manager->persist($hello);

        $manager->flush();
    }
}
