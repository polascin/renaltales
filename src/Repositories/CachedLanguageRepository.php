<?php

declare(strict_types=1);

namespace RenalTales\Repositories;

use RenalTales\Contracts\RepositoryInterface;
use RenalTales\Core\CacheManager;
use RenalTales\Core\AsyncManager;
use RenalTales\Models\LanguageModel;
use RenalTales\Repositories\LanguageRepository;
use React\Promise\PromiseInterface;
use Exception;

/**
 * Cached Language Repository
 *
 * Provides cached data access functionality for the LanguageModel
 * with Redis caching and async operations support.
 *
 * @package RenalTales\Repositories
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class CachedLanguageRepository implements RepositoryInterface
{
    /**
     * @var LanguageRepository Base repository
     */
    private LanguageRepository $repository;

    /**
     * @var CacheManager Cache manager
     */
    private CacheManager $cache;

    /**
     * @var AsyncManager|null Async manager
     */
    private ?AsyncManager $asyncManager = null;

    /**
     * @var int Default cache TTL in seconds
     */
    private int $defaultTtl = 3600;

    /**
     * Cache key prefixes
     */
    private const CACHE_PREFIX = 'language_';
    private const CACHE_ALL_LANGUAGES = 'all_languages';
    private const CACHE_LANGUAGE_EXISTS = 'language_exists_';
    private const CACHE_LANGUAGE_COUNT = 'language_count';
    private const CACHE_LANGUAGE_NAMES = 'language_names';
    private const CACHE_TRANSLATIONS = 'translations_';

    /**
     * Constructor
     *
     * @param LanguageRepository $repository Base repository
     * @param CacheManager $cache Cache manager
     * @param AsyncManager|null $asyncManager Async manager
     */
    public function __construct(
        LanguageRepository $repository,
        CacheManager $cache,
        ?AsyncManager $asyncManager = null
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->asyncManager = $asyncManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $id;

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->repository->find($id);

        if ($result !== null) {
            $this->cache->set($cacheKey, $result, $this->defaultTtl);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->cache->remember(
            self::CACHE_ALL_LANGUAGES,
            function () {
                return $this->repository->findAll();
            },
            $this->defaultTtl
        );
    }

    /**
     * Find all languages asynchronously
     *
     * @return PromiseInterface<array<string>>
     */
    public function findAllAsync(): PromiseInterface
    {
        if (!$this->asyncManager) {
            return \React\Promise\resolve($this->findAll());
        }

        $cacheKey = self::CACHE_ALL_LANGUAGES;
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return \React\Promise\resolve($cached);
        }

        return $this->asyncManager->executeTask('load_all_languages')
            ->then(function ($result) use ($cacheKey) {
                $this->cache->set($cacheKey, $result, $this->defaultTtl);
                return $result;
            });
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $cacheKey = $this->buildCacheKey('findBy', $criteria, $orderBy, $limit, $offset);

        return $this->cache->remember(
            $cacheKey,
            function () use ($criteria, $orderBy, $limit, $offset) {
                return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
            },
            $this->defaultTtl
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria): mixed
    {
        $cacheKey = $this->buildCacheKey('findOneBy', $criteria);

        return $this->cache->remember(
            $cacheKey,
            function () use ($criteria) {
                return $this->repository->findOneBy($criteria);
            },
            $this->defaultTtl
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): mixed
    {
        $result = $this->repository->create($data);

        if ($result) {
            $this->invalidateCache();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data): mixed
    {
        $result = $this->repository->update($id, $data);

        if ($result) {
            $this->invalidateCache();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id): bool
    {
        $result = $this->repository->delete($id);

        if ($result) {
            $this->invalidateCache();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function count(array $criteria = []): int
    {
        if (empty($criteria)) {
            return $this->cache->remember(
                self::CACHE_LANGUAGE_COUNT,
                function () {
                    return $this->repository->count();
                },
                $this->defaultTtl
            );
        }

        $cacheKey = $this->buildCacheKey('count', $criteria);

        return $this->cache->remember(
            $cacheKey,
            function () use ($criteria) {
                return $this->repository->count($criteria);
            },
            $this->defaultTtl
        );
    }

    /**
     * {@inheritdoc}
     */
    public function exists($id): bool
    {
        $cacheKey = self::CACHE_LANGUAGE_EXISTS . $id;

        return $this->cache->remember(
            $cacheKey,
            function () use ($id) {
                return $this->repository->exists($id);
            },
            $this->defaultTtl
        );
    }

    /**
     * Check if language exists asynchronously
     *
     * @param string $id Language ID
     * @return PromiseInterface<bool>
     */
    public function existsAsync(string $id): PromiseInterface
    {
        if (!$this->asyncManager) {
            return \React\Promise\resolve($this->exists($id));
        }

        $cacheKey = self::CACHE_LANGUAGE_EXISTS . $id;
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return \React\Promise\resolve($cached);
        }

        return $this->asyncManager->executeTask('check_language_exists', ['id' => $id])
            ->then(function ($result) use ($cacheKey) {
                $this->cache->set($cacheKey, $result, $this->defaultTtl);
                return $result;
            });
    }

    /**
     * Get language names with caching
     *
     * @return array<string, string> Language code => Native name mapping
     */
    public function getLanguageNames(): array
    {
        return $this->cache->remember(
            self::CACHE_LANGUAGE_NAMES,
            function () {
                $languages = $this->findAll();
                $names = [];

                foreach ($languages as $language) {
                    // This would typically call a method to get native name
                    // For now, we'll use a placeholder
                    $names[$language] = $this->getLanguageNativeName($language);
                }

                return $names;
            },
            86400 // Cache for 24 hours
        );
    }

    /**
     * Get translations with caching
     *
     * @param string $language Language code
     * @return array<string, string> Translation key => value mapping
     */
    public function getTranslations(string $language): array
    {
        $cacheKey = self::CACHE_TRANSLATIONS . $language;

        return $this->cache->remember(
            $cacheKey,
            function () use ($language) {
                return $this->loadTranslationsFromFile($language);
            },
            86400 // Cache for 24 hours
        );
    }

    /**
     * Get translations asynchronously
     *
     * @param string $language Language code
     * @return PromiseInterface<array<string, string>>
     */
    public function getTranslationsAsync(string $language): PromiseInterface
    {
        if (!$this->asyncManager) {
            return \React\Promise\resolve($this->getTranslations($language));
        }

        $cacheKey = self::CACHE_TRANSLATIONS . $language;
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return \React\Promise\resolve($cached);
        }

        return $this->asyncManager->executeTask('load_translations', ['language' => $language])
            ->then(function ($result) use ($cacheKey) {
                $this->cache->set($cacheKey, $result, 86400);
                return $result;
            });
    }

    /**
     * Preload frequently used data
     *
     * @return PromiseInterface<void>
     */
    public function preloadData(): PromiseInterface
    {
        if (!$this->asyncManager) {
            // Synchronous preload
            $this->findAll();
            $this->getLanguageNames();
            return \React\Promise\resolve();
        }

        $promises = [
            $this->findAllAsync(),
            $this->asyncManager->executeTask('preload_language_names'),
        ];

        return \React\Promise\all($promises)
            ->then(function () {
                // Preload translations for common languages
                $commonLanguages = ['en', 'es', 'fr', 'de', 'it'];
                $translationPromises = [];

                foreach ($commonLanguages as $language) {
                    if ($this->exists($language)) {
                        $translationPromises[] = $this->getTranslationsAsync($language);
                    }
                }

                return \React\Promise\all($translationPromises);
            });
    }

    /**
     * Batch operations with caching
     *
     * @param array<string> $languageCodes Language codes to check
     * @return array<string, bool> Language code => exists mapping
     */
    public function batchExists(array $languageCodes): array
    {
        $result = [];
        $uncachedCodes = [];

        // Check cache first
        foreach ($languageCodes as $code) {
            $cacheKey = self::CACHE_LANGUAGE_EXISTS . $code;
            $cached = $this->cache->get($cacheKey);

            if ($cached !== null) {
                $result[$code] = $cached;
            } else {
                $uncachedCodes[] = $code;
            }
        }

        // Load uncached data
        if (!empty($uncachedCodes)) {
            foreach ($uncachedCodes as $code) {
                $exists = $this->repository->exists($code);
                $result[$code] = $exists;

                // Cache the result
                $cacheKey = self::CACHE_LANGUAGE_EXISTS . $code;
                $this->cache->set($cacheKey, $exists, $this->defaultTtl);
            }
        }

        return $result;
    }

    /**
     * Batch operations asynchronously
     *
     * @param array<string> $languageCodes Language codes to check
     * @return PromiseInterface<array<string, bool>>
     */
    public function batchExistsAsync(array $languageCodes): PromiseInterface
    {
        if (!$this->asyncManager) {
            return \React\Promise\resolve($this->batchExists($languageCodes));
        }

        return $this->asyncManager->executeTask('batch_check_languages', ['codes' => $languageCodes])
            ->then(function ($result) {
                // Cache results
                foreach ($result as $code => $exists) {
                    $cacheKey = self::CACHE_LANGUAGE_EXISTS . $code;
                    $this->cache->set($cacheKey, $exists, $this->defaultTtl);
                }

                return $result;
            });
    }

    /**
     * Invalidate cache
     *
     * @return void
     */
    public function invalidateCache(): void
    {
        $keysToDelete = [
            self::CACHE_ALL_LANGUAGES,
            self::CACHE_LANGUAGE_COUNT,
            self::CACHE_LANGUAGE_NAMES,
        ];

        $this->cache->deleteMultiple($keysToDelete);
    }

    /**
     * Warm up cache
     *
     * @return void
     */
    public function warmUpCache(): void
    {
        // Preload commonly used data
        $this->findAll();
        $this->getLanguageNames();
        $this->count();

        // Preload translations for common languages
        $commonLanguages = ['en', 'es', 'fr', 'de'];
        foreach ($commonLanguages as $language) {
            if ($this->exists($language)) {
                $this->getTranslations($language);
            }
        }
    }

    /**
     * Get cache statistics
     *
     * @return array<string, mixed> Cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'cache_driver' => $this->cache->isRedisAvailable() ? 'redis' : 'file',
            'cache_stats' => $this->cache->getStats(),
            'cached_keys' => $this->getCachedKeys(),
        ];
    }

    /**
     * Get cached keys
     *
     * @return array<string> Cached keys
     */
    private function getCachedKeys(): array
    {
        $keys = [
            self::CACHE_ALL_LANGUAGES,
            self::CACHE_LANGUAGE_COUNT,
            self::CACHE_LANGUAGE_NAMES,
        ];

        $existingKeys = [];
        foreach ($keys as $key) {
            if ($this->cache->has($key)) {
                $existingKeys[] = $key;
            }
        }

        return $existingKeys;
    }

    /**
     * Build cache key from parameters
     *
     * @param string $method Method name
     * @param mixed ...$params Parameters
     * @return string Cache key
     */
    private function buildCacheKey(string $method, ...$params): string
    {
        $keyParts = [$method];

        foreach ($params as $param) {
            if (is_array($param)) {
                $keyParts[] = md5(serialize($param));
            } else {
                $keyParts[] = (string) $param;
            }
        }

        return implode('_', $keyParts);
    }

    /**
     * Get language native name
     *
     * @param string $language Language code
     * @return string Native name
     */
    private function getLanguageNativeName(string $language): string
    {
        // This is a placeholder - in real implementation, this would
        // load from a configuration file or database
        $names = [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'sk' => 'Slovenčina',
            'cs' => 'Čeština',
        ];

        return $names[$language] ?? $language;
    }

    /**
     * Load translations from file
     *
     * @param string $language Language code
     * @return array<string, string> Translations
     */
    private function loadTranslationsFromFile(string $language): array
    {
        $filePath = APP_ROOT . "/resources/lang/{$language}.php";

        if (file_exists($filePath)) {
            $translations = include $filePath;
            return is_array($translations) ? $translations : [];
        }

        return [];
    }

    /**
     * Set cache TTL
     *
     * @param int $ttl TTL in seconds
     * @return void
     */
    public function setCacheTtl(int $ttl): void
    {
        $this->defaultTtl = $ttl;
    }

    /**
     * Get cache TTL
     *
     * @return int TTL in seconds
     */
    public function getCacheTtl(): int
    {
        return $this->defaultTtl;
    }
}
