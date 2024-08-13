<?php

namespace App\Tests\Functional\Api;

use App\Entity\User;
use App\Tests\DatabasePrimer;
use App\Tests\FixturesLoadingTrait;
use App\Tests\Data\Fixtures\UserFixtures;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

/**
 * Tests of delete user backend API endopoint
 */
class UserDeleteApiTest extends ApiTestCase
{
    use FixturesLoadingTrait;

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

    public function testDeleteNonExistingUser(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // call the get user API endpoint
        $response = static::createClient()->request('DELETE', '/api/users/none');

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

    public function testDeleteExistingUser(): void
    {
        // remove all data from database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // get IRI address for tested resource
        $iri = self::findIriBy(User::class, ['email' => UserFixtures::EMAIL_USER_A]);

        // call the PUT full user update API endpoint
        static::createClient()->request('DELETE', $iri);

        static::assertResponseStatusCodeSame(204); // expecting 200 - No Content
        static::assertResponseNotHasHeader('content-type');

        static::assertNull(
            // through the container, you can access all your services from the tests, including the ORM, the mailer, remote API clients...
            static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => UserFixtures::EMAIL_USER_A])
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}