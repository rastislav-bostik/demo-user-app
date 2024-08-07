<?php

namespace App\Tests\Functional\Api;

use App\Entity\Role;
use App\Entity\Gender;
use App\Tests\DatabasePrimer;
use App\DataFixtures\Doctrine\UserFixtures;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

/**
 * Tests of create user backend API functionality
 */
class UserCreateApiTest extends ApiTestCase
{
    /** @var array Default craete user data set */
    protected const DEFAULT_USER_DATA = [
        'name'    => 'Test',
        'surname' => 'User X',
        'email'   => 'test.user.X@foo.local',
        'gender'  => Gender::MALE,
        'roles'   =>  [
            Role::USER,
            Role::ADMIN,
        ],
        'active'   => true 
    ];

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

    public function testCreateUserWithMissingJsonData(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/ld+json'
            ]
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type',
            'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'  => 'hydra:Error',
            'status' => 400,
            'title'  => 'An error occurred',
            'detail' => 'Syntax error',
        ]);
    }

    public function testCreateUserWithMissingJsonDataAndMissingContentType(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => [
                'Content-Type' => '',
                'Accept'       => 'application/ld+json'
            ]
        ]);

        static::assertResponseStatusCodeSame(415); // expecting HTTP reponse code 415 - Unsupported Media Type 
        static::assertResponseHeaderSame(
            'content-type',
            'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'  => 'hydra:Error',
            'status' => 415,
            'title'  => 'An error occurred',
            'detail' => 'The "Content-Type" header must exist.',
        ]);
    }

    public function testCreateUserWithMissingJsonDataAndWrongContentType(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept'       => 'application/ld+json'
            ]
        ]);

        static::assertResponseStatusCodeSame(415); // expecting HTTP reponse code 415 - Unsupported Media Type 
        static::assertResponseHeaderSame(
            'content-type',
            'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'  => 'hydra:Error',
            'status' => 415,
            'title'  => 'An error occurred',
        ]);
        static::assertStringContainsString(
            'The content-type "application/x-www-form-urlencoded" is not supported.',
            $response->toArray(throw: false)['detail']
        );
    }

    public function testCreateUserWithEmptyJsonData(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'json'    => [],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        static::assertResponseIsUnprocessable(); // expecting HTTP reponse code 422 - Unprocessable
        static::assertResponseHeaderSame(
            'content-type',
            'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/validation_errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'      => 'ConstraintViolationList',
            'status'     => 422,
            'violations' => [
                ['propertyPath' => 'name', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'surname', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'email', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'gender', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'roles', 'message' => 'This collection should contain 1 element or more.'],
                ['propertyPath' => 'active', 'message' => 'This value should not be blank.']
            ],
        ]);
        // check overall amount of expected constraint violations
        static::assertCount(6, $response->toArray(throw: false)['violations']);
    }


    // ============================================================================== //
    // ======================== NAME ATTRIBUTE FOCUSED TESTS ======================== //


    public function testCreateUserWithMissingNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName: 'name'
        );
    }

    public function testCreateUserWithNullAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   null,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithEmptyAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'This value should not be blank.'],
            ]
        );
    }

    public function testCreateUserWithWhitespaceAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: "\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}",
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithFalseBoolAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   false,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithFalseStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'false',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithTrueBoolAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   true,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithTrueStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'true',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithZeroIntAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroIntStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '0',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithPositiveIntAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveIntStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '1',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithNegativeIntAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   -1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeIntStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '-1',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithZeroDoubleAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   0.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroDoubleStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '0.0',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithPositiveDoubleAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveDoubleStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '1.0',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithNegativeDoubleAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   -1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeDoubleStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '-1.0',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithArrayAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   [],
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithObjectAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'name',
            attributeValue:   ["attributeX" => "valueX"],
            expectedTypeName: 'string'
        );
    }

    // public function testCreateUserWithBinaryAsNameFieldValue(): void
    // {
    //     // remove all data from database
    //     $this->cleanDatabase();

    //     // call the list users API endpoint
    //     $response = static::createClient()->request('POST', '/api/users', [
    //         'json'    => [
    //             'name'    => "\x04\x00\xa0\x00",// \u001A\u001B\u0005\u001B
    //             'surname' => 'User X',
    //             'email'   => 'test.user.X@foo.local',
    //             'gender'  => Gender::MALE,
    //             'roles'   =>  [
    //                 Role::USER,
    //                 Role::ADMIN,
    //             ],
    //             'active'   => true 

    //         ],
    //         'headers' => [
    //             'Accept' => 'application/ld+json'
    //         ]
    //     ]);

    //     static::assertResponseStatusCodeSame(400);
    //     static::assertResponseHeaderSame(
    //         'content-type',
    //         'application/problem+json; charset=utf-8'
    //     );
    //     static::assertStringContainsString('/api/errors', $response->toArray(throw: false)['@id']);
    //     static::assertJsonContains([
    //         '@type'  => 'hydra:Error',
    //         'status' => 400,
    //         'title'  => 'An error occurred',
    //         'detail' => 'The type of the "name" attribute must be "string", "object" given.'
    //     ]);
    // }

    public function testCreateUserWithTooLongStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'This value is too long. It should have 48 characters or less.'],
            ]
        );
    }

    public function testCreateUserWithLowercaseOnlyStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'lorem ipsum dolor sit',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserWithUpperCaseOnlyStringAsNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'name' => 'LOREM IPSUM DOLOR SIT'
            ]
        ));
    }
 
    public function testCreateUserContainingWordStartingWithApostropheInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Lorem \'Ipsum Dolor Sit',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithApostropheInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'name' => 'D\'Lisa'
            ]
        ));
    }

    public function testCreateUserContainingWordEndingWithApostropheInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Lorem Ipsum\' Dolor Sit',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithDoubleApostropheInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Ebony-M\'\'Lynn',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" may contain hyphens and apostrophes wrapped by letters only.'],
            ]
        );
    }

    public function testCreateUserContainingWordStartingWithHyphenInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Lorem -Ipsum Dolor Sit',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithHyphenInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'name' => 'Emily-rose'
            ]
        ));
    }

    public function testCreateUserContainingWordEndingWithHyphenInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Lorem Ipsum- Dolor Sit',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithDoubleHyphenInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Ebony--M\'Lynn',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" may contain hyphens and apostrophes wrapped by letters only.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithApostropheFollowedByHyphenInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Ebony-M\'-Lynn',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" may contain hyphens and apostrophes wrapped by letters only.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithApostropheAndHyphenInNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'name' => 'Ebony-M\'Lynn'
            ]
        ));
    }

    public function testCreateUserContainingSingleSpaceBeforeNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: ' Ebony-M\'Lynn',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingSingleSpaceBeforeAndAfterNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: ' Ebony-M\'Lynn ',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingSingleSpaceAfterNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Ebony-M\'Lynn ',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingDoubleSpaceBeforeNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '  Ebony-M\'Lynn',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingDoubleSpaceBeforeAndAfterNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: '  Ebony-M\'Lynn  ',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingDoubleSpaceAfterNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Ebony-M\'Lynn  ',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingDoubleSpaceInTheMiddleOfNameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Ebony-M\'Ly  nn',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }


    // ======================== NAME ATTRIBUTE FOCUSED TESTS ======================== //
    // ============================================================================== //



    // ============================================================================== //
    // ====================== SURNAME ATTRIBUTE FOCUSED TESTS ======================= //


    public function testCreateUserWithMissingSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName: 'surname'
        );
    }

    public function testCreateUserWithNullAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   null,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithEmptyAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'This value should not be blank.'],
            ]
        );
    }

    public function testCreateUserWithWhitespaceAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: "\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}",
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserWithFalseBoolAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   false,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithFalseStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'false',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" has to contain at least one uppercase letter.'],
            ]
        );
    }

    public function testCreateUserWithTrueBoolAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   true,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithTrueStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'true',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" has to contain at least one uppercase letter.'],
            ]
        );
    }

    public function testCreateUserWithZeroIntAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroIntStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '0',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserWithPositiveIntAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveIntStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '1',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserWithNegativeIntAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   -1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeIntStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '-1',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserWithZeroDoubleAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   0.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroDoubleStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '0.0',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserWithPositiveDoubleAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveDoubleStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '1.0',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserWithNegativeDoubleAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   -1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeDoubleStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '-1.0',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserWithArrayAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   [],
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithObjectAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'surname',
            attributeValue:   ["attributeX" => "valueX"],
            expectedTypeName: 'string'
        );
    }

    // public function testCreateUserWithBinaryAsSurnameFieldValue(): void
    // {
    //     // remove all data from database
    //     $this->cleanDatabase();

    //     // call the list users API endpoint
    //     $response = static::createClient()->request('POST', '/api/users', [
    //         'json'    => [
    //             'surname'    => "\x04\x00\xa0\x00",// \u001A\u001B\u0005\u001B
    //             'surname' => 'User X',
    //             'email'   => 'test.user.X@foo.local',
    //             'gender'  => Gender::MALE,
    //             'roles'   =>  [
    //                 Role::USER,
    //                 Role::ADMIN,
    //             ],
    //             'active'   => true 

    //         ],
    //         'headers' => [
    //             'Accept' => 'application/ld+json'
    //         ]
    //     ]);

    //     static::assertResponseStatusCodeSame(400);
    //     static::assertResponseHeaderSame(
    //         'content-type',
    //         'application/problem+json; charset=utf-8'
    //     );
    //     static::assertStringContainsString('/api/errors', $response->toArray(throw: false)['@id']);
    //     static::assertJsonContains([
    //         '@type'  => 'hydra:Error',
    //         'status' => 400,
    //         'title'  => 'An error occurred',
    //         'detail' => 'The type of the "surname" attribute must be "string", "object" given.'
    //     ]);
    // }

    public function testCreateUserWithTooLongStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'This value is too long. It should have 255 characters or less.'],
            ]
        );
    }

    public function testCreateUserWithLowercaseOnlyStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'lorem ipsum dolor sit amet consectetur adipiscing elit',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" has to contain at least one uppercase letter.'],
            ]
        );
    }

    public function testCreateUserWithUpperCaseOnlyStringAsSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'surname' => 'LOREM IPSUM DOLOR SIT'
            ]
        ));
    }
 
    public function testCreateUserContainingWordStartingWithApostropheInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'lorem \'Ipsum dolor sit amet consectetur adipiscing elit',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithApostropheInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'surname' => 'd\'Bosco Dolor'
            ]
        ));
    }

    public function testCreateUserContainingWordEndingWithApostropheInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'lorem Ipsum\' dolor sit amet consectetur adipiscing elit',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }
    
    public function testCreateUserContainingWordWithDoubleApostropheInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'd\'\'Bosco-Dolor',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" may contain hyphens and apostrophes wrapped by letters only.'],
            ]
        );
    }

    public function testCreateUserContainingWordStartingWithHyphenInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'lorem -Ipsum dolor sit amet consectetur adipiscing elit',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }
    
    public function testCreateUserContainingWordWithHyphenInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'surname' => 'de Bosco-Dolor'
            ]
        ));
    }

    public function testCreateUserContainingWordEndingWithHyphenInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'lorem Ipsum- dolor sit amet consectetur adipiscing elit',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithDoubleHyphenInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'd\'Bosco--Dolor',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" may contain hyphens and apostrophes wrapped by letters only.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithApostropheFollowedByHyphenInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'd\'-Bosco-Dolor',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" may contain hyphens and apostrophes wrapped by letters only.'],
            ]
        );
    }

    public function testCreateUserContainingWordWithApostropheAndHyphenInSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'surname' => 'd\'Bosco-Dolor'
            ]
        ));
    }

    public function testCreateUserContainingSingleSpaceBeforeSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: ' d\'Bosco-Dolor',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingSingleSpaceBeforeAndAfterSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: ' d\'Bosco-Dolor ',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingSingleSpaceAfterSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'd\'Bosco-Dolor ',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    
    public function testCreateUserContainingDoubleSpaceBeforeSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '  d\'Bosco-Dolor',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingDoubleSpaceBeforeAndAfterSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: '  d\'Bosco-Dolor  ',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingDoubleSpaceAfterSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'd\'Bosco-Dolor  ',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingDoubleSpaceInTheMiddleOfSurnameFieldValue(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'd\'Bos  co-Dolor',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    // TODO - create user with invalid surname set to <missing attribute at all> | null | '' | '   ' | false | "false" | true | "true" | true | 0 | "0" | 0.0 | "0.0" | array | object | binary data | too long value
    // TODO - create user with invalid email <missing attribute at all> | null | '' | '   ' | ... | too long value | invalid email pattern | already existing email
    // TODO - create user with invalid gender <missing attribute at all> | null | '' | '   ' | ... | too long value | value other than supported gender value
    // TODO - create user with invalid roles <missing attribute at all> | null | '' | '   ' | ... | empty array | too long value | value(s) other than support role value
    // TODO - create user with invalid note flag <missing attribute at all> | null | '' | '   ' | ... | too long value
    // TODO - create user with invalid active flag <missing attribute at all> | null | '' | '   ' | false | "false" | true | "true" | true | 0 | "0" | 1 | "1" | -1 | "-1" | 0.0 | "0.0" | 1.0 | "1.0" | -1.0 | "-1.0"

    public function testCreateUserSuccess(): void
    {
        // remove all data from database
        $this->cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(self::DEFAULT_USER_DATA);
    }

    /**
     * Test expected successfull user entity 
     * creating scenario for given input user
     * data
     * 
     * @param array $userData
     * @return void
     */
    public function _testSuccessfullCreationOfUser(array $userData): void
    {
        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'json'    => $userData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        static::assertResponseStatusCodeSame(201); // expecting HTTP reponse code 201 - Created
        static::assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/users', $response->toArray(throw: false)['@id']);
        static::assertJsonContains(array_merge(
            [
                '@context' => '/api/contexts/User', 
                '@type'    => 'User'
            ],
            // converting backend enums contained within
            // default user data array into strings
            json_decode(json_encode($userData),true)
        ));

        // check that user ID is valid UUID
        static::assertTrue(Uuid::isValid($response->toArray(throw: false)['id']));
        // check that JSON-LD @id is pointing
        // to the just created resource
        static::assertSame(
            $response->toArray(throw: false)['@id'],
            '/api/users/' . $response->toArray(throw: false)['id']
        );
    }

    /**
     * Test expected constraint violations scenario
     * for given missing attribute
     *
     * @param string $attributeName
     * @return void
     */
    protected function _testConstraintViolationForMissingAttribute(string $attributeName): void
    {
        // remove an attribute from default user data
        $testUserData = self::DEFAULT_USER_DATA;
        unset($testUserData[$attributeName]);

        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'json'    => $testUserData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        static::assertResponseIsUnprocessable(); // expecting HTTP reponse code 422 - Unprocessable
        static::assertResponseHeaderSame(
            'content-type',
            'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/validation_errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'      => 'ConstraintViolationList',
            'status'     => 422,
            'violations' => [
                ['propertyPath' => $attributeName, 'message' => 'This value should not be blank.'],
            ],
        ]);
        // check overall amount of expected constraint violations
        static::assertCount(1, $response->toArray(throw: false)['violations']);
    }

    /**
     * Test expected type violations scenario
     * for given entity attribute
     * 
     * @param string $attributeName
     * @param mixed  $attributeValue
     * @param string $expectedTypeName Definition ef expected attribute type name as string
     * @return void
     */
    protected function _testTypeViolationForAttributeValue(
        string $attributeName, 
        mixed $attributeValue, 
        string $expectedTypeName): void 
    {
        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ],
            'json'    => array_merge(
                self::DEFAULT_USER_DATA,
                [
                    $attributeName => $attributeValue,
                ]
            ),
        ]);

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame(
            'content-type',
            'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'  => 'hydra:Error',
            'status' => 400,
            'title'  => 'An error occurred',
            'detail' => sprintf(
                'The type of the "%s" attribute must be "%s", "%s" given.',
                $attributeName,
                $expectedTypeName,
                gettype($attributeValue)
            )
        ]);
    }

    /**
     * Test expected constraint violations scenario
     * for given entity attribute
     * 
     * @param string $attributeName
     * @param mixed  $attributeValue
     * @param array  $constraintViolations Definition ef expected constraint violations data to be found in the response
     * @return void
     */
    protected function _testConstraintViolationForAttributeValue(
        string $attributeName, 
        mixed $attributeValue, 
        array $constraintViolations): void 
    {
        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ],
            'json'    => array_merge(
                self::DEFAULT_USER_DATA,
                [
                    $attributeName => $attributeValue,
                ]
            ),
        ]);

        static::assertResponseIsUnprocessable(); // expecting HTTP reponse code 422 - Unprocessable
        static::assertResponseHeaderSame(
            'content-type',
            'application/problem+json; charset=utf-8'
        );
        static::assertStringContainsString('/api/validation_errors', $response->toArray(throw: false)['@id']);
        static::assertJsonContains([
            '@type'      => 'ConstraintViolationList',
            'status'     => 422,
            'violations' => $constraintViolations,
        ]);
        // check overall amount of expected constraint violations
        static::assertCount(count($constraintViolations), $response->toArray(throw: false)['violations']);
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