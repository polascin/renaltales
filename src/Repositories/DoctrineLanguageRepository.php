<?php

declare(strict_types=1);

namespace RenalTales\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use RenalTales\Entities\Language;
use RenalTales\Contracts\RepositoryInterface;

/**
 * Doctrine Language Repository
 *
 * Repository for Language entities using Doctrine ORM.
 * Provides methods to find, create, update, and delete languages.
 *
 * @package RenalTales\Repositories
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 * @extends EntityRepository<Language>
 */
class DoctrineLanguageRepository extends EntityRepository implements RepositoryInterface
{
    /**
     * Find all languages
     *
     * @return Language[]
     */
    public function findAll(): array
    {
        return $this->findBy([], ['sortOrder' => 'ASC', 'name' => 'ASC']);
    }

    /**
     * Find active languages
     *
     * @return Language[]
     */
    public function findActive(): array
    {
        return $this->findBy(['active' => true], ['sortOrder' => 'ASC', 'name' => 'ASC']);
    }

    /**
     * Find language by code
     *
     * @param string $code Language code
     * @return Language|null
     */
    public function findByCode(string $code): ?Language
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * Find the default language
     *
     * @return Language|null
     */
    public function findDefault(): ?Language
    {
        return $this->findOneBy(['isDefault' => true]);
    }

    /**
     * Check if language code exists
     *
     * @param string $code Language code
     * @param int|null $excludeId Exclude language with this ID
     * @return bool
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.code = :code')
            ->setParameter('code', $code);

        if ($excludeId !== null) {
            $qb->andWhere('l.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Set language as default and unset others
     *
     * @param Language $language Language to set as default
     * @return void
     * @throws ORMException
     */
    public function setAsDefault(Language $language): void
    {
        $em = $this->getEntityManager();

        // Start transaction
        $em->beginTransaction();

        try {
            // Unset current default language
            $qb = $this->createQueryBuilder('l')
                ->update()
                ->set('l.isDefault', ':false')
                ->where('l.isDefault = :true')
                ->setParameter('false', false)
                ->setParameter('true', true);

            $qb->getQuery()->execute();

            // Set new default language
            $language->setIsDefault(true);
            $em->persist($language);
            $em->flush();

            $em->commit();
        } catch (ORMException $e) {
            $em->rollback();
            throw $e;
        }
    }

    /**
     * Find languages by active status
     *
     * @param bool $active Active status
     * @return Language[]
     */
    public function findByActive(bool $active): array
    {
        return $this->findBy(['active' => $active], ['sortOrder' => 'ASC', 'name' => 'ASC']);
    }

    /**
     * Find languages for selection (code => name pairs)
     *
     * @param bool $activeOnly Only active languages
     * @return array<string, string>
     */
    public function findForSelection(bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('l')
            ->select('l.code, l.nativeName')
            ->orderBy('l.sortOrder', 'ASC')
            ->addOrderBy('l.name', 'ASC');

        if ($activeOnly) {
            $qb->where('l.active = :active')
                ->setParameter('active', true);
        }

        $results = $qb->getQuery()->getArrayResult();

        $languages = [];
        foreach ($results as $result) {
            $languages[$result['code']] = $result['nativeName'];
        }

        return $languages;
    }

    /**
     * Get languages count
     *
     * @param ?bool $activeOnly Count only active languages
     * @return int
     */
    public function countLanguages(?bool $activeOnly = null): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');

        if ($activeOnly !== null) {
            $qb->where('l.active = :active')
                ->setParameter('active', $activeOnly);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find languages by region
     *
     * @param string $region Region code
     * @return Language[]
     */
    public function findByRegion(string $region): array
    {
        return $this->findBy(['region' => $region], ['sortOrder' => 'ASC', 'name' => 'ASC']);
    }

    /**
     * Find languages by direction
     *
     * @param string $direction Direction (ltr or rtl)
     * @return Language[]
     */
    public function findByDirection(string $direction): array
    {
        return $this->findBy(['direction' => $direction], ['sortOrder' => 'ASC', 'name' => 'ASC']);
    }

    /**
     * Search languages by name or code
     *
     * @param string $query Search query
     * @param bool $activeOnly Search only active languages
     * @return Language[]
     */
    public function search(string $query, bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('l')
            ->where('l.name LIKE :query OR l.nativeName LIKE :query OR l.code LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('l.sortOrder', 'ASC')
            ->addOrderBy('l.name', 'ASC');

        if ($activeOnly) {
            $qb->andWhere('l.active = :active')
                ->setParameter('active', true);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get next sort order
     *
     * @return int
     */
    public function getNextSortOrder(): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('MAX(l.sortOrder)')
            ->getQuery();

        $maxOrder = $qb->getSingleScalarResult();

        return $maxOrder ? (int) $maxOrder + 1 : 1;
    }

    /**
     * Create new language
     *
     * @param array<string, mixed> $data Language data
     * @return Language
     * @throws ORMException
     */
    public function create(array $data): mixed
    {
        $language = new Language(
            $data['code'],
            $data['name'],
            $data['nativeName'] ?? $data['name'],
            $data['active'] ?? true,
            $data['isDefault'] ?? false
        );

        if (isset($data['direction'])) {
            $language->setDirection($data['direction']);
        }

        if (isset($data['region'])) {
            $language->setRegion($data['region']);
        }

        if (isset($data['sortOrder'])) {
            $language->setSortOrder($data['sortOrder']);
        } else {
            $language->setSortOrder($this->getNextSortOrder());
        }

        $em = $this->getEntityManager();
        $em->persist($language);
        $em->flush();

        return $language;
    }

    /**
     * Update language
     *
     * @param mixed $id Language ID
     * @param array<string, mixed> $data Update data
     * @return Language
     * @throws ORMException
     */
    public function update($id, array $data): mixed
    {
        $language = $this->find($id);
        if (!$language) {
            throw new ORMException('Language not found');
        }

        return $this->updateEntity($language, $data);
    }

    /**
     * Update language entity
     *
     * @param Language $language Language to update
     * @param array<string, mixed> $data Update data
     * @return Language
     * @throws ORMException
     */
    public function updateEntity(Language $language, array $data): Language
    {
        if (isset($data['code'])) {
            $language->setCode($data['code']);
        }

        if (isset($data['name'])) {
            $language->setName($data['name']);
        }

        if (isset($data['nativeName'])) {
            $language->setNativeName($data['nativeName']);
        }

        if (isset($data['active'])) {
            $language->setActive($data['active']);
        }

        if (isset($data['isDefault'])) {
            $language->setIsDefault($data['isDefault']);
        }

        if (isset($data['direction'])) {
            $language->setDirection($data['direction']);
        }

        if (isset($data['region'])) {
            $language->setRegion($data['region']);
        }

        if (isset($data['sortOrder'])) {
            $language->setSortOrder($data['sortOrder']);
        }

        $em = $this->getEntityManager();
        $em->persist($language);
        $em->flush();

        return $language;
    }

    /**
     * Delete language
     *
     * @param mixed $id Language ID
     * @return bool
     * @throws ORMException
     */
    public function delete($id): bool
    {
        $language = $this->find($id);
        if (!$language) {
            return false;
        }

        $em = $this->getEntityManager();
        $em->remove($language);
        $em->flush();

        return true;
    }

    /**
     * Delete language entity
     *
     * @param Language $language Language to delete
     * @return void
     * @throws ORMException
     */
    public function deleteEntity(Language $language): void
    {
        $em = $this->getEntityManager();
        $em->remove($language);
        $em->flush();
    }

    /**
     * Save language
     *
     * @param Language $language Language to save
     * @return Language
     * @throws ORMException
     */
    public function save(Language $language): Language
    {
        $em = $this->getEntityManager();
        $em->persist($language);
        $em->flush();

        return $language;
    }

    /**
     * Count entities
     *
     * @param array<string, mixed> $criteria Search criteria
     * @return int Number of entities
     */
    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("l.$field = :$field")
                ->setParameter($field, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Check if entity exists
     *
     * @param mixed $id The identifier
     * @return bool True if exists, false otherwise
     */
    public function exists($id): bool
    {
        return $this->find($id) !== null;
    }
}
