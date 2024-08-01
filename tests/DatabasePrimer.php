<?php

namespace App\Tests;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Database primer class for automated pre-migration of testing database
 * @see https://www.sitepoint.com/quick-tip-testing-symfony-apps-with-a-disposable-database/
 */
class DatabasePrimer
{
    /**
     * Method used to load overall entities metadata 
     * and apply them to the testing database serving
     * as automated migration tool for testing environment.
     * 
     * The prime() method of this class is ment to be called 
     * in the setUp() method of Symfony's KernelTestCase or 
     * WebTestCase extending test classes.
     * 
     * If nature of test class and test suite configuration allows it,
     * the prime() method can be even called once per given test class only
     * within the setUpBeforeClass() method saving time and resources in consequence.
     * (e.g. when DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension is used erasing     )
     * (     data created during the run of the test case through wrapping whole test  )
     * (     case into transaction being auto rolled back after test case is finished. )
     * 
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @throws \LogicException
     * @return void
     */
    public static function prime(KernelInterface $kernel)
    {
        // Make sure we are in the test environment
        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Primer must be executed in the test environment');
        }

        // Get the entity manager from the service container
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        // Run the schema update tool using our entities metadata
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadatas);

        // If you are using the Doctrine Fixtures Bundle you could load these here
    }
}