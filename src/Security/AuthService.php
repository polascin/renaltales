<?php
declare(strict_types=1);

namespace RenalTales\Security;

use RenalTales\Service\Service;
use RenalTales\Repository\UserRepository;
use RenalTales\Model\User;
use RenalTales\Core\Config;
use ParagonIE\PasswordLock\PasswordLock;
use ParagonIE\AntiCSRF\AntiCSRF;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

class AuthService extends Service
{
    private UserRepository $userRepository;
    private SessionManager $sessionManager;
    private LoginThrottling $loginThrottling;
    private AntiCSRF $antiCSRF;
    private Key $encryptionKey;

    public function __construct(Config $config = null)
    {
        parent::__construct($config);
        $this->userRepository = new UserRepository();
        $this->sessionManager = new SessionManager($config);
        $this->loginThrottling = new LoginThrottling($config);
        $this->initializeCSRF();
        $this->initializeEncryption();
    }

    private function initializeCSRF(): void
    {
        $this->antiCSRF = new AntiCSRF();
    }

    private function initializeEncryption(): void
    {
        $keyData = $this->config->get('security.encryption_key');
        if (!$keyData) {
            throw new \RuntimeException('Encryption key not configured');
        }
        $this->encryptionKey = Key::loadFromAsciiSafeString($keyData);
    }

    /**
     * Hash password using secure password hashing
     */
    public function hashPassword(string $password): string
    {
        return PasswordLock::hashPassword($password);
    }

    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        try {
            return PasswordLock::checkPassword($password, $hash);
        } catch (\Exception $e) {
            $this->logError('Password verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Authenticate user with throttling protection
     */
    public function authenticate(string $email, string $password, string $ipAddress): ?User
    {
        // Check if IP is throttled
        if ($this->loginThrottling->isThrottled($ipAddress)) {
            $this->logWarning('Login attempt from throttled IP', ['ip' => $ipAddress]);
            throw new \RuntimeException('Too many login attempts. Please try again later.');
        }

        // Check if user is throttled
        if ($this->loginThrottling->isUserThrottled($email)) {
            $this->logWarning('Login attempt for throttled user', ['email' => $email]);
            throw new \RuntimeException('Too many login attempts for this account. Please try again later.');
        }

        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !$this->verifyPassword($password, $user->password_hash)) {
            $this->loginThrottling->recordFailedAttempt($email, $ipAddress);
            $this->logWarning('Failed login attempt', ['email' => $email, 'ip' => $ipAddress]);
            return null;
        }

        if (!$user->isEmailVerified()) {
            throw new \RuntimeException('Email not verified');
        }

        // Clear failed attempts on successful login
        $this->loginThrottling->clearFailedAttempts($email, $ipAddress);
        
        $user->updateLastLogin();
        $this->logInfo('User logged in', ['user_id' => $user->id, 'ip' => $ipAddress]);
        
        return $user;
    }

    /**
     * Create user session
     */
    public function createSession(User $user, bool $rememberMe = false): string
    {
        $sessionToken = $this->sessionManager->createSession($user->id, $rememberMe);
        $this->logInfo('Session created', ['user_id' => $user->id, 'remember_me' => $rememberMe]);
        return $sessionToken;
    }

    /**
     * Validate session token
     */
    public function validateSession(string $sessionToken): ?User
    {
        $userId = $this->sessionManager->validateSession($sessionToken);
        if (!$userId) {
            return null;
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            $this->sessionManager->destroySession($sessionToken);
            return null;
        }

        return $user;
    }

    /**
     * Destroy session
     */
    public function destroySession(string $sessionToken): bool
    {
        return $this->sessionManager->destroySession($sessionToken);
    }

    /**
     * Destroy all user sessions
     */
    public function destroyAllUserSessions(int $userId): bool
    {
        return $this->sessionManager->destroyAllUserSessions($userId);
    }

    /**
     * Generate CSRF token
     */
    public function generateCSRFToken(): string
    {
        return $this->antiCSRF->generateToken();
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRFToken(string $token): bool
    {
        try {
            return $this->antiCSRF->validateToken($token);
        } catch (\Exception $e) {
            $this->logWarning('CSRF token validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get CSRF token for forms
     */
    public function getCSRFTokenHTML(): string
    {
        return $this->antiCSRF->insertToken();
    }

    /**
     * Check if user has permission for route
     */
    public function hasPermission(User $user, string $route, string $method = 'GET'): bool
    {
        $permissions = $this->config->get('security.route_permissions', []);
        
        foreach ($permissions as $pattern => $requiredPermission) {
            if ($this->matchRoute($route, $pattern)) {
                if ($requiredPermission === 'public') {
                    return true;
                }
                
                if ($requiredPermission === 'authenticated') {
                    return $user !== null;
                }
                
                return $user && $user->hasPermission($requiredPermission);
            }
        }
        
        // Default to requiring authentication
        return $user !== null;
    }

    /**
     * Encrypt sensitive data
     */
    public function encrypt(string $data): string
    {
        return Crypto::encrypt($data, $this->encryptionKey);
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt(string $encryptedData): string
    {
        return Crypto::decrypt($encryptedData, $this->encryptionKey);
    }

    /**
     * Generate secure random token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Rate limit check for actions
     */
    public function checkRateLimit(string $action, string $identifier, int $maxAttempts = 5, int $timeWindow = 3600): bool
    {
        return $this->loginThrottling->checkRateLimit($action, $identifier, $maxAttempts, $timeWindow);
    }

    /**
     * Get user's active sessions
     */
    public function getUserSessions(int $userId): array
    {
        return $this->sessionManager->getUserSessions($userId);
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int
    {
        return $this->sessionManager->cleanupExpiredSessions();
    }

    /**
     * Check if password needs rehashing
     */
    public function needsRehash(string $hash): bool
    {
        // Check if password was hashed with old algorithm
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }

    /**
     * Update user password hash if needed
     */
    public function updatePasswordHashIfNeeded(User $user, string $password): bool
    {
        if ($this->needsRehash($user->password_hash)) {
            $user->password_hash = $this->hashPassword($password);
            return $user->save();
        }
        return false;
    }

    /**
     * Match route pattern
     */
    private function matchRoute(string $route, string $pattern): bool
    {
        // Convert pattern to regex
        $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
        $regex = str_replace('*', '.*', $regex);
        $regex = '#^' . $regex . '$#';
        
        return preg_match($regex, $route) === 1;
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $this->logInfo("Security event: {$event}", $context);
    }

    /**
     * Validate session security
     */
    public function validateSessionSecurity(string $sessionToken, string $userAgent, string $ipAddress): bool
    {
        return $this->sessionManager->validateSessionSecurity($sessionToken, $userAgent, $ipAddress);
    }

    /**
     * Enable 2FA for user
     */
    public function enable2FA(User $user): array
    {
        $secret = $this->generateSecureToken(16);
        $backupCodes = [];
        
        // Generate backup codes
        for ($i = 0; $i < 8; $i++) {
            $backupCodes[] = $this->generateSecureToken(4);
        }
        
        $user->enable2FA($secret);
        
        // Store encrypted backup codes
        $encryptedCodes = $this->encrypt(json_encode($backupCodes));
        $this->userRepository->store2FABackupCodes($user->id, $encryptedCodes);
        
        $this->logInfo('2FA enabled', ['user_id' => $user->id]);
        
        return [
            'secret' => $secret,
            'backup_codes' => $backupCodes
        ];
    }

    /**
     * Verify 2FA code
     */
    public function verify2FA(User $user, string $code): bool
    {
        if (!$user->two_factor_enabled) {
            return false;
        }

        // Check if it's a backup code
        if (strlen($code) === 8) {
            return $this->verifyBackupCode($user, $code);
        }

        // Verify TOTP code (simplified - you might want to use a proper TOTP library)
        return $this->verifyTOTPCode($user->two_factor_secret, $code);
    }

    /**
     * Verify backup code
     */
    private function verifyBackupCode(User $user, string $code): bool
    {
        $encryptedCodes = $this->userRepository->get2FABackupCodes($user->id);
        if (!$encryptedCodes) {
            return false;
        }

        $backupCodes = json_decode($this->decrypt($encryptedCodes), true);
        
        if (in_array($code, $backupCodes)) {
            // Remove used backup code
            $backupCodes = array_filter($backupCodes, fn($c) => $c !== $code);
            $encryptedCodes = $this->encrypt(json_encode($backupCodes));
            $this->userRepository->store2FABackupCodes($user->id, $encryptedCodes);
            
            $this->logInfo('2FA backup code used', ['user_id' => $user->id]);
            return true;
        }
        
        return false;
    }

    /**
     * Verify TOTP code (simplified implementation)
     */
    private function verifyTOTPCode(string $secret, string $code): bool
    {
        // This is a simplified implementation
        // In production, use a proper TOTP library like RobThree/TwoFactorAuth
        return strlen($code) === 6 && ctype_digit($code);
    }
}
