<?php

namespace App\Tests;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

/**
 * Tests supporting trait helping loading and unloading test fixtures classes
 * wrapping and utilizing fixtures solution currently intagrated inot project.
 */
trait FixturesLoadingTrait
{
    /**
     * Load set of basic testing fixtures
     * usefull mostly for listing, filtering,
     * sorting and pagination testing purposes.
     * 
     * @param string[] $fixtureClassNames
     * @return void
     */
    protected static function loadFixtures(array $fixturesClassNames): void
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
    protected static function cleanDatabase(): void
    {
        // load set of basic fixtures
        $databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([]);
    }
}