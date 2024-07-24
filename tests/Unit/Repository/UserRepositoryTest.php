<?php

// tests/Unit/Repository/UserRepositoryTest.php
namespace App\Tests\Unit\Repository;

use App\Entity\Gender;
use App\Entity\Role;
use App\Entity\User;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests of basic user repository functionality
 */
class UserRepositoryTest extends KernelTestCase
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

    public function testSearchAllRecordsOverEmptyDataset(): void
    {
        // and try to fetch it
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        static::assertSame($users, []);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
