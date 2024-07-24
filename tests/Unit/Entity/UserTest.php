<?php

// tests/Unit/Entity/UserTest.php
namespace App\Tests\Unit\Repository;

use App\Entity\Gender;
use App\Entity\Role;
use App\Entity\User;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests covering basic user entity create, update and delete 
 * operations functionality
 */
class UserTest extends KernelTestCase
{
    /**
     * ORM entity manager
     * @var \Doctrine\ORM\EntityManager
     */
    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        // boot & pick kernel instance
        $kernel = self::bootKernel();

        // create testing database from scratch
        // before every single test
        DatabasePrimer::prime($kernel);

        // fetch & store Doctrine's entity manager
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testCreateUserWithoutNote(): void
    {
        // create user object
        $userA = new User();
        $userA->setName('Test');
        $userA->setSurname('User');
        $userA->setEmail('test.user.A@foo.local');
        $userA->setRoles([Role::ADMIN, Role::WORKER]);
        $userA->setGender(Gender::FEMALE);
        $userA->setActive(true);

        // store user object
        $this->entityManager->persist($userA);
        $this->entityManager->flush();

        // and try to fetch it
        $fetchedUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test.user.A@foo.local']);

            static::assertNotNull($fetchedUser);
            static::assertEquals($fetchedUser->getId(), $userA->getId());
            static::assertEquals($fetchedUser->getName(), 'Test');
            static::assertEquals($fetchedUser->getSurname(), 'User');
            static::assertEquals($fetchedUser->getEmail(), 'test.user.A@foo.local');
            static::assertEquals($fetchedUser->getRoles(), [Role::ADMIN, Role::WORKER]);
            static::assertEquals($fetchedUser->getGender(), Gender::FEMALE);
            static::assertNull($fetchedUser->getNote());
            static::assertSame($fetchedUser->isActive(), true);
    }

    public function testCreateUserWithNote(): void
    {
        // create user object
        $userA = new User();
        $userA->setName('Test');
        $userA->setSurname('User');
        $userA->setEmail('test.user.A@foo.local');
        $userA->setRoles([Role::ADMIN, Role::WORKER]);
        $userA->setGender(Gender::FEMALE);
        $userA->setNote('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.');
        $userA->setActive(true);

        // store user object
        $this->entityManager->persist($userA);
        $this->entityManager->flush();

        // and try to fetch it
        $fetchedUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test.user.A@foo.local']);

        static::assertNotNull($fetchedUser);
        static::assertEquals($fetchedUser->getId(), $userA->getId());
        static::assertEquals($fetchedUser->getName(), 'Test');
        static::assertEquals($fetchedUser->getSurname(), 'User');
        static::assertEquals($fetchedUser->getEmail(), 'test.user.A@foo.local');
        static::assertEquals($fetchedUser->getRoles(), [Role::ADMIN, Role::WORKER]);
        static::assertEquals($fetchedUser->getGender(), Gender::FEMALE);
        static::assertEquals($fetchedUser->getNote(), 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.');
        static::assertSame($fetchedUser->isActive(), true);
    }

    public function testUpdateUserName()
    {
        // TODO
    }


    public function testUpdateUserSurname()
    {
        // TODO
    }

    public function testUpdateUserEmail()
    {
        // TODO
    }

    public function testUpdateUserRoles()
    {
        // TODO
    }

    public function testUpdateUserGender()
    {
        // TODO
    }

    public function testUpdateUserActiveFlag()
    {
        // TODO
    }

    public function testUpdateUserNote()
    {
        // TODO
    }

    public function testDeleteUser()
    {
        // TODO
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
