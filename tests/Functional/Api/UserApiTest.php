<?php

namespace App\Tests\Functional\Api;

use App\Tests\DatabasePrimer;
use App\DataFixtures\Doctrine\UserFixtures;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

/**
 * Tests of user API backend functionality
 */
class UserApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        // boot & pick kernel instance
        $kernel = self::bootKernel();

        // rebuilding in-memory testing database schema
        // before every single test as the database
        // cease to exist as soon as the connection is
        // closead after each test case
        DatabasePrimer::prime($kernel);
    }

    public function testGetEmptyList(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ]
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 0,
            'hydra:member'     => [],
        ]);
        // no record should be returned
        static::assertCount(0, $response->toArray()['hydra:member']);
    }

    public function testGetPopulatedList(): void
    {
        // load fixture cotaining tiny basic set of 5 users
        $this->loadFixtures([
            UserFixtures::class
        ]);

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ]
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 5,
        ]);
        // implicit pagination of the class is set to 5
        // so we should get all 5 records of the tiny user
        // set rendered in the response
        static::assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testGetPopulatedPaginatedList(): void
    {
        // load fixture cotaining tiny basic set of 5 users
        $this->loadFixtures([
            UserFixtures::class
        ]);

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ],
            'query' => [
                'page-size'   => 1,
                'page-number' => 1,
            ],
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 5,
            'hydra:member'     => [
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User A',
                    'email'   => 'test.user.A@foo.local',
                    'gender'  => 'FEMALE',
                    'roles'   => [
                        'ADMIN',
                        'WORKER'
                    ],
                    'active'  => true
                ],
            ],
            'hydra:view'        =>  [
                '@id'         => '/api/users?page-size=1&page-number=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?page-size=1&page-number=1',
                'hydra:last'  => '/api/users?page-size=1&page-number=5',
                'hydra:next'  => '/api/users?page-size=1&page-number=2',
            ],
        ]);
    }

    public function testGetPopulatedPaginatedListOrderedBySurnameAscending(): void
    {
        // load fixture cotaining tiny basic set of 5 users
        $this->loadFixtures([
            UserFixtures::class
        ]);

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ],
            'query' => [
                'order-by[surname]' => 'asc',
                'page-size'         => 2,
                'page-number'       => 1,
            ],
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 5,
            'hydra:member'     => [
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User A',
                    'email'   => 'test.user.A@foo.local',
                    'gender'  => 'FEMALE',
                    'roles'   => [
                        'ADMIN',
                        'WORKER'
                    ],
                    'active'  => true
                ],
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User B',
                    'email'   => 'test.user.B@foo.local',
                    'gender'  => 'MALE',
                    'roles'   => [
                        'USER'
                    ],
                    'active'  => true
                ],
            ],
            'hydra:view'        =>  [
                '@id'         => '/api/users?'.urlencode('order-by[surname]').'=asc&page-size=2&page-number=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?'.urlencode('order-by[surname]').'=asc&page-size=2&page-number=1',
                'hydra:last'  => '/api/users?'.urlencode('order-by[surname]').'=asc&page-size=2&page-number=3',
                'hydra:next'  => '/api/users?'.urlencode('order-by[surname]').'=asc&page-size=2&page-number=2'
            ],
        ]);
        // two user records should be returned due to pagination setup
        static::assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testGetPopulatedPaginatedListOrderedBySurnameDescending(): void
    {
        // load fixture cotaining tiny basic set of 5 users
        $this->loadFixtures([
            UserFixtures::class
        ]);

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ],
            'query' => [
                'order-by[surname]' => 'desc',
                'page-size'         => 2,
                'page-number'       => 1,
            ],
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 5,
            'hydra:member'     => [
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User E',
                    'email'   => 'test.user.E@foo.local',
                    'gender'  => 'FEMALE',
                    'roles'   => [
                        'USER',
                        'ADMIN',
                        'WORKER'
                    ],
                    'note'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.',
                    'active'  => false
                ],
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User D',
                    'email'   => 'test.user.D@foo.local',
                    'gender'  => 'MALE',
                    'roles'   => [
                        'USER',
                        'ADMIN'
                    ],
                    'note'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas placerat orci eget sem consectetur faucibus.',
                    'active'  => true
                ],
            ],
            'hydra:view'        =>  [
                '@id'         => '/api/users?'.urlencode('order-by[surname]').'=desc&page-size=2&page-number=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?'.urlencode('order-by[surname]').'=desc&page-size=2&page-number=1',
                'hydra:last'  => '/api/users?'.urlencode('order-by[surname]').'=desc&page-size=2&page-number=3',
                'hydra:next'  => '/api/users?'.urlencode('order-by[surname]').'=desc&page-size=2&page-number=2'
            ],
        ]);
        // two user records should be returned due to pagination setup
        static::assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testGetPopulatedListFilteredByGenderOrderedBySurnameAscending(): void 
    {
        // load fixture cotaining tiny basic set of 5 users
        $this->loadFixtures([
            UserFixtures::class
        ]);

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ],
            'query' => [
                'gender'            => 'MALE',
                'order-by[surname]' => 'asc'
            ],
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 2,
            'hydra:member'     => [
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User B',
                    'email'   => 'test.user.B@foo.local',
                    'gender'  => 'MALE',
                    'roles'   => [
                        'USER'
                    ],
                    'active'  => true
                ],
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User D',
                    'email'   => 'test.user.D@foo.local',
                    'gender'  => 'MALE',
                    'roles'   => [
                        'USER'
                    ],
                    'active'  => true
                ],
            ],
            'hydra:view'        =>  [
                '@id'         => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=asc',
                '@type'       => 'hydra:PartialCollectionView'
            ],
        ]);
        // two user records should be returned due to pagination setup
        static::assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testGetPopulatedPaginatedListFilteredByGenderOrderedBySurnameAscending(): void 
    {
        // load fixture cotaining tiny basic set of 5 users
        $this->loadFixtures([
            UserFixtures::class
        ]);

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ],
            'query' => [
                'gender'            => 'MALE',
                'order-by[surname]' => 'asc',
                'page-size'         => 1,
                'page-number'       => 1,
            ],
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 2,
            'hydra:member'     => [
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User B',
                    'email'   => 'test.user.B@foo.local',
                    'gender'  => 'MALE',
                    'roles'   => [
                        'USER'
                    ],
                    'active'  => true
                ],
            ],
            'hydra:view'        =>  [
                '@id'         => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=asc&page-size=1&page-number=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=asc&page-size=1&page-number=1',
                'hydra:last'  => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=asc&page-size=1&page-number=2',
                'hydra:next'  => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=asc&page-size=1&page-number=2'
            ],
        ]);
        // one only user record should be returned due to pagination setup
        static::assertCount(1, $response->toArray()['hydra:member']);
    }
    
    public function testGetPopulatedPaginatedListFilteredByGenderOrderedBySurnameDescending(): void 
    {
        // load fixture cotaining tiny basic set of 5 users
        $this->loadFixtures([
            UserFixtures::class
        ]);

        // call the list users API endpoint
        $response = static::createClient()->request('GET', '/api/users', [
            'headers' => [
                'Accept' => 'application/ld+json'
            ],
            'query' => [
                'gender'            => 'MALE',
                'order-by[surname]' => 'desc',
                'page-size'         => 1,
                'page-number'       => 1,
            ],
        ]);

        static::assertResponseStatusCodeSame(200);
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@context'         => '/api/contexts/User',
            '@id'              => '/api/users',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 2,
            'hydra:member'     => [
                [
                    '@type'   => 'User',
                    'name'    => 'Test',
                    'surname' => 'User D',
                    'email'   => 'test.user.D@foo.local',
                    'gender'  => 'MALE',
                    'roles'   => [
                        'USER'
                    ],
                    'active'  => true
                ],
            ],
            'hydra:view'        =>  [
                '@id'         => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=desc&page-size=1&page-number=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=desc&page-size=1&page-number=1',
                'hydra:last'  => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=desc&page-size=1&page-number=2',
                'hydra:next'  => '/api/users?gender=MALE&'.urlencode('order-by[surname]').'=desc&page-size=1&page-number=2'
            ],
        ]);
        // one only user record should be returned due to pagination setup
        static::assertCount(1, $response->toArray()['hydra:member']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Load set of basic testing fixtures
     * usefull mostly for listing, filtering,
     * sorting and pagination testing purposes.
     * 
     * @param string[] $fixtureClassNames
     * @return void
     */
    protected function loadFixtures(array $fixturesClassNames): void
    {
        // load set of basic fixtures
        $databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures($fixturesClassNames);
    }

    /**
     * Clean the testing database
     * 
     * @return void
     */
    protected function cleanDatabase(): void
    {
        // load set of basic fixtures
        $databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([]);
    }
}