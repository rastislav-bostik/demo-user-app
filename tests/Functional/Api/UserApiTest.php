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

        // load basic user data fixtures
        $this->loadFixtures();
    }

    public function testGetEmptyList(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
    }

    public function testGetPopulatedList(): void
    {
        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
    }

    public function testGetPopulatedPaginatedList(): void
    {
        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
    }

    public function testGetPopulatedPaginatedListOrderedBySurnameDescending(): void
    {
        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
    }

    public function testGetPopulatedListFilteredByGenderOrderedBySurnameAscending(): void 
    {
        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
    }

    public function testGetPopulatedPaginatedListFilteredByGenderOrderedBySurnameAscending(): void 
    {
        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
    }
    
    public function testGetPopulatedPaginatedListFilteredByGenderOrderedBySurnameDescending(): void 
    {
        // call the list users API endpoint
        static::createClient()->request('GET', '/api/users', [
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
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Load set of basic user fixtures
     * usefull mostly for listing, filtering,
     * sorting and pagination testing purposes.
     * 
     * @return void
     */
    protected function loadFixtures(): void
    {
        // load set of basic fixtures
        $databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            UserFixtures::class
        ]);
    }

    /**
     * Unload all fixtures from the testing database
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