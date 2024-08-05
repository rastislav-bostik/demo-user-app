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
        static::assertCount(0, $response->toArray(throw: false)['hydra:member']);
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
        static::assertCount(5, $response->toArray(throw: false)['hydra:member']);
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
        // one only user record should be returned due to pagination setup
        static::assertCount(1, $response->toArray(throw: false)['hydra:member']);
    }

    public function testGetFirstPageOfPopulatedPaginatedList(): void
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
                'page-size'   => 2,
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
            'hydra:view'        =>  [
                '@id'         => '/api/users?page-size=2&page-number=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?page-size=2&page-number=1',
                'hydra:last'  => '/api/users?page-size=2&page-number=3',
                'hydra:next'  => '/api/users?page-size=2&page-number=2',
            ],
        ]);
        // two user records should be returned due to pagination setup
        static::assertCount(2, $response->toArray(throw: false)['hydra:member']);
    }

    public function testGetLastPageOfPopulatedPaginatedList(): void
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
                'page-size'   => 2,
                'page-number' => 3,
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
            'hydra:view'        =>  [
                '@id'         => '/api/users?page-size=2&page-number=3',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?page-size=2&page-number=1',
                'hydra:last'  => '/api/users?page-size=2&page-number=3'
            ],
        ]);
        // one only user record should be returned due to pagination setup
        static::assertCount(1, $response->toArray(throw: false)['hydra:member']);
    }

    public function testGetOutOfRangePageOfPopulatedPaginatedList(): void
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
                'page-size'   => 2,
                'page-number' => 4, // there is no page 4 with 5 records counting overall testing set and 2 records per page
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
            'hydra:view'        =>  [
                '@id'         => '/api/users?page-size=2&page-number=4',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?page-size=2&page-number=1',
                'hydra:last'  => '/api/users?page-size=2&page-number=3'
            ],
            'hydra:member'    => [],
        ]);
        // one only user record should be returned due to pagination setup
        static::assertCount(0, $response->toArray(throw: false)['hydra:member']);
    }

    public function testMissingPageSizeAttributeOfPaginationSetup(): void
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
                // page count is the only one explicitly set
                'page-number' => 1,
                // page size is implicitly set to 10 
                // by default in the User entity class
                // page-size  => 10,
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
            // 'hydra:view'        =>  [
            //     '@id'         => '/api/users?page-number=1',
            //     '@type'       => 'hydra:PartialCollectionView',
            //     'hydra:first' => '/api/users?page-number=1',
            //     'hydra:last'  => '/api/users?&page-number=1'
            // ],
            'hydra:member'    => [],
        ]);
        // all 5 user records should be returned due to pagination setup
        static::assertCount(5, $response->toArray(throw: false)['hydra:member']);
        // as the IMPLICIT PAGE SIZE is BIGGER THAN OVERALL USERS COUNT
        // the pagination 'hydra:view' entry is should not be rendered
        static::assertArrayNotHasKey('hydra:view', $response->toArray(throw: false)['hydra:member']);
    }

    public function testMissingPageNumberAttributeOfPaginationSetup(): void
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
                // page size is the only one
                // set explicitly in this test
                'page-size' => 10,
                // the page number is implicitly set to 1
                // in this test
                // page-number => 1
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
            // 'hydra:view'        =>  [
            //     '@id'         => '/api/users?page-number=1',
            //     '@type'       => 'hydra:PartialCollectionView',
            //     'hydra:first' => '/api/users?page-number=1',
            //     'hydra:last'  => '/api/users?&page-number=1'
            // ],
            'hydra:member'    => [],
        ]);
        // all 5 user records should be returned due to pagination setup
        static::assertCount(5, $response->toArray(throw: false)['hydra:member']);
        // as the IMPLICIT PAGE SIZE is BIGGER THAN OVERALL USERS COUNT
        // the pagination 'hydra:view' entry is should not be rendered
        static::assertArrayNotHasKey('hydra:view', $response->toArray(throw: false)['hydra:member']);
    }

    public function testAsNegativeIntMalformedPageSizeAttributeOfPaginationSetup(): void
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
                'page-size'   => -1, // malformed page-size value
                'page-number' => 123
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
    }

    public function testAsNegativeIntMalformedPageNumberAttributeOfPaginationSetup(): void
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
                'page-size'   => 123,
                'page-number' => -1 // malformed page number value
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
    }

    public function testAsZeroIntMalformedPageSizeAttributeOfPaginationSetup(): void
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
                'page-size'   => 0, // malformed page-size value
                'page-number' => 123
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
    }

    public function testAsZeroIntMalformedPageNumberAttributeOfPaginationSetup(): void
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
                'page-size'   => 123,
                'page-number' => 0 // malformed page number value
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
    }

    
    public function testAsFloatMalformedPageSizeAttributeOfPaginationSetup(): void
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
                'page-size'   => 0.123, // malformed page-size value
                'page-number' => 123
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
    }

    public function testAsFloatMalformedPageNumberAttributeOfPaginationSetup(): void
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
                'page-size'   => 123,
                'page-number' => 0.123 // malformed page number value
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
    }

    public function testAsStringMalformedPageSizeAttributeOfPaginationSetup(): void
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
                'page-size'   => 'null', // malformed page-size value
                'page-number' => 123
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
    }

    public function testAsStringMalformedPageNumberAttributeOfPaginationSetup(): void
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
                'page-size'   => 123,
                'page-number' => 'null' // malformed page number value
            ],
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        // there should be no hydra member field
        static::assertArrayHasKey('hydra:title', $response->toArray(throw: false));
        static::assertArrayHasKey('hydra:description', $response->toArray(throw: false));
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
        static::assertCount(2, $response->toArray(throw: false)['hydra:member']);
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
        static::assertCount(2, $response->toArray(throw: false)['hydra:member']);
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
        static::assertCount(2, $response->toArray(throw: false)['hydra:member']);
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
        static::assertCount(1, $response->toArray(throw: false)['hydra:member']);
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
        static::assertCount(1, $response->toArray(throw: false)['hydra:member']);
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