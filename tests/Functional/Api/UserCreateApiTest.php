<?php

namespace App\Tests\Functional\Api;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\Gender;
use App\Tests\DatabasePrimer;
use App\Tests\FixturesLoadingTrait;
use App\Tests\Data\Fixtures\UserFixtures;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Tests of create user backend API functionality
 */
class UserCreateApiTest extends ApiTestCase
{
    use FixturesLoadingTrait;

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
                ['propertyPath' => 'active', 'message' => 'This value should not be null.']
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
        static::cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName: 'name'
        );
    }

    public function testCreateUserWithNullAsNameFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
    //     static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'name',
            attributeValue: 'Ebony-M\'Ly  nn',
            constraintViolations: [
                ['propertyPath' => 'name', 'message' => 'The "name" attribute accepts uppercase letter starting forenames containing letters, hyphen or apostrophe symbols only and separated by single space symbols.'],
            ]
        );
    }

    public function testCreateUserContainingWithValidLatinNameFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'name' => 'Emily-rose Ebony-M\'Lynn'
            ]
        ));
    }

    public function testCreateUserContainingWithValidCyrilicNameFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'name' => 'Ангелюк'
            ]
        ));
    }


    // ======================== NAME ATTRIBUTE FOCUSED TESTS ======================== //
    // ============================================================================== //



    // ============================================================================== //
    // ====================== SURNAME ATTRIBUTE FOCUSED TESTS ======================= //


    public function testCreateUserWithMissingSurnameFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName: 'surname'
        );
    }

    public function testCreateUserWithNullAsSurnameFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
    //     static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

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
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'surname',
            attributeValue: 'd\'Bos  co-Dolor',
            constraintViolations: [
                ['propertyPath' => 'surname', 'message' => 'The "surname" attribute accepts letters, hyphen and apostrophe symbols containing surnames separated by single space symbols only.'],
            ]
        );
    }

    public function testCreateUserContainingWithValidLatinSurnameFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'surname' => 'd\'Bosco-Dolor Vincenze-diAnglia'
            ]
        ));
    }

    public function testCreateUserContainingWithValidCyrilicSurnameFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'surname' => 'Ангелюк'
            ]
        ));
    }


    // ====================== SURNAME ATTRIBUTE FOCUSED TESTS ======================= //
    // ============================================================================== //



    // ============================================================================== //
    // ======================= EMAIL ATTRIBUTE FOCUSED TESTS ======================== //


    public function testCreateUserWithMissingEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName: 'email'
        );
    }

    public function testCreateUserWithNullAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   null,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithEmptyAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: '',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value should not be blank.'],
            ]
        );
    }

    public function testCreateUserWithWhitespaceAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: "\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}",
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithFalseBoolAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   false,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithFalseStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: 'false',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithTrueBoolAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   true,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithTrueStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: 'true',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithZeroIntAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroIntStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: '0',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithPositiveIntAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveIntStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: '1',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithNegativeIntAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   -1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeIntStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: '-1',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithZeroDoubleAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   0.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroDoubleStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: '0.0',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithPositiveDoubleAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveDoubleStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: '1.0',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithNegativeDoubleAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   -1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeDoubleStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: '-1.0',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    public function testCreateUserWithArrayAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   [],
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithObjectAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'email',
            attributeValue:   ["attributeX" => "valueX"],
            expectedTypeName: 'string'
        );
    }

    // public function testCreateUserWithBinaryAsEmailFieldValue(): void
    // {
    //     // remove all data from database
    //     static::cleanDatabase();

    //     // call the list users API endpoint
    //     $response = static::createClient()->request('POST', '/api/users', [
    //         'json'    => [
    //             'email'    => "\x04\x00\xa0\x00",// \u001A\u001B\u0005\u001B
    //             'email' => 'User X',
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
    //         'detail' => 'The type of the "email" attribute must be "string", "object" given.'
    //     ]);
    // }

    public function testCreateUserWithTooLongStringAsEmailFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
            $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: 'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit',
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is too long. It should have 255 characters or less.'],
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    
    /**
     * @dataProvider \App\Tests\Data\Providers\EmailDataProvider::getInvalidEmails()
     */
    public function testCreateUserWithInvalidEmailFieldValue(string $email): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: $email,
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\EmailDataProvider::getInvalidHtml5Emails()
     */
    public function testCreateUserWithInvalidHtml5EmailFieldValue(string $email): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: $email,
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\EmailDataProvider::getValidEmailsWrappedByWhitespaces()
     */
    public function testCreateUserWithByWhitespacesWrappedValidEmailFieldValue(string $email): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'email',
            attributeValue: $email,
            constraintViolations: [
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ]
        );
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\EmailDataProvider::getValidEmails()
     */
    public function testCreateUserWithValidEmailFieldValue(string $email): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'email' => $email,
            ]
        ));
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\EmailDataProvider::getValidHtml5Emails()
     * @link https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address
     */
    public function testCreateUserWithValidHtml5EmailFieldValue(string $email): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'email' => $email,
            ]
        ));
    }


    // ======================= EMAIL ATTRIBUTE FOCUSED TESTS ======================== //
    // ============================================================================== //



    // ============================================================================== //
    // ======================= GENDER ATTRIBUTE FOCUSED TESTS ======================= //


    public function testCreateUserWithMissingGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName: 'gender'
        );
    }

    public function testCreateUserWithNullAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       null,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    public function testCreateUserWithEmptyAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       '',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithWhitespaceAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       "\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}",
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithFalseBoolAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       false,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    public function testCreateUserWithFalseStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       'false',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithTrueBoolAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       true,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    public function testCreateUserWithTrueStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       'true',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithZeroIntAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       0,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithZeroIntStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       '0',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithPositiveIntAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       1,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithPositiveIntStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       '1',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithNegativeIntAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       -1,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithNegativeIntStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       '-1',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithZeroDoubleAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       0.0,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    public function testCreateUserWithZeroDoubleStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       '0.0',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithPositiveDoubleAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       1.0,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    public function testCreateUserWithPositiveDoubleStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       '1.0',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithNegativeDoubleAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       -1.0,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    public function testCreateUserWithNegativeDoubleStringAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       '-1.0',
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }

    public function testCreateUserWithArrayAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       [],
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    public function testCreateUserWithObjectAsGenderFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       ["attributeX" => "valueX"],
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data is neither an integer nor a string, you should pass an integer or a string that can be parsed as an enumeration case of type App\\Entity\\Gender.',
        );
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\GenderDataProvider::getCaseMismatchingGenderEnumValues()
     */
    public function testCreateUserWithCaseSensitivityMalformedGenderFieldValue(string $invalidGenderValue): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'gender',
            attributeValue:       $invalidGenderValue,
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Gender',
        );
    }


    // ======================= GENDER ATTRIBUTE FOCUSED TESTS ======================= //
    // ============================================================================== //



    // ============================================================================== //
    // ======================= ROLES ATTRIBUTE FOCUSED TESTS ======================== //


    public function testCreateUserWithMissingRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName:        'roles',
            expectedErrorMessage: 'This collection should contain 1 element or more.'
        );
    }

    public function testCreateUserWithNullAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   null,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithEmptyAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   '',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithWhitespaceAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   "\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}",
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithFalseBoolAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   false,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithFalseStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   'false',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithTrueBoolAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   true,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithTrueStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   'true',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithZeroIntAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   0,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithZeroIntStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   '0',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithPositiveIntAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   1,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithPositiveIntStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   '1',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithNegativeIntAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   -1,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithNegativeIntStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   '-1',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithZeroDoubleAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   0.0,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithZeroDoubleStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   '0.0',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithPositiveDoubleAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   1.0,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithPositiveDoubleStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   '1.0',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithNegativeDoubleAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   -1.0,
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithNegativeDoubleStringAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'roles',
            attributeValue:   '-1.0',
            expectedTypeName: 'array',
        );
    }

    public function testCreateUserWithArrayAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:        'roles',
            attributeValue:       [],
            constraintViolations: [
                ['propertyPath' => 'roles', 'message' => 'This collection should contain 1 element or more.'],
            ],
        );
    }

    public function testCreateUserWithObjectAsRolesFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'roles',
            attributeValue:       ["attributeX" => "valueX"],
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Role',
        );
    }

    public function testCreateUserWithDuplicatedRolesFieldValues(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:        'roles',
            attributeValue:       [
                Role::USER, 
                Role::ADMIN, 
                Role::USER
            ],
            constraintViolations: [
                ['propertyPath' => 'roles', 'message' => 'The collection contains duplicate values.'],
            ]
        );
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\RoleDataProvider::getCaseMismatchingRoleEnumValues()
     */
    public function testCreateUserWithCaseSensitivityMalformedRolesFieldValues(string $invalidRoleValue): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run type violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:        'roles',
            attributeValue:       [$invalidRoleValue],
            expectedTypeName:     'string',
            expectedErrorMessage: 'The data must belong to a backed enumeration of type App\\Entity\\Role',
        );
    }


    // ======================= ROLES ATTRIBUTE FOCUSED TESTS ======================== //
    // ============================================================================== //



    // ============================================================================== //
    // ======================== NOTE ATTRIBUTE FOCUSED TESTS ======================== //


    public function testCreateUserWithMissingNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // get rid of 'note' attribute from
        // default user data if it exists there
        $userData = self::DEFAULT_USER_DATA;
        if(array_key_exists('note', $userData)) {
            unset($userData['note']);
        }
    
        // run the create user procedure
        // with the 'note' attribute missing
        // expecting user entity being successfully
        // created
        $this->_testSuccessfullCreationOfUser(
            userData: $userData,
            additionalAssertsCallback: 
                function($response) {
                    // expecting the 'note' attribute to be missing in the response
                    static::assertArrayNotHasKey('note', $response->toArray(throw: false));
                }
        );
    }

    public function testCreateUserWithNullAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();
    
        // run the create user procedure
        // with the 'note' attribute set
        // to NULL
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => null
                ]
            ),
            expectedResponseUserData: array_diff_assoc(
                self::DEFAULT_USER_DATA,
                [
                    'note' => null
                ]
            ),
            additionalAssertsCallback: 
                function($response) {
                    // expecting the 'note' attribute to be missing in the response
                    static::assertArrayNotHasKey('note', $response->toArray(throw: false));
                }
        );
    }

    public function testCreateUserWithEmptyAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();
    
        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => ''
                ]
            ),
        );
    }

    public function testCreateUserWithWhitespaceAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        // to values consisting of whitespaces
        // only expecting it being trimmed
        // to the empty string
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => "\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}"
                ]
            ),
            expectedResponseUserData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => ''
                ]
            ),
            additionalAssertsCallback: 
                function($response) {
                    // expecting the 'note' attribute is present in the response
                    static::assertArrayHasKey('note', $response->toArray(throw: false));
                    // and expeting it to be multibyte-trimmed into empty string
                    static::assertSame('', $response->toArray(throw: false)['note']);
                }
        );
    }

    public function testCreateUserWithFalseBoolAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   false,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithFalseStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        // to string 'false'
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => 'false'
                ]
            )
        );
    }

    public function testCreateUserWithTrueBoolAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   true,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithTrueStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        // to string 'true'
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => 'true'
                ]
            )
        );
    }

    public function testCreateUserWithZeroIntAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroIntStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => '0'
                ]
            )
        );
    }

    public function testCreateUserWithPositiveIntAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveIntStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => '1'
                ]
            )
        );
    }

    public function testCreateUserWithNegativeIntAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   -1,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeIntStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => '-1'
                ]
            )
        );
    }

    public function testCreateUserWithZeroDoubleAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   0.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithZeroDoubleStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => '0.0'
                ]
            )
        );
    }

    public function testCreateUserWithPositiveDoubleAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithPositiveDoubleStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => '1.0'
                ]
            )
        );
    }

    public function testCreateUserWithNegativeDoubleAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   -1.0,
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithNegativeDoubleStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => '-1.0'
                ]
            )
        );
    }

    public function testCreateUserWithArrayAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   [],
            expectedTypeName: 'string'
        );
    }

    public function testCreateUserWithObjectAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'note',
            attributeValue:   ["attributeX" => "valueX"],
            expectedTypeName: 'string'
        );
    }

    // public function testCreateUserWithBinaryAsNoteFieldValue(): void
    // {
    //     // remove all data from database
    //     static::cleanDatabase();

    //     // call the list users API endpoint
    //     $response = static::createClient()->request('POST', '/api/users', [
    //         'json'    => [
    //             'note'    => "\x04\x00\xa0\x00",// \u001A\u001B\u0005\u001B
    //             'note' => 'User X',
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
    //         'detail' => 'The type of the "note" attribute must be "string", "object" given.'
    //     ]);
    // }

    public function testCreateUserWithTooLongStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testConstraintViolationForAttributeValue(
            attributeName:  'note',
            attributeValue: str_repeat('x',4097),
            constraintViolations: [
                ['propertyPath' => 'note', 'message' => 'This value is too long. It should have 4096 characters or less.'],
            ]
        );
    }

    public function testCreateUserWithLowercaseOnlyStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        
        // run the create user procedure
        // with the 'note' attribute set
        $this->_testSuccessfullCreationOfUser(
            userData: array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'note' => 'lorem ipsum dolor sit amet consectetur adipiscing elit'
                ]
            )
        );
    }

    public function testCreateUserWithUpperCaseOnlyStringAsNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'note' => 'LOREM IPSUM DOLOR SIT'
            ]
        ));
    }

    public function testCreateUserWithValidLatinNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'note' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit.'
            ]
        ));
    }

    public function testCreateUserContainingWithValidCyrilicNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'note' => 'Лорем ипсум долор сит амет, но партиендо перицулис иус, ат еам аугуе реформиданс.'
            ]
        ));
    }
    
    public function testCreateUserContainingWithValidArabicNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'note' => 'كُلفة استعملت المتاخمة من مكن, بحشد الأهداف التغييرات شيء ما. أن وفي بداية وانهاء الشتوية. عسكرياً الأوروبيّون جعل بل, إحكام الباهضة بالولايات دول أن, حقول والديون الواقعة مما قد. صفحة انتصارهم أخذ ان, عدم حاملات والنفيس ان.'
            ]
        ));
    }

    public function testCreateUserContainingWithValidHebrewNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'note' => 'החלל ערכים מה כלל. או דפים ספורט המלחמה לוח. שמו דת רביעי המזנון בהיסטוריה, קסאם למחיקה אם מדע. ברוכים צרפתית תבניות ויש או.'
            ]
        ));
    }
    
    public function testCreateUserContainingWithValidHindiNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'note' => 'अधिकार पत्रिका वहहर सादगि सादगि कम्प्युटर यन्त्रालय उशकी बढाता दिये जागरुक वेबजाल एसेएवं मुश्किल मुख्यतह देते विश्व हमेहो। पहोचाना निरपेक्ष सीमित भाषा मजबुत स्थिति वैश्विक पुष्टिकर्ता आपको मुक्त लगती प्रव्रुति विचारशिलता'
            ]
        ));
    }

    
    public function testCreateUserContainingWithValidChineseNoteFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'note' => '面転仏領齢祝刈念演幸川月覧乗新革者。整鹿寝冷前題俊激新郵識報打秀岸認政。面裕関宇少男断注逃覧究良雪暴文新載。'
            ]
        ));
    }

    
    // ======================== NOTE ATTRIBUTE FOCUSED TESTS ======================== //
    // ============================================================================== //



    // ============================================================================== //
    // ====================== ACTIVE ATTRIBUTE FOCUSED TESTS ======================== //


    public function testCreateUserWithMissingActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        $this->_testConstraintViolationForMissingAttribute(
            attributeName:        'active',
            expectedErrorMessage: 'This value should not be null.'
        );
    }

    public function testCreateUserWithNullAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   null,
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithEmptyAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   '',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithWhitespaceAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   "\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}",
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithFalseBoolAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'active' => false
            ]
        ));
    }

    public function testCreateUserWithFalseStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   'false',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithTrueBoolAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(array_merge(
            self::DEFAULT_USER_DATA,
            [
                'active' => true
            ]
        ));
    }

    public function testCreateUserWithTrueStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   'true',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithZeroIntAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   0,
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithZeroIntStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   '0',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithPositiveIntAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   1,
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithPositiveIntStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   '1',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithNegativeIntAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   -1,
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithNegativeIntStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   '-1',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithZeroDoubleAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   0.0,
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithZeroDoubleStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   '0.0',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithPositiveDoubleAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   1.0,
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithPositiveDoubleStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   '1.0',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithNegativeDoubleAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   -1.0,
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithNegativeDoubleStringAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   '-1.0',
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithArrayAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   [],
            expectedTypeName: 'bool'
        );
    }

    public function testCreateUserWithObjectAsActiveFieldValue(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run constraint violation test body
        $this->_testTypeViolationForAttributeValue(
            attributeName:    'active',
            attributeValue:   ["attributeX" => "valueX"],
            expectedTypeName: 'bool'
        );
    }


    // ======================= ACTIVE ATTRIBUTE FOCUSED TESTS ======================== //
    // ============================================================================== //



    // ==================== COMMON COMPLEX USER CREATION TESTS ====================== //
    // ============================================================================== //


    public function testCreateNewUserSuccess(): void
    {
        // remove all data from database
        static::cleanDatabase();

        // run the user creation test body
        $this->_testSuccessfullCreationOfUser(self::DEFAULT_USER_DATA);
    }

    public function testCreateExistingUserSuccess(): void
    {
        // remove all data from database
        static::loadFixtures([
            UserFixtures::class
        ]);

        // definition of expected constraint violations
        $expectedContraintViolations = [
            ['propertyPath' => 'email', 'message' => 'This value is already used.'],
        ];

        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ],
            'json'    => array_merge(
                self::DEFAULT_USER_DATA,
                [
                    'surname' => 'User A',
                    'email'   => 'test.user.A@foo.local'
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
            'violations' => $expectedContraintViolations,
        ]);
        // check overall amount of expected constraint violations
        static::assertCount(count($expectedContraintViolations), $response->toArray(throw: false)['violations']);
    }


    // ==================== COMMON COMPLEX USER CREATION TESTS ====================== //
    // ============================================================================== //


    /**
     * Test expected successfull user entity 
     * creating scenario for given input user
     * data
     * 
     * @param array $userData
     * @param array $expectedResponseUserData
     * @param callable $additionalAssertsCallback
     * @return void
     */
    public function _testSuccessfullCreationOfUser(array $userData, ?array $expectedResponseUserData = null, ?callable $additionalAssertsCallback = null): void
    {
        // call the list users API endpoint
        $response = static::createClient()->request('POST', '/api/users', [
            'json'    => $userData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        // compile expected response user data
        // from input user data unless provided
        // explicitly
        $expectedResponseUserData ??= $userData;

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
            json_decode(json_encode($expectedResponseUserData),true)
        ));

        // check that user ID is valid UUID
        static::assertTrue(Uuid::isValid($response->toArray(throw: false)['id']));
        // check that JSON-LD @id is pointing
        // to the just created resource
        static::assertSame(
            $response->toArray(throw: false)['@id'],
            '/api/users/' . $response->toArray(throw: false)['id']
        );

        // check that location header is set in the response
        // and that it points to the newly created resource
        static::assertResponseHasHeader('location');
        static::assertResponseHeaderSame('location', '/api/users/' . $response->toArray(throw: false)['id']);

        // execute additional external assertions
        // delivered as input parameter of this method
        if(!is_null($additionalAssertsCallback)) {
            // invoke additional assertions
            // provided by calling test method
            $additionalAssertsCallback($response);   
        }

        // check that entity has been created in database 
        // successfully
        static::assertNotNull(
            static::getContainer()
                ->get('doctrine')
                ->getRepository(User::class)
                ->findOneBy(['email' => $userData['email']])
        );
    }

    /**
     * Test expected type violations scenario
     * for given entity attribute
     * 
     * @param string $attributeName
     * @param mixed  $attributeValue
     * @param string $expectedTypeName Definition ef expected attribute type name as string
     * @param string $expectedErrorMessage
     * @return void
     */
    protected function _testTypeViolationForAttributeValue(
        string $attributeName, 
        mixed $attributeValue, 
        string $expectedTypeName,
        ?string $expectedErrorMessage = null): void 
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

        // calculate expected detailed error message
        // unless provided explicitly
        $expectedErrorMessage ??= sprintf(
            'The type of the "%s" attribute must be "%s", "%s" given.',
            $attributeName,
            $expectedTypeName,
            gettype($attributeValue)
        );

        // assert obtained response
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
            'detail' => $expectedErrorMessage
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

    /**
     * Test expected constraint violations scenario
     * for given missing attribute
     *
     * @param string $attributeName
     * @param string $expectedErrorMessage
     * @return void
     */
    protected function _testConstraintViolationForMissingAttribute(string $attributeName, ?string $expectedErrorMessage = null): void
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

        // calculate expected error message unless provided explicitly
        $expectedErrorMessage ??= 'This value should not be blank.';

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
                ['propertyPath' => $attributeName, 'message' => $expectedErrorMessage],
            ],
        ]);
        // check overall amount of expected constraint violations
        static::assertCount(1, $response->toArray(throw: false)['violations']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}