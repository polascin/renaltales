<?php

declare(strict_types=1);

namespace RenalTales\Models;

use RenalTales\Database\DatabaseConnection;
use PDO;

/**
 * Translation Model
 * 
 * Handles translation data operations
 */
class Translation
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Get translation by key and language
     */
    public function getTranslation(string $key, string $languageCode, string $group = 'default'): ?string
    {
        $sql = "SELECT translation_text FROM translations 
                WHERE key_name = :key AND language_code = :lang AND group_name = :group AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'key' => $key,
            'lang' => $languageCode,
            'group' => $group
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['translation_text'] : null;
    }

    /**
     * Get all translations for a language
     */
    public function getAllTranslations(string $languageCode, string $group = null): array
    {
        $sql = "SELECT key_name, translation_text, group_name FROM translations 
                WHERE language_code = :lang AND is_active = 1";
        
        $params = ['lang' => $languageCode];
        
        if ($group !== null) {
            $sql .= " AND group_name = :group";
            $params['group'] = $group;
        }
        
        $sql .= " ORDER BY group_name, key_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create or update translation
     */
    public function saveTranslation(string $key, string $languageCode, string $text, string $group = 'default'): bool
    {
        $sql = "INSERT INTO translations (language_code, key_name, translation_text, group_name, created_at, updated_at) 
                VALUES (:lang, :key, :text, :group, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                translation_text = :text, updated_at = NOW()";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'lang' => $languageCode,
            'key' => $key,
            'text' => $text,
            'group' => $group
        ]);
    }

    /**
     * Delete translation
     */
    public function deleteTranslation(string $key, string $languageCode, string $group = 'default'): bool
    {
        $sql = "DELETE FROM translations 
                WHERE key_name = :key AND language_code = :lang AND group_name = :group";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'key' => $key,
            'lang' => $languageCode,
            'group' => $group
        ]);
    }

    /**
     * Get all translation keys
     */
    public function getAllKeys(string $group = null): array
    {
        $sql = "SELECT DISTINCT key_name, group_name FROM translations WHERE is_active = 1";
        
        if ($group !== null) {
            $sql .= " AND group_name = :group";
        }
        
        $sql .= " ORDER BY group_name, key_name";
        
        $stmt = $this->db->prepare($sql);
        
        if ($group !== null) {
            $stmt->execute(['group' => $group]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get translation groups
     */
    public function getGroups(): array
    {
        $sql = "SELECT DISTINCT group_name FROM translations WHERE is_active = 1 ORDER BY group_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get translation statistics
     */
    public function getStatistics(): array
    {
        $sql = "SELECT 
                    language_code,
                    group_name,
                    COUNT(*) as translation_count
                FROM translations 
                WHERE is_active = 1 
                GROUP BY language_code, group_name
                ORDER BY language_code, group_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search translations
     */
    public function searchTranslations(string $search, string $languageCode = null): array
    {
        $sql = "SELECT language_code, key_name, translation_text, group_name 
                FROM translations 
                WHERE is_active = 1 AND (
                    key_name LIKE :search OR 
                    translation_text LIKE :search
                )";
        
        $params = ['search' => '%' . $search . '%'];
        
        if ($languageCode !== null) {
            $sql .= " AND language_code = :lang";
            $params['lang'] = $languageCode;
        }
        
        $sql .= " ORDER BY language_code, group_name, key_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Import translations from array
     */
    public function importTranslations(array $translations, string $languageCode): bool
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($translations as $key => $value) {
                if (is_array($value)) {
                    // Handle grouped translations
                    foreach ($value as $subKey => $subValue) {
                        $this->saveTranslation($subKey, $languageCode, $subValue, $key);
                    }
                } else {
                    // Handle simple translations
                    $this->saveTranslation($key, $languageCode, $value);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Export translations to array
     */
    public function exportTranslations(string $languageCode, string $group = null): array
    {
        $translations = $this->getAllTranslations($languageCode, $group);
        $result = [];
        
        foreach ($translations as $translation) {
            if ($group === null) {
                $result[$translation['group_name']][$translation['key_name']] = $translation['translation_text'];
            } else {
                $result[$translation['key_name']] = $translation['translation_text'];
            }
        }
        
        return $result;
    }
}
