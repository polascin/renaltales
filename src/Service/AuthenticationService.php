<?php
declare(strict_types=1);

namespace RenalTales\Service;

use RenalTales\Repository\UserRepository;
use RenalTales\Model\User;
use PHPMailer\PHPMailer\PHPMailer;
use RenalTales\Core\Config;

class AuthenticationService extends Service
{
    private UserRepository $userRepository;
    private const PASSWORD_MIN_LENGTH = 12;
    private const TOKEN_EXPIRATION = 3600; // 1 hour

    public function __construct(Config $config = null)
    {
        parent::__construct($config);
        $this->userRepository = new UserRepository();
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !$user->verifyPassword($password)) {
            $this->logWarning('Failed login attempt', ['email' => $email]);
            return null;
        }

        if (!$user->isEmailVerified()) {
            throw new \RuntimeException('Email not verified');
        }

        $user->updateLastLogin();
        $this->logInfo('User logged in', ['user_id' => $user->id]);
        
        return $user;
    }

    public function register(array $userData): User
    {
        $this->validateRegistrationData($userData);

        // Check if user already exists
        if ($this->userRepository->findByEmail($userData['email'])) {
            throw new \RuntimeException('Email already registered');
        }
        if ($this->userRepository->findByUsername($userData['username'])) {
            throw new \RuntimeException('Username already taken');
        }

        // Create user
        $user = new User([
            'username' => $this->sanitizeString($userData['username']),
            'email' => $userData['email'],
            'full_name' => $this->sanitizeString($userData['full_name']),
            'role' => 'user',
            'language_preference' => $userData['language'] ?? 'en'
        ]);
        $user->setPassword($userData['password']);
        
        if (!$user->save()) {
            throw new \RuntimeException('Failed to create user');
        }

        // Send verification email
        $this->sendVerificationEmail($user);

        $this->logInfo('User registered', ['user_id' => $user->id]);
        
        return $user;
    }

    public function verifyEmail(string $token): bool
    {
        $verification = $this->userRepository->findVerificationToken($token);
        
        if (!$verification) {
            throw new \RuntimeException('Invalid verification token');
        }

        if (strtotime($verification['created_at']) < time() - self::TOKEN_EXPIRATION) {
            throw new \RuntimeException('Verification token expired');
        }

        $user = $this->userRepository->find($verification['user_id']);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        $user->verifyEmail();
        $this->userRepository->deleteVerificationToken($token);

        $this->logInfo('Email verified', ['user_id' => $user->id]);
        
        return true;
    }

    public function initiatePasswordReset(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            // Don't reveal whether the email exists
            return;
        }

        $token = bin2hex(random_bytes(32));
        $this->userRepository->createPasswordResetToken($user->id, $token);
        
        $this->sendPasswordResetEmail($user, $token);
        
        $this->logInfo('Password reset initiated', ['user_id' => $user->id]);
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $reset = $this->userRepository->findPasswordResetToken($token);
        
        if (!$reset) {
            throw new \RuntimeException('Invalid reset token');
        }

        if (strtotime($reset['created_at']) < time() - self::TOKEN_EXPIRATION) {
            throw new \RuntimeException('Reset token expired');
        }

        $user = $this->userRepository->find($reset['user_id']);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        $this->validatePassword($newPassword);
        $user->setPassword($newPassword);
        $user->save();

        $this->userRepository->deletePasswordResetToken($token);
        
        $this->logInfo('Password reset completed', ['user_id' => $user->id]);
        
        return true;
    }

    public function enable2FA(User $user): string
    {
        $secret = $this->generate2FASecret();
        $user->enable2FA($secret);
        
        $this->logInfo('2FA enabled', ['user_id' => $user->id]);
        
        return $secret;
    }

    public function verify2FA(User $user, string $code): bool
    {
        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            throw new \RuntimeException('2FA not enabled');
        }

        $valid = $this->verify2FACode($user->two_factor_secret, $code);
        
        if (!$valid) {
            $this->logWarning('Failed 2FA attempt', ['user_id' => $user->id]);
        }
        
        return $valid;
    }

    private function validateRegistrationData(array $data): void
    {
        $this->validateRequired($data, ['username', 'email', 'password', 'full_name']);
        $this->validateEmail($data['email']);
        $this->validatePassword($data['password']);
        $this->validateLength($data['username'], 'Username', 3, 50);
        $this->validateLength($data['full_name'], 'Full name', 2, 100);
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Password must be at least %d characters', self::PASSWORD_MIN_LENGTH)
            );
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one number');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new \InvalidArgumentException('Password must contain at least one special character');
        }
    }

    private function sendVerificationEmail(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $this->userRepository->createVerificationToken($user->id, $token);

        $mail = $this->createMailer();
        $mail->addAddress($user->email, $user->full_name);
        $mail->Subject = 'Verify your email address';
        
        $verifyUrl = sprintf(
            '%s/verify-email?token=%s',
            $this->config->get('app.url'),
            $token
        );
        
        $mail->Body = $this->renderEmailTemplate('verification', [
            'name' => $user->full_name,
            'verify_url' => $verifyUrl
        ]);

        if (!$mail->send()) {
            $this->logError('Failed to send verification email', [
                'user_id' => $user->id,
                'error' => $mail->ErrorInfo
            ]);
            throw new \RuntimeException('Failed to send verification email');
        }
    }

    private function sendPasswordResetEmail(User $user, string $token): void
    {
        $mail = $this->createMailer();
        $mail->addAddress($user->email, $user->full_name);
        $mail->Subject = 'Reset your password';
        
        $resetUrl = sprintf(
            '%s/reset-password?token=%s',
            $this->config->get('app.url'),
            $token
        );
        
        $mail->Body = $this->renderEmailTemplate('password-reset', [
            'name' => $user->full_name,
            'reset_url' => $resetUrl
        ]);

        if (!$mail->send()) {
            $this->logError('Failed to send password reset email', [
                'user_id' => $user->id,
                'error' => $mail->ErrorInfo
            ]);
            throw new \RuntimeException('Failed to send password reset email');
        }
    }

    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->config->get('mail.smtp_host');
        $mail->SMTPAuth = true;
        $mail->Username = $this->config->get('mail.smtp_username');
        $mail->Password = $this->config->get('mail.smtp_password');
        $mail->SMTPSecure = $this->config->get('mail.smtp_encryption');
        $mail->Port = $this->config->get('mail.smtp_port');
        $mail->setFrom(
            $this->config->get('mail.from_address'),
            $this->config->get('mail.from_name')
        );
        return $mail;
    }

    private function renderEmailTemplate(string $template, array $data): string
    {
        $templatePath = sprintf(
            '%s/templates/emails/%s.php',
            dirname(__DIR__, 2),
            $template
        );

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Email template not found: {$template}");
        }

        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }

    private function generate2FASecret(): string
    {
        return trim(base64_encode(random_bytes(32)), '=');
    }

    private function verify2FACode(string $secret, string $code): bool
    {
        // Implementation depends on your 2FA library
        // This is a placeholder that should be replaced with actual 2FA verification
        return strlen($code) === 6 && ctype_digit($code);
    }
}
