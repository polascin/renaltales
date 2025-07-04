<?php
declare(strict_types=1);

namespace RenalTales\Model;

use PDO;
use DateTime;
use RenalTales\Core\Config;

abstract class Model
{
    protected static ?PDO $db = null;
    protected array $attributes = [];
    protected array $original = [];
    protected array $changes = [];
    
    abstract protected static function getTable(): string;
    abstract protected static function getFields(): array;
    abstract protected static function getValidationRules(): array;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    protected static function getDatabase(): PDO
    {
        if (self::$db === null) {
            $config = new Config(dirname(__DIR__, 2) . '/config/config.php');
            
            self::$db = new PDO(
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

        return self::$db;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value)
    {
        if (in_array($name, static::getFields())) {
            $this->attributes[$name] = $value;
            $this->changes[$name] = $value;
        }
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::getFields())) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function isDirty(): bool
    {
        return !empty($this->changes);
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function save(): bool
    {
        if (isset($this->attributes['id'])) {
            return $this->update();
        }
        return $this->insert();
    }

    protected function insert(): bool
    {
        $fields = array_keys($this->attributes);
        $placeholders = array_map(fn($field) => ":$field", $fields);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::getTable(),
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = self::getDatabase()->prepare($sql);
        $result = $stmt->execute($this->attributes);

        if ($result) {
            $this->attributes['id'] = (int)self::getDatabase()->lastInsertId();
            $this->original = $this->attributes;
            $this->changes = [];
        }

        return $result;
    }

    protected function update(): bool
    {
        if (empty($this->changes)) {
            return true;
        }

        $fields = array_map(
            fn($field) => "$field = :$field",
            array_keys($this->changes)
        );

        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            static::getTable(),
            implode(', ', $fields)
        );

        $stmt = self::getDatabase()->prepare($sql);
        $result = $stmt->execute(array_merge(
            $this->changes,
            ['id' => $this->attributes['id']]
        ));

        if ($result) {
            $this->original = $this->attributes;
            $this->changes = [];
        }

        return $result;
    }

    public function delete(): bool
    {
        if (!isset($this->attributes['id'])) {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE id = :id",
            static::getTable()
        );

        $stmt = self::getDatabase()->prepare($sql);
        return $stmt->execute(['id' => $this->attributes['id']]);
    }

    public static function find($id): ?self
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE id = :id",
            static::getTable()
        );

        $stmt = self::getDatabase()->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        if ($result === false) {
            return null;
        }

        return new static($result);
    }

    public static function findBy(string $field, $value): ?self
    {
        if (!in_array($field, static::getFields())) {
            throw new \InvalidArgumentException("Invalid field: $field");
        }

        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = :value",
            static::getTable(),
            $field
        );

        $stmt = self::getDatabase()->prepare($sql);
        $stmt->execute(['value' => $value]);
        
        $result = $stmt->fetch();
        if ($result === false) {
            return null;
        }

        return new static($result);
    }

    public static function all(): array
    {
        $sql = sprintf("SELECT * FROM %s", static::getTable());
        $stmt = self::getDatabase()->query($sql);
        
        return array_map(
            fn($row) => new static($row),
            $stmt->fetchAll()
        );
    }

    public static function where(array $conditions): array
    {
        $where = [];
        $params = [];

        foreach ($conditions as $field => $value) {
            if (!in_array($field, static::getFields())) {
                throw new \InvalidArgumentException("Invalid field: $field");
            }
            $where[] = "$field = :$field";
            $params[$field] = $value;
        }

        $sql = sprintf(
            "SELECT * FROM %s WHERE %s",
            static::getTable(),
            implode(' AND ', $where)
        );

        $stmt = self::getDatabase()->prepare($sql);
        $stmt->execute($params);

        return array_map(
            fn($row) => new static($row),
            $stmt->fetchAll()
        );
    }

    protected function castAttribute($key, $value)
    {
        if ($value === null) {
            return null;
        }

        // Handle date fields
        if (in_array($key, ['created_at', 'updated_at', 'deleted_at', 'published_at', 'email_verified_at'])) {
            return $value instanceof DateTime ? $value : new DateTime($value);
        }

        return $value;
    }

    protected function validate(): bool
    {
        // Basic validation implementation
        // In a real application, you might want to use a validation library
        $rules = static::getValidationRules();
        foreach ($rules as $field => $rule) {
            if (!isset($this->attributes[$field]) && strpos($rule, 'required') !== false) {
                throw new \InvalidArgumentException("Field $field is required");
            }
            // Add more validation rules as needed
        }
        return true;
    }
}
