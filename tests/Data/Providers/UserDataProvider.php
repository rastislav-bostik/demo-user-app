<?php

namespace App\Tests\Data\Providers;

use App\Entity\Role;

/**
 * Source of various string data usable for testing purposes
 */
class UserDataProvider
{
    public static function getInvalidNameValues(): array
    {
        return self::decorateWithDescriptiveKeys([
           [null, 400],
           [true, 400], [false, 400],
           [0, 400], [1, 400], [-1, 400],
           [0.0, 400], [1.0, 400], [-1.0, 400],

           ['', 422],
           ['null', 422],
           ['true', 422], ['false', 422],
           ['0', 422], ['1', 422], ['-1', 422],
           ['0.0', 422], ['1.0', 422], ['-1.0', 422],

           ["\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}", 422],

           'name-too-long'   => ['Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit', 422],
           'name-lowercased' => ['lorem ipsum dolor sit', 422],
           // ...
        ]);
    }

    public static function getInvalidSurnameValues(): array
    {
        return self::decorateWithDescriptiveKeys([
           [null, 400],
           [true, 400], [false, 400],
           [0, 400], [1, 400], [-1, 400],
           [0.0, 400], [1.0, 400], [-1.0, 400],

           ['', 422],
           ['null', 422],
           ['true', 422], ['false', 422],
           ['0', 422], ['1', 422], ['-1', 422],
           ['0.0', 422], ['1.0', 422], ['-1.0', 422],

           ["\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}", 422],

           'name-too-long'   => [
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit' .
                'Lorem Ipsum Dolor Sit Amet Consectetur Adipiscing Elit', 
                422
            ],
           'name-lowercased' => ['lorem ipsum dolor sit', 422],
           // ...
        ]);
    }

    public static function getInvalidEmailValues(): array
    {
        // get list of invalid HTML5 emails
        $invalidHtml5Emails = self::decorateImportedDataWithResponseCode(
            EmailDataProvider::getInvalidHtml5Emails(),
            422
        );

        return self::decorateWithDescriptiveKeys(array_merge(
            [
                [null, 400],
                [true, 400], [false, 400],
                [0, 400], [1, 400], [-1, 400],
                [0.0, 400], [1.0, 400], [-1.0, 400],

                ['', 422],
                ['null', 422],
                ['true', 422], ['false', 422],
                ['0', 422], ['1', 422], ['-1', 422],
                ['0.0', 422], ['1.0', 422], ['-1.0', 422],

                ["\u{0009}\u{000A}\u{000B}\u{000C}\u{000D}\u{0020}\u{0085}\u{00A0}\u{1680}", 422],

                [[], 400], [['attribute' => 'value'], 400], [new \stdClass(), 400],
            ],
            $invalidHtml5Emails
        ));
    }

    public static function getInvalidGenderValues(): array
    {
        // get list of invalid HTML5 emails
        $invalidGenderValues = self::decorateImportedDataWithResponseCode(
            GenderDataProvider::getCaseMismatchingGenderEnumValues(),
            400
        );
        return self::decorateWithDescriptiveKeys(array_merge(
            [
                [null, 400],
                [true, 400], [false, 400],
                [0, 400], [1, 400], [-1, 400],
                [0.0, 400], [1.0, 400], [-1.0, 400],

                ['', 400],
                ['null', 400], 
                ['true', 400], ['false', 400],
                ['0', 400], ['1', 400], ['-1', 400],
                ['0.0', 400], ['1.0', 400], ['-1.0', 400],

                [[], 400], [['attribute' => 'value'], 400], [new \stdClass(), 400],
            ],
            $invalidGenderValues
        ));
    }

    public static function getInvalidRolesValues(): array
    {
        // get list of invalid HTML5 emails
        $invalidSingleRoleValues = self::decorateImportedDataWithResponseCode(
            RoleDataProvider::getCaseMismatchingRoleEnumValues(),
            400
        );
        return self::decorateWithDescriptiveKeys(array_merge(
            [
                [null, 400],
                [true, 400], [false, 400],
                [0, 400], [1, 400], [-1, 400],
                [0.0, 400], [1.0, 400], [-1.0, 400],

                ['', 400],
                ['null', 400], 
                ['true', 400], ['false', 400],
                ['0', 400], ['1', 400], ['-1', 400],
                ['0.0', 400], ['1.0', 400], ['-1.0', 400],

                [[], 422], [['attribute' => 'value'], 400], [new \stdClass(), 422],
            ],

            // invalid malformed single role values
            $invalidSingleRoleValues,
            
            // duplicated role names validation check
            [
                'duplicated-role' => [[Role::ADMIN, Role::USER, Role::ADMIN], 422],
            ],
        ));
    }

    public static function getInvalidNoteValues(): array
    {
        return self::decorateWithDescriptiveKeys(array_merge(
            [
                [true, 400], [false, 400],
                [0, 400], [1, 400], [-1, 400],
                [0.0, 400], [1.0, 400], [-1.0, 400],

                [[], 400], [['attribute' => 'value'], 400], [new \stdClass(), 400],

                'too-long-string' => [str_repeat('x', 4097), 422],
            ]
        ));
    }

    public static function getInvalidActiveValues(): array
    {
        return self::decorateWithDescriptiveKeys(array_merge(
            [
                [null, 400],
                [0, 400], [1, 400], [-1, 400],
                [0.0, 400], [1.0, 400], [-1.0, 400],

                ['', 400],
                ['null', 400], 
                ['true', 400], ['false', 400],
                ['0', 400], ['1', 400], ['-1', 400],
                ['0.0', 400], ['1.0', 400], ['-1.0', 400],

                [[], 400], [['attribute' => 'value'], 400], [new \stdClass(), 400],
            ],
        ));
    }

    private static function decorateWithDescriptiveKeys(array $inputData): array
    {
        $decoratedArray = [];
        foreach($inputData as $key => $value) {
            // customize key unless it's customized alread
            $newKey = (is_int($key))
                ? serialize($value)
                : $key;

            $decoratedArray[$newKey] = $value;
        }

        return $decoratedArray;
    }

    private static function decorateImportedDataWithResponseCode(array $data, int $responseCode): array {
        // decoreate each array item with expected response code
        array_walk($data, function (&$value,$key) use ($responseCode) {
            if(is_array($value)) {
                $value[] = $responseCode;
            } else {
                $value = [$value, $responseCode];
            }
        });

        return $data;
    }
}
