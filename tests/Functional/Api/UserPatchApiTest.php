<?php

namespace App\Tests\Functional\Api;

use App\Entity\User;
use App\Entity\Gender;
use App\Entity\Role;
use App\Tests\DatabasePrimer;
use App\Tests\FixturesLoadingTrait;
use App\Tests\Data\Fixtures\UserFixtures;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;

/**
 * Tests of update user backend API endopoint
 */
class UserPatchApiTest extends ApiTestCase
{
    use FixturesLoadingTrait;

    /**
     * Default craete user data set 
     * @var array  
     */
    protected const USER_Q_DATA = [
        'name'    => 'Test',
        'surname' => 'User Q',
        'email'   => 'test.user.Q@masters-of-universe.local',
        'gender'  => Gender::MALE,
        'roles'   =>  [
            Role::USER,
            Role::ADMIN,
        ],
        'active'   => true 
    ];

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

    public function testUpdateOnNonExistingUser(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // call the get user API endpoint
        $response = static::createClient()->request('PATCH', '/api/users/none', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => self::USER_Q_DATA,
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

    public function testUpdateWithMissingJsonDataOnExistingUser(): void
    {
        // load user fixtures into testing database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // get user entity to be updated
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        // get link to the API resource representing updated entity
        // (i.e. resource location)
        $iri = self::findIriBy(User::class, ['email' => UserFixtures::EMAIL_USER_A]);

        // call the get user API endpoint
        $response = static::createClient()->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
        ]);

        static::assertResponseStatusCodeSame(400); // expecting 400 - Bad Request
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@id'    => '/api/errors/400',
            '@type'  => 'hydra:Error',
            'status' => 400,
            'type'   => '/errors/400',
            'title'  => 'An error occurred',
            'detail' => 'Syntax error',
        ]);
    }

    public function testUpdateWithNullJsonDataOnExistingUser(): void
    {
        // load user fixtures into testing database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        
        // get user entity to be updated
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        // test bad request
        $this->_testBadRequest(
            user:                 $updatedUser, 
            updateData:           null, 
            expectedErrorMessage: 'Syntax error'
        );
    }

    // TODO - reconsider throwing 400 - Bad Request when empty
    //        update data provided
    public function testUpdateWithEmptyJsonDataOnExistingUser(): void
    {
        // load user fixtures into testing database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // get user entity to be updated
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        self::_testSuccessfullUpdateOfUser($updatedUser, []);
    }

    public function testUpdateIncludingSameIdInJsonBodyAllowedOnExistingUser(): void
    {
        // load user fixtures into testing database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // getting user to be updated directly from used fixtures set
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        // calling update test method body
        $this->_testSuccessfullUpdateOfUser(
            $updatedUser,
            [
                'id' => $updatedUser->getId()
            ]
        );
    }

    public function testUpdateIncludingDifferentIdForbiddenOnExistingUser(): void
    {
        // load user fixtures into testing database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // get link to the API resource representing updated entity
        // (i.e. resource location)
        $iri = self::findIriBy(User::class, ['email' => UserFixtures::EMAIL_USER_A]);

        // call the get user API endpoint
        static::createClient()->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => [
                'id' => Uuid::v7()
            ],
        ]);

        static::assertResponseStatusCodeSame(400); // expecting 400 - Bad Request
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@id'    => '/api/errors/400',
            '@type'  => 'hydra:Error',
            'status' => 400,
            'type'   => '/errors/400',
            'title'  => 'An error occurred',
            'detail' => 'Modification of resource identifier value refused.'
        ]);
    }

    // TODO - test updating of non-existing attributes

    // TODO - test passing invalid values for each attribute

    // TODO - test same value partial upadate setting new for each attribute

    // TODO - test different than current attribute value partial upadate setting new for each attribute

    public function testNewEmailUpdateOnExistingUser(): void
    {
        // remove all data from database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // getting user to be updated directly from used fixtures set
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        // calling update test method body
        $this->_testSuccessfullUpdateOfUser(
            $updatedUser,
            [
                'email' => self::USER_Q_DATA['email']
            ]
        );
    }

    public function testSameEmailUpdateOnExistingUser(): void
    {
        // remove all data from database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // getting user to be updated directly from used fixtures set
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        // calling update test method body
        $this->_testSuccessfullUpdateOfUser(
            $updatedUser,
            [
                'email' => $updatedUser->getEmail()
            ]
        );
    }

    public function testDuplicateEmailUpdateExistingUser(): void
    {
        // remove all data from database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // getting user to be updated directly from used fixtures set
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        // calling update test method body
        $this->_testConstraintViolations(
            $updatedUser,
            [
                'email' => UserFixtures::EMAIL_USER_B,
            ],
            [
                ['propertyPath' => 'email', 'message' => 'This value is already used.']
            ]
        );
    }

    public function testFullUpdateOfExistingUser(): void
    {
        // remove all data from database
        static::loadFixtures([
            UserFixtures::class,
        ]);

        // getting user to be updated directly from used fixtures set
        $updatedUser = UserFixtures::get()[UserFixtures::EMAIL_USER_A];

        // calling update test method body
        $this->_testSuccessfullUpdateOfUser(
            $updatedUser,
            self::USER_Q_DATA
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test successfull update of user data
     * using PATCH update
     * 
     * @param User $user
     * @param array $updateData
     * @return void
     */
    protected function _testSuccessfullUpdateOfUser(User $user, array $updateData): void 
    {
        // get IRI address for tested resource
        $iri = self::findIriBy(User::class, ['email' => $user->getEmail()]);

        // call the PATCH partial user update API endpoint
        static::createClient()->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => $updateData,
        ]);

        // compile expected response data
        $expectedData = $this->_stringifyEnumsInArrays(
            array_merge(
                // original user data
                self::$serializer->normalize($user),
                // updates applied on top
                // of original user's data
                $updateData,
            )
        );
        // get expected data rid of 'note' attribute if contains NULL value
        if(array_key_exists('note', $expectedData) && is_null($expectedData['note'])) {
            unset($expectedData['note']);
        }
       
        static::assertResponseStatusCodeSame(200); // expecting 200 - OKs
        static::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        static::assertJsonContains(array_merge(
            // JSON-LD data
            [
                '@context' => '/api/contexts/User',
                '@id'      => $iri,
                '@type'    => 'User',
            ],
            // expected data of user after update
            $expectedData
        ));
    }

    /**
     * Test bad request to update the user data
     * using PATCH update
     * 
     * @param User $user
     * @param mixed $updateData
     * @param string $expectedErrorMessage
     * @return void
     */
    protected function _testBadRequest(User $user, mixed $updateData, string $expectedErrorMessage): void 
    {
        // get IRI address for tested resource
        $iri = self::findIriBy(User::class, ['email' => $user->getEmail()]);

        // call the PATCH partial user update API endpoint
        static::createClient()->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => $updateData,
        ]);

        // get link to the API resource representing updated entity
        // (i.e. resource location)
        $iri = self::findIriBy(User::class, ['email' => UserFixtures::EMAIL_USER_A]);

        // call the get user API endpoint
        static::createClient()->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => null,
        ]);

        static::assertResponseStatusCodeSame(400); // expecting 400 - Bad Request
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        static::assertJsonContains([
            '@id'    => '/api/errors/400',
            '@type'  => 'hydra:Error',
            'status' => 400,
            'type'   => '/errors/400',
            'title'  => 'An error occurred',
            'detail' => $expectedErrorMessage,
        ]);
    }

    /**
     * Test successfull update of user data
     * using PATCH update
     * 
     * @param User $user
     * @param array $updateData
     * @param array $expectedConstraintViolations
     * @return void
     */
    protected function _testConstraintViolations(User $user, array $updateData, array $expectedConstraintViolations): void 
    {
        // get IRI address for tested resource
        $iri = self::findIriBy(User::class, ['email' => $user->getEmail()]);

        // call the PATCH partial user update API endpoint
        $response = static::createClient()->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => $updateData,
        ]);

        static::assertResponseStatusCodeSame(422); // expecting 422 - Unprocessable Content
        static::assertResponseHeaderSame(
            'content-type', 'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/validation_errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'      => 'ConstraintViolationList',
            'status'     => 422,
            'violations' => $expectedConstraintViolations,
        ]);
        // check overall amount of expected constraint violations
        static::assertCount(count($expectedConstraintViolations), $response->toArray(throw: false)['violations']);
    }
 
    /**
     * Method turning backend enums within provided array data
     * into strings
     * 
     * @param array $rawData
     * @return array
     */
    private function _stringifyEnumsInArrays(array $rawData): array
    {
        return json_decode(json_encode($rawData),true);
    }

}