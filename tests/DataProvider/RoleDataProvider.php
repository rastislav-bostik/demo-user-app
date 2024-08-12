<?php

namespace App\Tests\DataProvider;

use App\Entity\Role;

/**
 * Source of data for role validation testing
 */
class RoleDataProvider
{
    /**
     * Set of of basic uppercased, lowercased and capilatalized first letter
     * differing from original value of each role enum case
     * (e.g. male, Male, female, Female - avoiding enum case values MALE, FEMALE)
     * 
     * @return string[][]
     */
    public static function getCaseMismatchingRoleEnumValues(): array
    {
        $values = [];
        foreach(Role::cases() as $role) {
            // lowercased value
            $lowercasedValue = strtolower($role->value);
            if($lowercasedValue !== $role->value) {
                $values[$lowercasedValue] = [$lowercasedValue];
            }
            // uppercased value
            $uppercasedValue = strtoupper($role->value);
            if($uppercasedValue !== $role->value) {
                $values[$uppercasedValue] = [$uppercasedValue];
            }
            // first letter capitalized lowercased value
            $ucfirstLowercasedValue = ucfirst($lowercasedValue);
            if($ucfirstLowercasedValue !== $role->value) {
                $values[$ucfirstLowercasedValue] = [$ucfirstLowercasedValue];
            }
        }

        return $values;
    }
}
