<?php

namespace App\Tests\Data\Providers;

use App\Entity\Gender;

/**
 * Source of data for gender validation testing
 */
class GenderDataProvider
{
    /**
     * Set of of basic uppercased, lowercased and capilatalized first letter
     * differing from original value of each gender enum case
     * 
     * @return string[][]
     */
    public static function getCaseMismatchingGenderEnumValues(): array
    {
        $values = [];
        foreach(Gender::cases() as $gender) {
            // lowercased value
            $lowercasedValue = strtolower($gender->value);
            if($lowercasedValue !== $gender->value) {
                $values[$lowercasedValue] = [$lowercasedValue];
            }
            // uppercased value
            $uppercasedValue = strtoupper($gender->value);
            if($uppercasedValue !== $gender->value) {
                $values[$uppercasedValue] = [$uppercasedValue];
            }
            // first letter capitalized lowercased value
            $ucfirstLowercasedValue = ucfirst($lowercasedValue);
            if($ucfirstLowercasedValue !== $gender->value) {
                $values[$ucfirstLowercasedValue] = [$ucfirstLowercasedValue];
            }
        }

        return $values;
    }
}
