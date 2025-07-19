<?php

declare(strict_types=1);

namespace RenalTales\Tests\Traits;

use RenalTales\Core\DatabaseManager;
use RenalTales\Entities\Language;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Database trait for test database management
 */
trait DatabaseTrait
{
    /**
     * @var EntityManagerInterface|null The entity manager instance
     */
    protected ?EntityManagerInterface $entityManager = null;

    /**
     * Set up the test database
     */
    protected function setUpDatabase(): void
    {
        $this->entityManager = $this->getService(EntityManagerInterface::class);

        // Create schema
        $this->createSchema();

        // Seed basic test data
        $this->seedDatabase();
    }

    /**
     * Tear down the test database
     */
    protected function tearDownDatabase(): void
    {
        if ($this->entityManager) {
            // Drop schema
            $this->dropSchema();

            // Clear entity manager
            $this->entityManager->clear();
        }
    }

    /**
     * Create database schema
     */
    protected function createSchema(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // Drop existing schema if it exists
        $schemaTool->dropSchema($metadata);

        // Create new schema
        $schemaTool->createSchema($metadata);
    }

    /**
     * Drop database schema
     */
    protected function dropSchema(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
    }

    /**
     * Seed the database with basic test data
     */
    protected function seedDatabase(): void
    {
        // Create basic language entries
        $languages = [
            ['en', 'English', 'English', true],
            ['sk', 'Slovak', 'Slovenčina', false],
            ['cs', 'Czech', 'Čeština', false],
            ['de', 'German', 'Deutsch', false],
        ];

        foreach ($languages as $langData) {
            $language = new Language();
            $language->setCode($langData[0]);
            $language->setName($langData[1]);
            $language->setNativeName($langData[2]);
            $language->setIsDefault($langData[3]);
            $language->setIsActive(true);

            $this->entityManager->persist($language);
        }

        $this->entityManager->flush();
    }

    /**
     * Get the entity manager
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Refresh an entity from the database
     */
    protected function refreshEntity($entity): void
    {
        $this->entityManager->refresh($entity);
    }

    /**
     * Clear the entity manager
     */
    protected function clearEntityManager(): void
    {
        $this->entityManager->clear();
    }

    /**
     * Begin a database transaction
     */
    protected function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * Commit a database transaction
     */
    protected function commitTransaction(): void
    {
        $this->entityManager->commit();
    }

    /**
     * Rollback a database transaction
     */
    protected function rollbackTransaction(): void
    {
        $this->entityManager->rollback();
    }

    /**
     * Execute a raw SQL query
     */
    protected function executeQuery(string $sql, array $params = []): mixed
    {
        return $this->entityManager->getConnection()->executeQuery($sql, $params);
    }

    /**
     * Execute a raw SQL statement
     */
    protected function executeStatement(string $sql, array $params = []): int
    {
        return $this->entityManager->getConnection()->executeStatement($sql, $params);
    }

    /**
     * Create a test entity
     */
    protected function createTestEntity(string $entityClass, array $data = []): object
    {
        $entity = new $entityClass();

        foreach ($data as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * Remove a test entity
     */
    protected function removeTestEntity($entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * Find entity by criteria
     */
    protected function findEntity(string $entityClass, array $criteria = []): ?object
    {
        return $this->entityManager->getRepository($entityClass)->findOneBy($criteria);
    }

    /**
     * Find all entities by criteria
     */
    protected function findEntities(string $entityClass, array $criteria = []): array
    {
        return $this->entityManager->getRepository($entityClass)->findBy($criteria);
    }

    /**
     * Count entities by criteria
     */
    protected function countEntities(string $entityClass, array $criteria = []): int
    {
        return $this->entityManager->getRepository($entityClass)->count($criteria);
    }

    /**
     * Assert that an entity exists in the database
     */
    protected function assertEntityExists(string $entityClass, array $criteria = [], string $message = ''): void
    {
        $entity = $this->findEntity($entityClass, $criteria);

        $this->assertNotNull(
            $entity,
            $message ?: "Entity of type '{$entityClass}' with criteria " . json_encode($criteria) . " should exist"
        );
    }

    /**
     * Assert that an entity does not exist in the database
     */
    protected function assertEntityNotExists(string $entityClass, array $criteria = [], string $message = ''): void
    {
        $entity = $this->findEntity($entityClass, $criteria);

        $this->assertNull(
            $entity,
            $message ?: "Entity of type '{$entityClass}' with criteria " . json_encode($criteria) . " should not exist"
        );
    }

    /**
     * Assert entity count
     */
    protected function assertEntityCount(string $entityClass, int $expectedCount, array $criteria = [], string $message = ''): void
    {
        $count = $this->countEntities($entityClass, $criteria);

        $this->assertEquals(
            $expectedCount,
            $count,
            $message ?: "Expected {$expectedCount} entities of type '{$entityClass}' but found {$count}"
        );
    }
}
