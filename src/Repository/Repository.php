<?php
declare(strict_types=1);

namespace RenalTales\Repository;

use PDO;
use RenalTales\Core\Config;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

abstract class Repository
{
    protected PDO $db;
    protected string $table;
    protected string $modelClass;
    protected array $allowedOrderBy = [];
    protected ?CacheInterface $cache = null;
    protected int $cacheLifetime = 3600; // 1 hour default

    public function __construct(CacheInterface $cache = null)
    {
        if ($cache === null) {
            $filesystemAdapter = new FilesystemAdapter('renaltales', $this->cacheLifetime, dirname(__DIR__, 2) . '/var/cache');
            $this->cache = new Psr16Cache($filesystemAdapter);
        } else {
            $this->cache = $cache;
        }
        $config = new Config(dirname(__DIR__, 2) . '/config/config.php');
        
        $this->db = new PDO(
            sprintf(
                "%s:host=%s;dbname=%s;charset=%s",
                $config->get('database.driver'),
                $config->get('database.host'),
                $config->get('database.database'),
                $config->get('database.charset')
            ),
            $config->get('database.username'),
            $config->get('database.password'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    public function find(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        if ($result === false) {
            return null;
        }

        return new $this->modelClass($result);
    }

    public function findBy(array $criteria, array $orderBy = [], int $limit = null, int $offset = null)
    {
        $query = "SELECT * FROM {$this->table}";
        
        // WHERE clause
        if (!empty($criteria)) {
            $conditions = [];
            $params = [];
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $placeholders = [];
                    foreach ($value as $i => $val) {
                        $key = "{$field}_{$i}";
                        $placeholders[] = ":{$key}";
                        $params[$key] = $val;
                    }
                    $conditions[] = "{$field} IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $conditions[] = "{$field} = :{$field}";
                    $params[$field] = $value;
                }
            }
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        // ORDER BY clause
        if (!empty($orderBy)) {
            $orders = [];
            foreach ($orderBy as $field => $direction) {
                if (!in_array($field, $this->allowedOrderBy)) {
                    throw new \InvalidArgumentException("Invalid order by field: {$field}");
                }
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orders[] = "{$field} {$direction}";
            }
            if (!empty($orders)) {
                $query .= " ORDER BY " . implode(', ', $orders);
            }
        }

        // LIMIT and OFFSET
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }

        $stmt = $this->db->prepare($query);

        // Bind parameters
        if (!empty($criteria)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        
        return array_map(
            fn($row) => new $this->modelClass($row),
            $stmt->fetchAll()
        );
    }

    public function findOneBy(array $criteria)
    {
        $results = $this->findBy($criteria, [], 1);
        return !empty($results) ? $results[0] : null;
    }

    public function findAll(array $orderBy = [], int $limit = null, int $offset = null)
    {
        return $this->findBy([], $orderBy, $limit, $offset);
    }

    public function count(array $criteria = []): int
    {
        $query = "SELECT COUNT(*) FROM {$this->table}";
        
        if (!empty($criteria)) {
            $conditions = [];
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    $placeholders = array_map(
                        fn($i) => ":{$field}_{$i}",
                        array_keys($value)
                    );
                    $conditions[] = "{$field} IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $conditions[] = "{$field} = :{$field}";
                }
            }
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($query);

        if (!empty($criteria)) {
            foreach ($criteria as $field => $value) {
                if (is_array($value)) {
                    foreach ($value as $i => $val) {
                        $stmt->bindValue(":{$field}_{$i}", $val);
                    }
                } else {
                    $stmt->bindValue(":{$field}", $value);
                }
            }
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data)
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":{$field}", $fields);
        
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($query);
        
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }

        $stmt->execute();
        $id = (int)$this->db->lastInsertId();
        
        return $this->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $fields = array_map(
            fn($field) => "{$field} = :{$field}",
            array_keys($data)
        );

        $query = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            $this->table,
            implode(', ', $fields)
        );

        $stmt = $this->db->prepare($query);
        
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    protected function executeQuery(string $query, array $params = []): array
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function executeSingleResult(string $query, array $params = [])
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result === false ? null : $result;
    }

    protected function getCacheKey(string $method, array $params = []): string
    {
        return sprintf(
            '%s:%s:%s:%s',
            $this->table,
            $method,
            md5(serialize($params)),
            $this->getCurrentLanguage()
        );
    }

    protected function getCurrentLanguage(): string
    {
        // Get the current language from the session or config
        return $_SESSION['language'] ?? 'en';
    }

    protected function getFromCache(string $key)
    {
        try {
            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }
        } catch (\Exception $e) {
            // Log cache error but continue without cache
            error_log("Cache error: " . $e->getMessage());
        }
        return null;
    }

    protected function setInCache(string $key, $value, ?int $lifetime = null): void
    {
        try {
            $this->cache->set(
                $key,
                $value,
                $lifetime ?? $this->cacheLifetime
            );
        } catch (\Exception $e) {
            // Log cache error but continue without cache
            error_log("Cache error: " . $e->getMessage());
        }
    }

    protected function invalidateCache(string $pattern = null): void
    {
        try {
            if ($pattern === null) {
                $pattern = $this->table . ':*';
            }
            $this->cache->clear();
        } catch (\Exception $e) {
            // Log cache error but continue
            error_log("Cache invalidation error: " . $e->getMessage());
        }
    }

    protected function shouldCache(string $method): bool
    {
        // Define methods that should be cached
        $cacheMethods = [
            'find',
            'findAll',
            'findBy',
            'findOneBy',
            'count',
            'getStatistics',
            'getRecentItems'
        ];

        return in_array($method, $cacheMethods);
    }
}
