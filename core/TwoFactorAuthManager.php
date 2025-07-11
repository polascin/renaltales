<?php

/**
 * TwoFactorAuthManager - Two-Factor Authentication Manager
 * 
 * Handles TOTP (Time-based One-Time Password) authentication using Google Authenticator
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/Database.php';

class TwoFactorAuthManager {
    
    private $db;
    private $issuer = 'RenalTales';
    private $digits = 6;
    private $period = 30; // seconds
    private $algorithm = 'sha1';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate secret key for user
     * 
     * @param int $userId User ID
     * @return string|false Secret key or false on failure
     */
    public function generateSecret($userId) {
        try {
            // Generate random secret
            $secret = $this->generateRandomSecret();
            
            // Generate backup codes
            $backupCodes = $this->generateBackupCodes();
            
            // Insert or update 2FA record
            $sql = "INSERT INTO user_two_factor_auth (user_id, secret_key, backup_codes, recovery_codes) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    secret_key = VALUES(secret_key),
                    backup_codes = VALUES(backup_codes),
                    recovery_codes = VALUES(recovery_codes),
                    is_enabled = FALSE,
                    updated_at = NOW()";
            
            $result = $this->db->execute($sql, [
                $userId,
                $secret,
                json_encode($backupCodes),
                json_encode($backupCodes) // Same as backup codes initially
            ]);
            
            return $result ? $secret : false;
            
        } catch (Exception $e) {
            error_log('2FA secret generation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate QR code URL for authenticator apps
     * 
     * @param int $userId User ID
     * @param string $userEmail User email
     * @param string $secret Secret key
     * @return string QR code URL
     */
    public function generateQRCodeUrl($userId, $userEmail, $secret) {
        $label = urlencode($this->issuer . ':' . $userEmail);
        $issuer = urlencode($this->issuer);
        
        $url = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&digits={$this->digits}&period={$this->period}&algorithm={$this->algorithm}";
        
        // Generate QR code URL using Google Charts API
        $qrCodeUrl = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($url);
        
        return $qrCodeUrl;
    }
    
    /**
     * Enable 2FA for user after verification
     * 
     * @param int $userId User ID
     * @param string $verificationCode Verification code from authenticator
     * @return bool Success status
     */
    public function enable2FA($userId, $verificationCode) {
        try {
            // Get user's 2FA data
            $twoFaData = $this->get2FAData($userId);
            if (!$twoFaData) {
                return false;
            }
            
            // Verify the code
            if (!$this->verifyCode($twoFaData['secret_key'], $verificationCode)) {
                return false;
            }
            
            // Enable 2FA
            $sql = "UPDATE user_two_factor_auth 
                    SET is_enabled = TRUE, enabled_at = NOW(), updated_at = NOW() 
                    WHERE user_id = ?";
            
            $result = $this->db->execute($sql, [$userId]);
            
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('2FA enable error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Disable 2FA for user
     * 
     * @param int $userId User ID
     * @param string $verificationCode Verification code or backup code
     * @return bool Success status
     */
    public function disable2FA($userId, $verificationCode) {
        try {
            // Get user's 2FA data
            $twoFaData = $this->get2FAData($userId);
            if (!$twoFaData) {
                return false;
            }
            
            // Verify the code (either TOTP or backup code)
            $isValid = $this->verifyCode($twoFaData['secret_key'], $verificationCode) || 
                       $this->verifyBackupCode($userId, $verificationCode);
            
            if (!$isValid) {
                return false;
            }
            
            // Disable 2FA
            $sql = "UPDATE user_two_factor_auth 
                    SET is_enabled = FALSE, updated_at = NOW() 
                    WHERE user_id = ?";
            
            $result = $this->db->execute($sql, [$userId]);
            
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('2FA disable error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify 2FA code
     * 
     * @param int $userId User ID
     * @param string $code Verification code
     * @return bool True if code is valid
     */
    public function verify2FACode($userId, $code) {
        try {
            // Get user's 2FA data
            $twoFaData = $this->get2FAData($userId);
            if (!$twoFaData || !$twoFaData['is_enabled']) {
                return false;
            }
            
            // Try TOTP verification first
            if ($this->verifyCode($twoFaData['secret_key'], $code)) {
                // Update last used timestamp
                $this->updateLastUsed($userId);
                return true;
            }
            
            // Try backup code verification
            if ($this->verifyBackupCode($userId, $code)) {
                $this->updateLastUsed($userId);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('2FA verification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if 2FA is enabled for user
     * 
     * @param int $userId User ID
     * @return bool True if 2FA is enabled
     */
    public function is2FAEnabled($userId) {
        try {
            $sql = "SELECT is_enabled FROM user_two_factor_auth WHERE user_id = ?";
            $result = $this->db->selectOne($sql, [$userId]);
            
            return $result ? (bool)$result['is_enabled'] : false;
            
        } catch (Exception $e) {
            error_log('2FA status check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get 2FA data for user
     * 
     * @param int $userId User ID
     * @return array|false 2FA data or false
     */
    private function get2FAData($userId) {
        try {
            $sql = "SELECT * FROM user_two_factor_auth WHERE user_id = ?";
            return $this->db->selectOne($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log('Get 2FA data error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate random secret key
     * 
     * @return string Base32 encoded secret
     */
    private function generateRandomSecret() {
        $secretLength = 32;
        $secret = '';
        
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= chr(mt_rand(0, 255));
        }
        
        return $this->base32Encode($secret);
    }
    
    /**
     * Generate backup codes
     * 
     * @return array Array of backup codes
     */
    private function generateBackupCodes() {
        $codes = [];
        
        for ($i = 0; $i < 10; $i++) {
            $codes[] = $this->generateRandomCode();
        }
        
        return $codes;
    }
    
    /**
     * Generate random backup code
     * 
     * @return string Random backup code
     */
    private function generateRandomCode() {
        return strtoupper(bin2hex(random_bytes(4)));
    }
    
    /**
     * Verify TOTP code
     * 
     * @param string $secret Secret key
     * @param string $code User provided code
     * @return bool True if code is valid
     */
    private function verifyCode($secret, $code) {
        $timeWindow = 2; // Allow 2 time windows before and after current time
        $currentTime = floor(time() / $this->period);
        
        for ($i = -$timeWindow; $i <= $timeWindow; $i++) {
            $calculatedCode = $this->calculateCode($secret, $currentTime + $i);
            if ($calculatedCode === $code) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calculate TOTP code
     * 
     * @param string $secret Secret key
     * @param int $timeCounter Time counter
     * @return string TOTP code
     */
    private function calculateCode($secret, $timeCounter) {
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeCounter);
        
        $hash = hash_hmac($this->algorithm, $time, $secretKey, true);
        $offset = ord($hash[19]) & 0xf;
        
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, $this->digits);
        
        return str_pad($code, $this->digits, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verify backup code
     * 
     * @param int $userId User ID
     * @param string $code Backup code
     * @return bool True if code is valid
     */
    private function verifyBackupCode($userId, $code) {
        try {
            $twoFaData = $this->get2FAData($userId);
            if (!$twoFaData) {
                return false;
            }
            
            $backupCodes = json_decode($twoFaData['backup_codes'], true);
            if (!$backupCodes || !in_array($code, $backupCodes)) {
                return false;
            }
            
            // Remove used backup code
            $backupCodes = array_diff($backupCodes, [$code]);
            
            // Update backup codes
            $sql = "UPDATE user_two_factor_auth SET backup_codes = ? WHERE user_id = ?";
            $this->db->execute($sql, [json_encode(array_values($backupCodes)), $userId]);
            
            return true;
            
        } catch (Exception $e) {
            error_log('Backup code verification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last used timestamp
     * 
     * @param int $userId User ID
     */
    private function updateLastUsed($userId) {
        try {
            $sql = "UPDATE user_two_factor_auth SET last_used_at = NOW() WHERE user_id = ?";
            $this->db->execute($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log('Update last used error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get backup codes for user
     * 
     * @param int $userId User ID
     * @return array|false Array of backup codes or false
     */
    public function getBackupCodes($userId) {
        try {
            $twoFaData = $this->get2FAData($userId);
            if (!$twoFaData) {
                return false;
            }
            
            return json_decode($twoFaData['backup_codes'], true) ?: [];
            
        } catch (Exception $e) {
            error_log('Get backup codes error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate new backup codes
     * 
     * @param int $userId User ID
     * @return array|false New backup codes or false
     */
    public function generateNewBackupCodes($userId) {
        try {
            $newCodes = $this->generateBackupCodes();
            
            $sql = "UPDATE user_two_factor_auth SET backup_codes = ? WHERE user_id = ?";
            $result = $this->db->execute($sql, [json_encode($newCodes), $userId]);
            
            return $result ? $newCodes : false;
            
        } catch (Exception $e) {
            error_log('Generate new backup codes error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Base32 encode
     * 
     * @param string $data Data to encode
     * @return string Base32 encoded string
     */
    private function base32Encode($data) {
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $v <<= 8;
            $v |= ord($data[$i]);
            $vbits += 8;
            
            while ($vbits >= 5) {
                $output .= $base32[($v >> ($vbits - 5)) & 31];
                $vbits -= 5;
            }
        }
        
        if ($vbits > 0) {
            $output .= $base32[($v << (5 - $vbits)) & 31];
        }
        
        return $output;
    }
    
    /**
     * Base32 decode
     * 
     * @param string $data Base32 encoded string
     * @return string Decoded data
     */
    private function base32Decode($data) {
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $v <<= 5;
            $v |= strpos($base32, $data[$i]);
            $vbits += 5;
            
            if ($vbits >= 8) {
                $output .= chr(($v >> ($vbits - 8)) & 255);
                $vbits -= 8;
            }
        }
        
        return $output;
    }
    
    /**
     * Get 2FA statistics
     * 
     * @return array 2FA statistics
     */
    public function get2FAStatistics() {
        try {
            $stats = [];
            
            // Total users with 2FA enabled
            $sql = "SELECT COUNT(*) as count FROM user_two_factor_auth WHERE is_enabled = TRUE";
            $result = $this->db->selectOne($sql);
            $stats['enabled_users'] = $result['count'] ?? 0;
            
            // Total users with 2FA configured but not enabled
            $sql = "SELECT COUNT(*) as count FROM user_two_factor_auth WHERE is_enabled = FALSE";
            $result = $this->db->selectOne($sql);
            $stats['configured_users'] = $result['count'] ?? 0;
            
            // Recent 2FA usage
            $sql = "SELECT COUNT(*) as count FROM user_two_factor_auth 
                    WHERE last_used_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $result = $this->db->selectOne($sql);
            $stats['recent_usage'] = $result['count'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log('Get 2FA statistics error: ' . $e->getMessage());
            return [];
        }
    }
}
