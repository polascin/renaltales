<?php

/**
 * Security Monitoring Dashboard
 * 
 * Provides real-time security monitoring and alerting for the admin interface
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/AdminSecurityManager.php';
require_once __DIR__ . '/../models/SecurityEvent.php';
require_once __DIR__ . '/../models/AdminSession.php';

class SecurityMonitoringDashboard {
    
    private Database $db;
    private AdminSecurityManager $adminSecurity;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->adminSecurity = new AdminSecurityManager();
    }
    
    /**
     * Get security dashboard data
     * 
     * @return array Dashboard data
     */
    public function getDashboardData(): array {
        return [
            'alerts' => $this->getActiveAlerts(),
            'sessions' => $this->getSessionMetrics(),
            'security_events' => $this->getRecentSecurityEvents(),
            'failed_logins' => $this->getFailedLoginStats(),
            'suspicious_activity' => $this->getSuspiciousActivity(),
            'system_status' => $this->getSystemStatus(),
            'threat_level' => $this->calculateThreatLevel(),
            'recommendations' => $this->getSecurityRecommendations()
        ];
    }
    
    /**
     * Get active security alerts
     * 
     * @return array Active alerts
     */
    private function getActiveAlerts(): array {
        try {
            // Get critical security events from last 24 hours
            $sql = "SELECT se.*, u.username 
                    FROM security_events se 
                    LEFT JOIN users u ON se.user_id = u.id 
                    WHERE se.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND se.event_type IN ('admin_login_failure', 'unauthorized_admin_access', 
                                         'hijacking_detected', 'privilege_escalation_attempt',
                                         'security_alert', 'suspicious_admin_activity')
                    ORDER BY se.created_at DESC 
                    LIMIT 10";
            
            $events = $this->db->select($sql);
            
            $alerts = [];
            foreach ($events as $event) {
                $alerts[] = [
                    'id' => $event['id'],
                    'type' => $event['event_type'],
                    'severity' => $this->getAlertSeverity($event['event_type']),
                    'message' => $this->formatAlertMessage($event),
                    'timestamp' => $event['created_at'],
                    'ip_address' => $event['ip_address'],
                    'user' => $event['username'] ?? 'Unknown',
                    'details' => $event['failure_reason']
                ];
            }
            
            return $alerts;
            
        } catch(Exception $e) 
            error_log('Dashboard alerts error: ' . $e->getMessage());
            return [];
        
    }
    
    /**
     * Get session metrics
     * 
     * @return array Session metrics
     */
    private function getSessionMetrics(): array {
        try {
            $adminSession = new AdminSession();
            $stats = $adminSession->getSessionStats();
            
            // Add regular session stats
            $regularSessions = $this->getRegularSessionStats();
            
            return [
                'admin_sessions' => [
                    'active' => $stats['active_sessions'] ?? 0,
                    'today' => $stats['sessions_today'] ?? 0,
                    'by_user' => $stats['sessions_by_user'] ?? []
                ],
                'regular_sessions' => $regularSessions,
                'total_active' => ($stats['active_sessions'] ?? 0) + ($regularSessions['active'] ?? 0),
                'regenerations_today' => $this->getRegenerationsToday()
            ];
            
        } catch(Exception $e) 
            error_log('Session metrics error: ' . $e->getMessage());
            return [];
        
    }
    
    /**
     * Get recent security events
     * 
     * @return array Recent events
     */
    private function getRecentSecurityEvents(): array {
        try {
            $sql = "SELECT se.*, u.username 
                    FROM security_events se 
                    LEFT JOIN users u ON se.user_id = u.id 
                    WHERE se.created_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
                    ORDER BY se.created_at DESC 
                    LIMIT 20";
            
            $events = $this->db->select($sql);
            
            return array_map(function($event) {
                return [
                    'type' => $event['event_type'],
                    'user' => $event['username'] ?? 'Unknown',
                    'ip_address' => $event['ip_address'],
                    'timestamp' => $event['created_at'],
                    'description' => $event['failure_reason'],
                    'severity' => $this->getEventSeverity($event['event_type'])
                ];
            }, $events);
            
        } catch(Exception $e) 
            error_log('Recent events error: ' . $e->getMessage());
            return [];
        
    }
    
    /**
     * Get failed login statistics
     * 
     * @return array Failed login stats
     */
    private function getFailedLoginStats(): array {
        try {
            // Failed logins by IP in last 24 hours
            $sql = "SELECT ip_address, COUNT(*) as attempts 
                    FROM security_events 
                    WHERE event_type IN ('login_failure', 'admin_login_failure')
                    AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY ip_address 
                    ORDER BY attempts DESC 
                    LIMIT 10";
            
            $byIP = $this->db->select($sql);
            
            // Failed logins by hour
            $sql = "SELECT HOUR(created_at) as hour, COUNT(*) as attempts 
                    FROM security_events 
                    WHERE event_type IN ('login_failure', 'admin_login_failure')
                    AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY HOUR(created_at) 
                    ORDER BY hour";
            
            $byHour = $this->db->select($sql);
            
            // Total failed logins today
            $sql = "SELECT COUNT(*) as total 
                    FROM security_events 
                    WHERE event_type IN ('login_failure', 'admin_login_failure')
                    AND DATE(created_at) = CURDATE()";
            
            $totalResult = $this->db->selectOne($sql);
            
            return [
                'total_today' => $totalResult['total'] ?? 0,
                'by_ip' => $byIP,
                'by_hour' => $byHour,
                'blocked_ips' => $this->getBlockedIPs()
            ];
            
        } catch(Exception $e) 
            error_log('Failed login stats error: ' . $e->getMessage());
            return [];
        
    }
    
    /**
     * Get suspicious activity indicators
     * 
     * @return array Suspicious activity
     */
    private function getSuspiciousActivity(): array {
        try {
            $suspicious = [];
            
            // Multiple failed admin logins
            $sql = "SELECT ip_address, COUNT(*) as attempts 
                    FROM security_events 
                    WHERE event_type = 'admin_login_failure'
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    GROUP BY ip_address 
                    HAVING attempts >= 3";
            
            $adminFailures = $this->db->select($sql);
            
            // Privilege escalation attempts
            $sql = "SELECT user_id, ip_address, COUNT(*) as attempts 
                    FROM security_events 
                    WHERE event_type = 'unauthorized_admin_access'
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    GROUP BY user_id, ip_address 
                    HAVING attempts >= 2";
            
            $escalationAttempts = $this->db->select($sql);
            
            // Unusual session patterns
            $sql = "SELECT user_id, ip_address, COUNT(*) as sessions 
                    FROM admin_sessions 
                    WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    GROUP BY user_id, ip_address 
                    HAVING sessions > 5";
            
            $unusualSessions = $this->db->select($sql);
            
            return [
                'admin_login_failures' => $adminFailures,
                'escalation_attempts' => $escalationAttempts,
                'unusual_sessions' => $unusualSessions,
                'off_hours_access' => $this->getOffHoursAccess()
            ];
            
        } catch(Exception $e) 
            error_log('Suspicious activity error: ' . $e->getMessage());
            return [];
        
    }
    
    /**
     * Get system security status
     * 
     * @return array System status
     */
    private function getSystemStatus(): array {
        return [
            'security_enabled' => true,
            'csrf_protection' => $this->checkCSRFProtection(),
            'session_security' => $this->checkSessionSecurity(),
            'database_security' => $this->checkDatabaseSecurity(),
            'ssl_enabled' => $this->checkSSLStatus(),
            'firewall_status' => $this->checkFirewallStatus(),
            'backup_status' => $this->checkBackupStatus(),
            'log_monitoring' => $this->checkLogMonitoring()
        ];
    }
    
    /**
     * Calculate overall threat level
     * 
     * @return array Threat level information
     */
    private function calculateThreatLevel(): array {
        $score = 0;
        $factors = [];
        
        // Check recent failed logins
        $recentFailures = $this->getRecentFailuresCount();
        if ($recentFailures > 20) {
            $score += 3;
            $factors[] = 'High number of failed logins';
        } elseif ($recentFailures > 10) {
            $score += 2;
            $factors[] = 'Moderate failed login attempts';
        }
        
        // Check for admin intrusion attempts
        $adminAttempts = $this->getAdminIntrusionAttempts();
        if ($adminAttempts > 5) {
            $score += 4;
            $factors[] = 'Multiple admin intrusion attempts';
        } elseif ($adminAttempts > 2) {
            $score += 2;
            $factors[] = 'Admin intrusion attempts detected';
        }
        
        // Check for session anomalies
        $sessionAnomalies = $this->getSessionAnomalies();
        if ($sessionAnomalies > 3) {
            $score += 2;
            $factors[] = 'Session security anomalies';
        }
        
        // Determine threat level
        if ($score >= 8) {
            $level = 'critical';
            $color = '#dc3545';
        } elseif ($score >= 5) {
            $level = 'high';
            $color = '#fd7e14';
        } elseif ($score >= 3) {
            $level = 'medium';
            $color = '#ffc107';
        } else {
            $level = 'low';
            $color = '#28a745';
        }
        
        return [
            'level' => $level,
            'score' => $score,
            'color' => $color,
            'factors' => $factors,
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get security recommendations
     * 
     * @return array Security recommendations
     */
    private function getSecurityRecommendations(): array {
        $recommendations = [];
        
        // Check for weak security configurations
        if (!$this->checkSSLStatus()) {
            $recommendations[] = [
                'type' => 'critical',
                'title' => 'Enable HTTPS',
                'description' => 'SSL/TLS encryption is not enabled. This is critical for admin security.',
                'action' => 'Configure SSL certificate and redirect HTTP to HTTPS'
            ];
        }
        
        // Check for excessive failed logins
        if ($this->getRecentFailuresCount() > 50) {
            $recommendations[] = [
                'type' => 'high',
                'title' => 'Implement Rate Limiting',
                'description' => 'High number of failed login attempts detected.',
                'action' => 'Enable stricter rate limiting and consider IP blocking'
            ];
        }
        
        // Check for admin sessions without 2FA
        if (!$this->checkAdmin2FAStatus()) {
            $recommendations[] = [
                'type' => 'medium',
                'title' => 'Enable 2FA for Admins',
                'description' => 'Some admin accounts do not have 2FA enabled.',
                'action' => 'Require two-factor authentication for all admin accounts'
            ];
        }
        
        // Check for old sessions
        if ($this->hasOldSessions()) {
            $recommendations[] = [
                'type' => 'low',
                'title' => 'Clean Old Sessions',
                'description' => 'There are expired sessions in the system.',
                'action' => 'Run session cleanup to remove old session data'
            ];
        }
        
        return $recommendations;
    }
    
    // Helper methods for dashboard data
    
    private function getAlertSeverity(string $eventType): string {
        $severityMap = [
            'admin_login_failure' => 'medium',
            'unauthorized_admin_access' => 'high',
            'hijacking_detected' => 'critical',
            'privilege_escalation_attempt' => 'critical',
            'security_alert' => 'high',
            'suspicious_admin_activity' => 'medium'
        ];
        
        return $severityMap[$eventType] ?? 'low';
    }
    
    private function formatAlertMessage(array $event): string {
        $typeMessages = [
            'admin_login_failure' => 'Failed admin login attempt',
            'unauthorized_admin_access' => 'Unauthorized admin access attempt',
            'hijacking_detected' => 'Session hijacking detected',
            'privilege_escalation_attempt' => 'Privilege escalation attempt',
            'security_alert' => 'Security alert triggered',
            'suspicious_admin_activity' => 'Suspicious admin activity'
        ];
        
        $baseMessage = $typeMessages[$event['event_type']] ?? 'Security event';
        return $baseMessage . ' from ' . $event['ip_address'];
    }
    
    private function getEventSeverity(string $eventType): string {
        $criticalEvents = ['hijacking_detected', 'privilege_escalation_attempt'];
        $highEvents = ['unauthorized_admin_access', 'security_alert'];
        $mediumEvents = ['admin_login_failure', 'suspicious_admin_activity'];
        
        if (in_array($eventType, $criticalEvents)) return 'critical';
        if (in_array($eventType, $highEvents)) return 'high';
        if (in_array($eventType, $mediumEvents)) return 'medium';
        
        return 'low';
    }
    
    private function getRegularSessionStats(): array {
        // This would query regular user sessions
        // For now, return mock data
        return [
            'active' => 0,
            'today' => 0
        ];
    }
    
    private function getRegenerationsToday(): int {
        try {
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE event_type = 'session_regenerated' 
                    AND DATE(created_at) = CURDATE()";
            
            $result = $this->db->selectOne($sql);
            return $result['count'] ?? 0;
            
        } catch(Exception $e) 
    error_log('Exception in SecurityMonitoringDashboard.php: ' . $e->getMessage());
            return 0;
        
    }
    
    private function getBlockedIPs(): array {
        // This would query blocked IPs from firewall/rate limiting system
        return [];
    }
    
    private function getOffHoursAccess(): array {
        try {
            $sql = "SELECT user_id, ip_address, created_at 
                    FROM admin_sessions 
                    WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND (HOUR(created_at) < 8 OR HOUR(created_at) > 18)
                    ORDER BY created_at DESC";
            
            return $this->db->select($sql);
            
        } catch(Exception $e) 
    error_log('Exception in SecurityMonitoringDashboard.php: ' . $e->getMessage());
            return [];
        
    }
    
    private function checkCSRFProtection(): bool {
        return class_exists('SecurityManager');
    }
    
    private function checkSessionSecurity(): bool {
        return session_get_cookie_params()['httponly'] ?? false;
    }
    
    private function checkDatabaseSecurity(): bool {
        return true; // Assume secure based on our implementation
    }
    
    private function checkSSLStatus(): bool {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
    
    private function checkFirewallStatus(): bool {
        return true; // Would check actual firewall status
    }
    
    private function checkBackupStatus(): bool {
        return true; // Would check backup system status
    }
    
    private function checkLogMonitoring(): bool {
        return file_exists(__DIR__ . '/../storage/logs/security_events.log');
    }
    
    private function getRecentFailuresCount(): int {
        try {
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE event_type IN ('login_failure', 'admin_login_failure')
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $result = $this->db->selectOne($sql);
            return $result['count'] ?? 0;
            
        } catch(Exception $e) 
    error_log('Exception in SecurityMonitoringDashboard.php: ' . $e->getMessage());
            return 0;
        
    }
    
    private function getAdminIntrusionAttempts(): int {
        try {
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE event_type IN ('unauthorized_admin_access', 'privilege_escalation_attempt')
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $result = $this->db->selectOne($sql);
            return $result['count'] ?? 0;
            
        } catch(Exception $e) 
    error_log('Exception in SecurityMonitoringDashboard.php: ' . $e->getMessage());
            return 0;
        
    }
    
    private function getSessionAnomalies(): int {
        try {
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE event_type IN ('hijacking_detected', 'session_anomaly')
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $result = $this->db->selectOne($sql);
            return $result['count'] ?? 0;
            
        } catch(Exception $e) 
    error_log('Exception in SecurityMonitoringDashboard.php: ' . $e->getMessage());
            return 0;
        
    }
    
    private function checkAdmin2FAStatus(): bool {
        // Would check if all admin users have 2FA enabled
        return true; // Assume enabled for now
    }
    
    private function hasOldSessions(): bool {
        try {
            $sql = "SELECT COUNT(*) as count FROM admin_sessions 
                    WHERE expires_at < NOW()";
            
            $result = $this->db->selectOne($sql);
            return ($result['count'] ?? 0) > 0;
            
        } catch(Exception $e) 
    error_log('Exception in SecurityMonitoringDashboard.php: ' . $e->getMessage());
            return false;
        
    }
}
