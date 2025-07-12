<?php

/**
 * EmailManager - Email System
 * 
 * Handles email sending for password reset, email verification, and other notifications
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class EmailManager {
    
    private $fromEmail;
    private $fromName;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpSecure;
    
    public function __construct($config = []) {
        $this->fromEmail = $config['from_email'] ?? 'noreply@renaltales.com';
        $this->fromName = $config['from_name'] ?? 'RenalTales';
        $this->smtpHost = $config['smtp_host'] ?? 'localhost';
        $this->smtpPort = $config['smtp_port'] ?? 587;
        $this->smtpUsername = $config['smtp_username'] ?? '';
        $this->smtpPassword = $config['smtp_password'] ?? '';
        $this->smtpSecure = $config['smtp_secure'] ?? 'tls';
    }
    
    /**
     * Send password reset email
     * 
     * @param string $email Recipient email
     * @param string $resetToken Reset token
     * @param string $resetUrl Reset URL
     * @return bool Success status
     */
    public function sendPasswordResetEmail($email, $resetToken, $resetUrl) {
        $subject = 'Password Reset Request - RenalTales';
        
        $message = "
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>You have requested to reset your password for your RenalTales account.</p>
            <p>Please click the link below to reset your password:</p>
            <p><a href='{$resetUrl}?token={$resetToken}' style='background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
            <p>If you did not request this password reset, please ignore this email.</p>
            <p>This link will expire in 1 hour for security reasons.</p>
            <hr>
            <p>Best regards,<br>RenalTales Team</p>
        </body>
        </html>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send email verification email
     * 
     * @param string $email Recipient email
     * @param string $verificationToken Verification token
     * @param string $verificationUrl Verification URL
     * @return bool Success status
     */
    public function sendEmailVerificationEmail($email, $verificationToken, $verificationUrl) {
        $subject = 'Email Verification - RenalTales';
        
        $message = "
        <html>
        <body>
            <h2>Email Verification</h2>
            <p>Thank you for registering with RenalTales!</p>
            <p>Please click the link below to verify your email address:</p>
            <p><a href='{$verificationUrl}?token={$verificationToken}' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
            <p>If you did not create an account, please ignore this email.</p>
            <p>This link will expire in 24 hours for security reasons.</p>
            <hr>
            <p>Best regards,<br>RenalTales Team</p>
        </body>
        </html>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send two-factor authentication backup codes
     * 
     * @param string $email Recipient email
     * @param array $backupCodes Array of backup codes
     * @return bool Success status
     */
    public function send2FABackupCodes($email, $backupCodes) {
        $subject = 'Two-Factor Authentication Backup Codes - RenalTales';
        
        $codesList = '';
        foreach ($backupCodes as $code) {
            $codesList .= "<li style='font-family: monospace; background-color: #f8f9fa; padding: 5px; margin: 5px 0;'>{$code}</li>";
        }
        
        $message = "
        <html>
        <body>
            <h2>Two-Factor Authentication Backup Codes</h2>
            <p>You have successfully enabled two-factor authentication for your RenalTales account.</p>
            <p>Please save these backup codes in a safe place. You can use them to access your account if you lose access to your authenticator app:</p>
            <ul>{$codesList}</ul>
            <p><strong>Important:</strong> Each backup code can only be used once. Store them securely and never share them with anyone.</p>
            <hr>
            <p>Best regards,<br>RenalTales Team</p>
        </body>
        </html>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send security alert email
     * 
     * @param string $email Recipient email
     * @param string $alertType Alert type
     * @param string $details Alert details
     * @return bool Success status
     */
    public function sendSecurityAlert($email, $alertType, $details) {
        $subject = 'Security Alert - RenalTales';
        
        $message = "
        <html>
        <body>
            <h2>Security Alert</h2>
            <p>We detected {$alertType} on your RenalTales account.</p>
            <p><strong>Details:</strong> {$details}</p>
            <p>If this was you, you can safely ignore this email. If not, please secure your account immediately by changing your password.</p>
            <p>If you need help, please contact our support team.</p>
            <hr>
            <p>Best regards,<br>RenalTales Team</p>
        </body>
        </html>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send email using basic PHP mail function
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email message
     * @return bool Success status
     */
    private function sendEmail($to, $subject, $message) {
        try {
            $headers = [
                'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
                'Reply-To: ' . $this->fromEmail,
                'Content-Type: text/html; charset=UTF-8',
                'MIME-Version: 1.0',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $headerString = implode("\r\n", $headers);
            
            // Log email attempt
            error_log("Sending email to: {$to}, Subject: {$subject}");
            
            // In production, you would use PHPMailer or similar
            // For now, using basic mail function
            $result = mail($to, $subject, $message, $headerString);
            
            if ($result) {
                error_log("Email sent successfully to: {$to}");
            } else {
                error_log("Failed to send email to: {$to}");
            }
            
            return $result;
            
        } catch(Exception $e) 
            error_log('Email sending error: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Send email using PHPMailer (when available)
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email message
     * @return bool Success status
     */
    private function sendEmailWithPHPMailer($to, $subject, $message) {
        // This would be implemented if PHPMailer is available
        // For now, return false to fall back to basic mail
        return false;
    }
    
    /**
     * Validate email address
     * 
     * @param string $email Email address
     * @return bool True if valid
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Get email sending statistics
     * 
     * @return array Email statistics
     */
    public function getEmailStatistics() {
        // This would track email sending statistics
        // For now, return empty array
        return [
            'sent_today' => 0,
            'sent_this_week' => 0,
            'sent_this_month' => 0,
            'failed_today' => 0
        ];
    }
}
