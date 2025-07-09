<?php

declare(strict_types=1);

namespace RenalTales\Models;

use RenalTales\Database\DatabaseConnection;
use PDO;

/**
 * Language Model
 * 
 * Handles language data operations
 */
class Language
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Get all active languages
     */
    public function getActiveLanguages(): array
    {
        $sql = "SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order, name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get language by code
     */
    public function getLanguageByCode(string $code): ?array
    {
        $sql = "SELECT * FROM languages WHERE code = :code AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage(): ?array
    {
        $sql = "SELECT * FROM languages WHERE is_default = 1 AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get fallback language (English)
     */
    public function getFallbackLanguage(): ?array
    {
        $sql = "SELECT * FROM languages WHERE code = 'en' AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Add new language
     */
    public function addLanguage(array $data): bool
    {
        $sql = "INSERT INTO languages (code, name, native_name, flag_icon, direction, is_active, sort_order, created_at, updated_at) 
                VALUES (:code, :name, :native_name, :flag_icon, :direction, :is_active, :sort_order, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'code' => $data['code'],
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'flag_icon' => $data['flag_icon'] ?? null,
            'direction' => $data['direction'] ?? 'ltr',
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0
        ]);
    }

    /**
     * Update language
     */
    public function updateLanguage(int $id, array $data): bool
    {
        $sql = "UPDATE languages SET 
                name = :name, 
                native_name = :native_name, 
                flag_icon = :flag_icon, 
                direction = :direction, 
                is_active = :is_active, 
                sort_order = :sort_order, 
                updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'flag_icon' => $data['flag_icon'] ?? null,
            'direction' => $data['direction'] ?? 'ltr',
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0
        ]);
    }

    /**
     * Delete language
     */
    public function deleteLanguage(int $id): bool
    {
        $sql = "DELETE FROM languages WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Set default language
     */
    public function setDefaultLanguage(string $code): bool
    {
        $this->db->beginTransaction();
        
        try {
            // Remove default from all languages
            $sql1 = "UPDATE languages SET is_default = 0";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute();
            
            // Set new default
            $sql2 = "UPDATE languages SET is_default = 1 WHERE code = :code";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute(['code' => $code]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Check if language code exists
     */
    public function languageExists(string $code): bool
    {
        $sql = "SELECT COUNT(*) FROM languages WHERE code = :code";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get language statistics
     */
    public function getLanguageStatistics(): array
    {
        $sql = "SELECT 
                    l.code,
                    l.name,
                    l.native_name,
                    l.is_active,
                    COUNT(t.id) as translation_count
                FROM languages l
                LEFT JOIN translations t ON l.code = t.language_code AND t.is_active = 1
                GROUP BY l.id, l.code, l.name, l.native_name, l.is_active
                ORDER BY l.sort_order, l.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get supported language codes
     */
    public function getSupportedLanguageCodes(): array
    {
        $sql = "SELECT code FROM languages WHERE is_active = 1 ORDER BY sort_order";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Activate/Deactivate language
     */
    public function toggleLanguageStatus(int $id, bool $isActive): bool
    {
        $sql = "UPDATE languages SET is_active = :is_active, updated_at = NOW() WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'is_active' => $isActive
        ]);
    }

    /**
     * Update language sort order
     */
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $sql = "UPDATE languages SET sort_order = :sort_order, updated_at = NOW() WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'sort_order' => $sortOrder
        ]);
    }
}
