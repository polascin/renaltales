<?php
declare(strict_types=1);

namespace RenalTales\Model;

use PDO;
use DateTime;
use Exception;
use RenalTales\Database\DatabaseConnection;

abstract class Model
{
    protected static ?PDO $db = null;
    protected array $attributes = [];
    protected array $original = [];
    protected array $changes = [];
    protected array $relations = [];
    protected bool $exists = false;
    
    abstract protected static function getTable(): string;
    abstract protected static function getFields(): array;
    abstract protected static function getValidationRules(): array;
    
    protected static function getRelations(): array
    {
        return [];
    }
    
    protected static function getFillable(): array
    {
        return static::getFields();
    }
    
    protected static function getHidden(): array
    {
        return [];
    }
    
    protected static function getCasts(): array
    {
        return [];
    }

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
        $this->exists = !empty($this->attributes['id']);
    }

    protected static function getDatabase(): PDO
    {
        if (self::$db === null) {
            self::$db = DatabaseConnection::getInstance();
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
        return $this->saveWithTransaction();
    }
    
    public function saveWithTransaction(): bool
    {
        $db = self::getDatabase();
        $db->beginTransaction();
        
        try {
            $this->validate();
            
            if ($this->exists) {
                $result = $this->performUpdate();
            } else {
                $result = $this->performInsert();
            }
            
            if ($result) {
                $db->commit();
                $this->syncOriginal();
                return true;
            } else {
                $db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $db = self::getDatabase();
        $db->beginTransaction();
        
        try {
            $result = $this->performDelete();
            
            if ($result) {
                $db->commit();
                $this->exists = false;
                return true;
            } else {
                $db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    protected function performInsert(): bool
    {
        // Add timestamps if they exist
        if (in_array('created_at', static::getFields()) && !isset($this->attributes['created_at'])) {
            $this->attributes['created_at'] = new DateTime();
        }
        if (in_array('updated_at', static::getFields()) && !isset($this->attributes['updated_at'])) {
            $this->attributes['updated_at'] = new DateTime();
        }
        
        $fillable = static::getFillable();
        $data = array_intersect_key($this->attributes, array_flip($fillable));
        
        // Convert DateTime objects to strings
        $data = $this->prepareDataForDatabase($data);
        
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":$field", $fields);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::getTable(),
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = self::getDatabase()->prepare($sql);
        $result = $stmt->execute($data);

        if ($result) {
            $this->attributes['id'] = (int)self::getDatabase()->lastInsertId();
            $this->exists = true;
        }

        return $result;
    }

    protected function performUpdate(): bool
    {
        if (empty($this->changes)) {
            return true;
        }
        
        // Add updated_at timestamp if it exists
        if (in_array('updated_at', static::getFields())) {
            $this->attributes['updated_at'] = new DateTime();
            $this->changes['updated_at'] = $this->attributes['updated_at'];
        }
        
        $fillable = static::getFillable();
        $data = array_intersect_key($this->changes, array_flip($fillable));
        
        // Convert DateTime objects to strings
        $data = $this->prepareDataForDatabase($data);

        $fields = array_map(
            fn($field) => "$field = :$field",
            array_keys($data)
        );

        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            static::getTable(),
            implode(', ', $fields)
        );

        $stmt = self::getDatabase()->prepare($sql);
        $result = $stmt->execute(array_merge(
            $data,
            ['id' => $this->attributes['id']]
        ));

        return $result;
    }

    protected function performDelete(): bool
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE id = :id",
            static::getTable()
        );

        $stmt = self::getDatabase()->prepare($sql);
        return $stmt->execute(['id' => $this->attributes['id']]);
    }
    
    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
        $this->changes = [];
    }
    
    protected function prepareDataForDatabase(array $data): array
    {
        $casts = static::getCasts();
        
        foreach ($data as $key => $value) {
            if ($value instanceof DateTime) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            } elseif (isset($casts[$key])) {
                $data[$key] = $this->castValue($value, $casts[$key]);
            }
        }
        
        return $data;
    }
    
    protected function castValue($value, string $type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'json':
                return is_array($value) ? json_encode($value) : $value;
            case 'datetime':
                return $value instanceof DateTime ? $value : new DateTime($value);
            default:
                return $value;
        }
    }

    public static function find($id, array $with = []): ?self
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

        $instance = new static($result);
        $instance->exists = true;
        
        // Load relationships if specified
        if (!empty($with)) {
            $instance->loadRelations($with);
        }
        
        return $instance;
    }

    public static function findBy(string $field, $value, array $with = []): ?self
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

        $instance = new static($result);
        $instance->exists = true;
        
        // Load relationships if specified
        if (!empty($with)) {
            $instance->loadRelations($with);
        }
        
        return $instance;
    }

    public static function all(array $with = []): array
    {
        $sql = sprintf("SELECT * FROM %s", static::getTable());
        $stmt = self::getDatabase()->query($sql);
        
        $instances = array_map(
            function($row) {
                $instance = new static($row);
                $instance->exists = true;
                return $instance;
            },
            $stmt->fetchAll()
        );
        
        // Load relationships if specified
        if (!empty($with) && !empty($instances)) {
            static::loadRelationsForCollection($instances, $with);
        }
        
        return $instances;
    }

    public static function where(array $conditions, array $with = []): array
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

        $instances = array_map(
            function($row) {
                $instance = new static($row);
                $instance->exists = true;
                return $instance;
            },
            $stmt->fetchAll()
        );
        
        // Load relationships if specified
        if (!empty($with) && !empty($instances)) {
            static::loadRelationsForCollection($instances, $with);
        }
        
        return $instances;
    }
    
    // Helper method for finding by email (User model will override)
    public static function findByEmail(string $email): ?self
    {
        return static::findBy('email', $email);
    }
    
    // Helper method for finding records owned by a user
    public static function ownedBy(int $userId, array $with = []): array
    {
        return static::where(['user_id' => $userId], $with);
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
        $rules = static::getValidationRules();
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $fieldValue = $this->attributes[$field] ?? null;
            $ruleList = explode('|', $ruleString);
            
            foreach ($ruleList as $rule) {
                $error = $this->validateRule($field, $fieldValue, $rule);
                if ($error !== null) {
                    $errors[] = $error;
                }
            }
        }
        
        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }
        
        return true;
    }
    
    protected function validateRule(string $field, $value, string $rule): ?string
    {
        if (strpos($rule, ':') !== false) {
            [$ruleName, $ruleValue] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }
        
        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    return "Field {$field} is required";
                }
                break;
                
            case 'email':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "Field {$field} must be a valid email address";
                }
                break;
                
            case 'min':
                if ($value !== null && strlen((string)$value) < (int)$ruleValue) {
                    return "Field {$field} must be at least {$ruleValue} characters";
                }
                break;
                
            case 'max':
                if ($value !== null && strlen((string)$value) > (int)$ruleValue) {
                    return "Field {$field} must not exceed {$ruleValue} characters";
                }
                break;
                
            case 'size':
                if ($value !== null && strlen((string)$value) !== (int)$ruleValue) {
                    return "Field {$field} must be exactly {$ruleValue} characters";
                }
                break;
                
            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if ($value !== null && !in_array($value, $allowedValues)) {
                    return "Field {$field} must be one of: " . implode(', ', $allowedValues);
                }
                break;
                
            case 'exists':
                [$table, $column] = explode(',', $ruleValue);
                if ($value !== null && !$this->recordExists($table, $column, $value)) {
                    return "Field {$field} references a non-existent record";
                }
                break;
        }
        
        return null;
    }
    
    protected function recordExists(string $table, string $column, $value): bool
    {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $stmt = self::getDatabase()->prepare($sql);
        $stmt->execute(['value' => $value]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    // Relationship loading methods
    protected function loadRelations(array $relations): void
    {
        $relationDefinitions = static::getRelations();
        
        foreach ($relations as $relationName) {
            if (isset($relationDefinitions[$relationName])) {
                $this->loadRelation($relationName, $relationDefinitions[$relationName]);
            }
        }
    }
    
    protected function loadRelation(string $name, array $definition): void
    {
        $type = $definition['type'];
        $model = $definition['model'];
        $foreignKey = $definition['foreign_key'] ?? null;
        $localKey = $definition['local_key'] ?? 'id';
        
        switch ($type) {
            case 'hasMany':
                $this->relations[$name] = $model::where([$foreignKey => $this->attributes[$localKey]]);
                break;
                
            case 'belongsTo':
                $this->relations[$name] = $model::find($this->attributes[$foreignKey]);
                break;
                
            case 'hasOne':
                $results = $model::where([$foreignKey => $this->attributes[$localKey]]);
                $this->relations[$name] = !empty($results) ? $results[0] : null;
                break;
        }
    }
    
    protected static function loadRelationsForCollection(array $instances, array $relations): void
    {
        foreach ($instances as $instance) {
            $instance->loadRelations($relations);
        }
    }
    
    public function getRelation(string $name)
    {
        if (!isset($this->relations[$name])) {
            $relationDefinitions = static::getRelations();
            if (isset($relationDefinitions[$name])) {
                $this->loadRelation($name, $relationDefinitions[$name]);
            }
        }
        
        return $this->relations[$name] ?? null;
    }
}
