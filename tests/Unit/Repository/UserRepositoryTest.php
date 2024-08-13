<?php

// tests/Unit/Repository/UserRepositoryTest.php
namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Tests\DatabasePrimer;
use App\Tests\FixturesLoadingTrait;
use App\Tests\Data\Fixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;

/**
 * Tests of basic user repository functionality
 */
class UserRepositoryTest extends KernelTestCase
{
    use FixturesLoadingTrait;

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
        // make sure there are no user records
        self::cleanDatabase();

        // and try to fetch it
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        static::assertSame($users, []);
    }

    public function testSearchAllRecordsOverPopulatedDataset(): void
    {
        // load basic users set fixture
        self::loadFixtures([
            UserFixtures::class,
        ]);

        // load basic user set fixtures
        // and try to fetch it
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        static::assertSame(count($users), 5);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
