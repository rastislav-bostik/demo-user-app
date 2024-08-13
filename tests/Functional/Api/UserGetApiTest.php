<?php

namespace App\Tests\Functional\Api;

use App\Entity\User;
use App\Tests\DatabasePrimer;
use App\Tests\FixturesLoadingTrait;
use App\Tests\Data\Fixtures\UserFixtures;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Tests of get user backend API endopoint
 */
class UserGetApiTest extends ApiTestCase
{
    use FixturesLoadingTrait;
    
    /**
     * Shared object serializer usable
     * for turning entities into associative
     * arrays or JSON strings
     * 
     * @var Serializer
     */
    private static Serializer $serializer;

    public static function setUpBeforeClass(): void {
        // conversion of objects to normalized arrays
        $normalizers = [
            // !!! BEWARE !!!
            // order of normalizers matters
            // start with the most concrete
            // end with the most general
            // (otherwise everything gets normalized by the general normalizer first giving you different than expected results)
            new UidNormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer(), 
        ];
        // conversion of normalized arrays into formats
        $encoders = [
            new JsonEncoder()
        ];

        self::$serializer = new Serializer($normalizers, $encoders);
    }

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

    public function testGetNonExistingUser(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // call the get user API endpoint
        $response = static::createClient()->request('GET', '/api/users/none', [
            'headers' => [
                'Accept'       => 'application/ld+json',
            ]
        ]);

        static::assertResponseStatusCodeSame(404); // expecting 404 - Not Found
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@id'    => '/api/errors/404',
            '@type'  => 'hydra:Error',
            'status' => 404,
            'type'   => '/errors/404',
            'title'  => 'An error occurred',
            'detail' => 'Invalid identifier value or configuration.',
        ]);
    }

    public function testGetExistingUser(): void
    {
        // remove all data from database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // get IRI address for tested resource
        $iri = self::findIriBy(User::class, ['email' => UserFixtures::EMAIL_USER_A]);

        // call the get user API endpoint
        static::createClient()->request('GET', $iri, [
            'headers' => [
                'Accept' => 'application/ld+json',
            ]
        ]);

        // load expected user entity
        $expectedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];
        $expectedData = self::$serializer->normalize($expectedUser);
        // get expected data rid of 'note' attribute if contains NULL value
        if(array_key_exists('note', $expectedData) && is_null($expectedData['note'])) {
            unset($expectedData['note']);
        }

        static::assertResponseStatusCodeSame(200); // expecting 200 - OKs
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains(array_merge(
            [
                '@context' => '/api/contexts/User',
                '@id'      => $iri,
                '@type'    => 'User',
            ],
            $expectedData
        ));
    }

    public function _testGetUserWithInvalidIdentifier(string $identifier): void
    {
        // call the get user API endpoint
        $response = static::createClient()->request('GET', '/api/users/'.$identifier, [
            'headers' => [
                'Accept' => 'application/ld+json',
            ]
        ]);

        static::assertResponseStatusCodeSame(404); // expecting 404 - Not Found
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@id'    => '/api/errors/404',
            '@type'  => 'hydra:Error',
            'status' => 404,
            'type'   => '/errors/404',
            'title'  => 'An error occurred',
            'detail' => 'Invalid identifier value or configuration.',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}