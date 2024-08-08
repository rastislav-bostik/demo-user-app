<?php

namespace App\Tests\DataProvider;

/**
 * Source of data for email validion testing
 * 
 * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
 * @link https://gist.github.com/cjaoude/fd9910626629b53c4d25
 */
class EmailDataProvider
{

    /**
     * Set of valid emails according to Symfony 7.1 email validation tests
     * 
     * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
     */
    public static function getValidEmails(): array
    {
        return [
            ['fabien@symfony.com'],
            ['example@example.co.uk'],
            ['fabien_potencier@example.fr'],
        ];
    }

    /**
     * Set of valid emails wrapped by whitespaces to Symfony 7.1 email validation tests
     * (usable for testing of valid values decorated by invalid surronding tokens)
     * 
     * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
     */
    public static function getValidEmailsWrappedByWhitespaces(): array
    {
        return [
            ["\x20example@example.co.uk"],
            ["\x20\x0Bexample@example.co.uk"],
            ["\x20example@example.co.uk\x20"],
            ["\x20\x0Bexample@example.co.uk\x20\x0B"],
            ["example@example.co.uk\x20"],
            ["example@example.com\x0B\x0B"],
        ];
    }

    /**
     * Set of valid emails according to Symfony 7.1 email validation tests
     * following the HTML living standards
     * 
     * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
     * @link https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address
     */
    public static function getValidHtml5Emails(): array
    {
        return [
            ['fabien@symfony.com'],
            ['example@example.co.uk'],
            ['fabien_potencier@example.fr'],
            ['{}~!@example.com'],
        ];
    }

    /**
     * Set of INVALID emails according to Symfony 7.1 email validation tests
     * 
     * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
     */
    public static function getInvalidEmails(): array
    {
        return [
            ['example'],
            ['example@'],
            ['example@localhost'],
            ['foo@example.com bar'],
        ];
    }

    /**
     * Set of INVALID emails according to Symfony 7.1 email validation tests
     * following the HTML living standards
     * 
     * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
     * @link https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address
     */
    public static function getInvalidHtml5Emails(): array
    {
        return [
            ['example'],
            ['example@'],
            ['example@localhost'],
            ['example@example.co..uk'],
            ['foo@example.com bar'],
            ['example@example.'],
            ['example@.fr'],
            ['@example.com'],
            ['example@example.com;example@example.com'],
            ['example@.'],
            [' example@example.com'],
            ['example@ '],
            [' example@example.com '],
            [' example @example .com '],
            ['example@-example.com'],
            [sprintf('example@%s.com', str_repeat('a', 64))],
        ];
    }

    /**
     * Set of INVALID emails according to Symfony 7.1 email validation tests
     * following the HTML living standards allowing no TLD
     * 
     * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
     * @link https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address
     */
    public static function getInvalidHtml5EmailsAllowingNoTld(): array
    {
        return [
            ['example bar'],
            ['example@'],
            ['example@ bar'],
            ['example@localhost bar'],
            ['foo@example.com bar'],
        ];
    }

    /**
     * Set of INVALID emails according RFC5322 specification 
     * taken from to Symfony 7.1 email validation tests
     * 
     * @link https://github.com/symfony/validator/blob/7.1/Tests/Constraints/EmailValidatorTest.php
     * @link https://datatracker.ietf.org/doc/html/rfc5322
     */
    public static function getInvalidRfc5322EmailsForStrictChecks(): array
    {
        return [
            ['test@example.com test'],
            ['user  name@example.com'],
            ['user   name@example.com'],
            ['example.@example.co.uk'],
            ['example@example@example.co.uk'],
            ['(test_exampel@example.fr)'],
            ['example(example)example@example.co.uk'],
            ['.example@localhost'],
            ['ex\ample@localhost'],
            ['example@local\host'],
            ['example@localhost.'],
            ['user name@example.com'],
            ['username@ example . com'],
            ['example@(fake).com'],
            ['example@(fake.com'],
            ['username@example,com'],
            ['usern,ame@example.com'],
            ['user[na]me@example.com'],
            ['"""@iana.org'],
            ['"\"@iana.org'],
            ['"test"test@iana.org'],
            ['"test""test"@iana.org'],
            ['"test"."test"@iana.org'],
            ['"test".test@iana.org'],
            ['"test"'.\chr(0).'@iana.org'],
            ['"test\"@iana.org'],
            [\chr(226).'@iana.org'],
            ['test@'.\chr(226).'.org'],
            ['\r\ntest@iana.org'],
            ['\r\n test@iana.org'],
            ['\r\n \r\ntest@iana.org'],
            ['\r\n \r\ntest@iana.org'],
            ['\r\n \r\n test@iana.org'],
            ['test@iana.org \r\n'],
            ['test@iana.org \r\n '],
            ['test@iana.org \r\n \r\n'],
            ['test@iana.org \r\n\r\n'],
            ['test@iana.org  \r\n\r\n '],
            ['test@iana/icann.org'],
            ['test@foo;bar.com'],
            ['test;123@foobar.com'],
            ['test@example..com'],
            ['email.email@email."'],
            ['test@email>'],
            ['test@email<'],
            ['test@email{'],
            [str_repeat('x', 254).'@example.com'], // email with warnings
        ];
    }
}
