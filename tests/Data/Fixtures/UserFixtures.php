<?php

namespace App\Tests\Data\Fixtures;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\Gender;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    /**
     * Create basic set of 5 users
     * with various combination of 
     * attribute values
     * 
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $userA = new User();
        $userA->setName('Test');
        $userA->setSurname('User A');
        $userA->setEmail('test.user.A@foo.local');
        $userA->setRoles([Role::ADMIN, Role::WORKER]);
        $userA->setGender(Gender::FEMALE);
        $userA->setActive(true);
        $manager->persist($userA);

        $userB = new User();
        $userB->setName('Test');
        $userB->setSurname('User B');
        $userB->setEmail('test.user.B@foo.local');
        $userB->setRoles([Role::USER]);
        $userB->setGender(Gender::MALE);
        $userB->setActive(true);
        $manager->persist($userB);

        $userC = new User();
        $userC->setName('Test');
        $userC->setSurname('User C');
        $userC->setEmail('test.user.C@foo.local');
        $userC->setRoles([Role::USER, Role::WORKER]);
        $userC->setGender(Gender::FEMALE);
        $userC->setActive(true);
        $manager->persist($userC);

        $userD = new User();
        $userD->setName('Test');
        $userD->setSurname('User D');
        $userD->setEmail('test.user.D@foo.local');
        $userD->setRoles([Role::USER, Role::ADMIN]);
        $userD->setGender(Gender::MALE);
        $userD->setActive(true);
        $userD->setNote('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.');
        $manager->persist($userD);

        $userE = new User();
        $userE->setName('Test');
        $userE->setSurname('User E');
        $userE->setEmail('test.user.E@foo.local');
        $userE->setRoles([Role::USER, Role::ADMIN, Role::WORKER]);
        $userE->setGender(Gender::FEMALE);
        $userE->setActive(false);
        $userE->setNote('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.');
        $manager->persist($userE);

        $manager->flush();
    }
}
