<?php
/**
 * Database Configuration Management Interface
 * 
 * Provides a web interface to manage database settings
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Include necessary files
require_once dirname(__DIR__, 2) . '/bootstrap.php';

// Check if user is authenticated and has admin privileges
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die('Access denied. Admin privileges required.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_database_config':
                $response = updateDatabaseConfig($_POST);
                break;
            case 'test_connection':
                $response = testDatabaseConnection($_POST);
                break;
            case 'backup_config':
                $response = backupConfiguration();
                break;
            case 'restore_config':
                $response = restoreConfiguration($_POST['backup_file']);
                break;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Load current configuration
$currentConfig = loadCurrentConfig();
$backupFiles = getBackupFiles();

function loadCurrentConfig() {
    $envFile = dirname(__DIR__, 2) . '/.env';
    $config = [];
    
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
            
            list($key, $value) = explode('=', $line, 2);
            $config[trim($key)] = trim($value, '"');
        }
    }
    
    return $config;
}

function updateDatabaseConfig($data) {
    try {
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        // Create backup first
        $backupResult = backupConfiguration();
        if (!$backupResult['success']) {
            return ['success' => false, 'message' => 'Failed to create backup: ' . $backupResult['message']];
        }
        
        // Validate input
        $requiredFields = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field $field is required"];
            }
        }
        
        // Load current .env file
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $updatedLines = [];
        $updatedKeys = [];
        
        // Update existing lines
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                $updatedLines[] = $line;
                continue;
            }
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            
            if (isset($data[$key])) {
                $newValue = $data[$key];
                // Escape special characters in password
                if ($key === 'DB_PASSWORD' && !empty($newValue)) {
                    $newValue = '"' . $newValue . '"';
                }
                $updatedLines[] = $key . '=' . $newValue;
                $updatedKeys[] = $key;
            } else {
                $updatedLines[] = $line;
            }
        }
        
        // Add new keys if they don't exist
        $dbKeys = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
        foreach ($dbKeys as $key) {
            if (!in_array($key, $updatedKeys) && isset($data[$key])) {
                $value = $data[$key];
                if ($key === 'DB_PASSWORD' && !empty($value)) {
                    $value = '"' . $value . '"';
                }
                $updatedLines[] = $key . '=' . $value;
            }
        }
        
        // Write updated file
        if (file_put_contents($envFile, implode("\n", $updatedLines) . "\n") === false) {
            return ['success' => false, 'message' => 'Failed to write configuration file'];
        }
        
        // Test the new configuration
        $testResult = testDatabaseConnection($data);
        if (!$testResult['success']) {
            // Restore backup if test fails
            $backupFile = end(getBackupFiles());
            if ($backupFile) {
                restoreConfiguration($backupFile);
            }
            return ['success' => false, 'message' => 'Configuration updated but database test failed: ' . $testResult['message']];
        }
        
        return ['success' => true, 'message' => 'Database configuration updated successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating configuration: ' . $e->getMessage()];
    }
}

function testDatabaseConnection($config) {
    try {
        $host = $config['DB_HOST'] ?? '';
        $port = $config['DB_PORT'] ?? '3306';
        $database = $config['DB_DATABASE'] ?? '';
        $username = $config['DB_USERNAME'] ?? '';
        $password = $config['DB_PASSWORD'] ?? '';
        
        if (empty($host) || empty($database) || empty($username)) {
            return ['success' => false, 'message' => 'Missing required connection parameters'];
        }
        
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10, // 10 second timeout
        ];
        
        $startTime = microtime(true);
        $pdo = new PDO($dsn, $username, $password, $options);
        $connectionTime = microtime(true) - $startTime;
        
        // Test basic query
        $stmt = $pdo->query("SELECT VERSION() as version, NOW() as current_time");
        $result = $stmt->fetch();
        
        return [
            'success' => true, 
            'message' => 'Connection successful',
            'details' => [
                'connection_time' => round($connectionTime * 1000, 2) . ' ms',
                'database_version' => $result['version'],
                'server_time' => $result['current_time']
            ]
        ];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
    }
}

function backupConfiguration() {
    try {
        $envFile = dirname(__DIR__, 2) . '/.env';
        $backupDir = dirname(__DIR__, 2) . '/storage/config-backups';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . '/env_backup_' . $timestamp . '.txt';
        
        if (copy($envFile, $backupFile)) {
            return ['success' => true, 'message' => 'Configuration backed up successfully', 'backup_file' => basename($backupFile)];
        } else {
            return ['success' => false, 'message' => 'Failed to create backup'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Backup error: ' . $e->getMessage()];
    }
}

function restoreConfiguration($backupFile) {
    try {
        $envFile = dirname(__DIR__, 2) . '/.env';
        $backupPath = dirname(__DIR__, 2) . '/storage/config-backups/' . $backupFile;
        
        if (!file_exists($backupPath)) {
            return ['success' => false, 'message' => 'Backup file not found'];
        }
        
        if (copy($backupPath, $envFile)) {
            return ['success' => true, 'message' => 'Configuration restored successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to restore configuration'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Restore error: ' . $e->getMessage()];
    }
}

function getBackupFiles() {
    $backupDir = dirname(__DIR__, 2) . '/storage/config-backups';
    if (!is_dir($backupDir)) {
        return [];
    }
    
    $files = glob($backupDir . '/env_backup_*.txt');
    $backups = [];
    
    foreach ($files as $file) {
        $filename = basename($file);
        $timestamp = filemtime($file);
        $backups[] = [
            'filename' => $filename,
            'timestamp' => $timestamp,
            'date' => date('Y-m-d H:i:s', $timestamp),
            'size' => filesize($file)
        ];
    }
    
    // Sort by timestamp descending
    usort($backups, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    return $backups;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration - Renal Tales Admin</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #007cba; }
        .btn { display: inline-block; padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn:hover { background: #005a87; }
        .btn.secondary { background: #6c757d; }
        .btn.secondary:hover { background: #545b62; }
        .btn.danger { background: #dc3545; }
        .btn.danger:hover { background: #c82333; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .status { padding: 5px 10px; border-radius: 4px; font-size: 12px; }
        .status.success { background: #d4edda; color: #155724; }
        .status.error { background: #f8d7da; color: #721c24; }
        .backup-list { max-height: 300px; overflow-y: auto; }
        .backup-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .backup-item:last-child { border-bottom: none; }
        .loading { display: none; }
        .loading.active { display: inline-block; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Database Configuration Management</h1>
            <p>Manage database connection settings for Renal Tales application</p>
        </div>

        <div id="alert-container"></div>

        <div class="grid">
            <div class="card">
                <h2>Database Settings</h2>
                <form id="database-config-form">
                    <div class="form-group">
                        <label for="DB_HOST">Database Host</label>
                        <input type="text" id="DB_HOST" name="DB_HOST" value="<?= htmlspecialchars($currentConfig['DB_HOST'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="DB_PORT">Port</label>
                        <input type="number" id="DB_PORT" name="DB_PORT" value="<?= htmlspecialchars($currentConfig['DB_PORT'] ?? '3306') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="DB_DATABASE">Database Name</label>
                        <input type="text" id="DB_DATABASE" name="DB_DATABASE" value="<?= htmlspecialchars($currentConfig['DB_DATABASE'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="DB_USERNAME">Username</label>
                        <input type="text" id="DB_USERNAME" name="DB_USERNAME" value="<?= htmlspecialchars($currentConfig['DB_USERNAME'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="DB_PASSWORD">Password</label>
                        <input type="password" id="DB_PASSWORD" name="DB_PASSWORD" value="<?= htmlspecialchars($currentConfig['DB_PASSWORD'] ?? '') ?>">
                        <small>Leave empty to keep current password</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="button" id="test-connection" class="btn secondary">Test Connection</button>
                        <button type="submit" class="btn">Update Configuration</button>
                        <span class="loading" id="form-loading">Processing...</span>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Connection Status</h2>
                <div id="connection-status">
                    <p>Click "Test Connection" to check database connectivity</p>
                </div>
                
                <h3>Configuration Backup</h3>
                <button type="button" id="backup-config" class="btn secondary">Create Backup</button>
                <span class="loading" id="backup-loading">Creating backup...</span>
                
                <h3>Available Backups</h3>
                <div class="backup-list">
                    <?php if (empty($backupFiles)): ?>
                        <p>No backups available</p>
                    <?php else: ?>
                        <?php foreach ($backupFiles as $backup): ?>
                            <div class="backup-item">
                                <div>
                                    <strong><?= htmlspecialchars($backup['filename']) ?></strong><br>
                                    <small><?= htmlspecialchars($backup['date']) ?> (<?= number_format($backup['size']) ?> bytes)</small>
                                </div>
                                <button type="button" class="btn danger" onclick="restoreBackup('<?= htmlspecialchars($backup['filename']) ?>')">
                                    Restore
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAlert(message, type = 'info') {
            const container = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert ${type}`;
            alert.textContent = message;
            container.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        function setLoading(elementId, show) {
            const element = document.getElementById(elementId);
            if (show) {
                element.classList.add('active');
            } else {
                element.classList.remove('active');
            }
        }

        document.getElementById('test-connection').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('database-config-form'));
            formData.append('action', 'test_connection');
            
            setLoading('form-loading', true);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setLoading('form-loading', false);
                
                const statusDiv = document.getElementById('connection-status');
                if (data.success) {
                    statusDiv.innerHTML = `
                        <div class="status success">✓ Connection Successful</div>
                        <p><strong>Connection Time:</strong> ${data.details.connection_time}</p>
                        <p><strong>Database Version:</strong> ${data.details.database_version}</p>
                        <p><strong>Server Time:</strong> ${data.details.server_time}</p>
                    `;
                    showAlert('Database connection test successful', 'success');
                } else {
                    statusDiv.innerHTML = `<div class="status error">✗ Connection Failed: ${data.message}</div>`;
                    showAlert('Database connection failed: ' + data.message, 'error');
                }
            })
            .catch(error => {
                setLoading('form-loading', false);
                showAlert('Error testing connection: ' + error.message, 'error');
            });
        });

        document.getElementById('database-config-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_database_config');
            
            setLoading('form-loading', true);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setLoading('form-loading', false);
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                setLoading('form-loading', false);
                showAlert('Error updating configuration: ' + error.message, 'error');
            });
        });

        document.getElementById('backup-config').addEventListener('click', function() {
            setLoading('backup-loading', true);
            
            const formData = new FormData();
            formData.append('action', 'backup_config');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setLoading('backup-loading', false);
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                setLoading('backup-loading', false);
                showAlert('Error creating backup: ' + error.message, 'error');
            });
        });

        function restoreBackup(filename) {
            if (!confirm('Are you sure you want to restore this backup? Current configuration will be overwritten.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'restore_config');
            formData.append('backup_file', filename);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Error restoring backup: ' + error.message, 'error');
            });
        }
    </script>
</body>
</html>
