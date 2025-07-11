<?php

/**
 * Cache Management Utility for Admin Panel
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Security check - ensure this is accessed from admin context
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Access denied');
}

// Handle cache clear request
if ($_POST['action'] ?? '' === 'clear_cache') {
    $result = clearApplicationCache();
    echo json_encode($result);
    exit;
}

/**
 * Clear application cache
 */
function clearApplicationCache() {
    $result = [
        'success' => true,
        'message' => '',
        'details' => []
    ];
    
    try {
        // Clear file cache
        $cachePath = __DIR__ . '/../../storage/cache/';
        $deletedCache = 0;
        
        if (is_dir($cachePath)) {
            $cacheFiles = glob($cachePath . '*');
            
            foreach ($cacheFiles as $file) {
                if (is_file($file) && basename($file) !== '.gitkeep') {
                    if (unlink($file)) {
                        $deletedCache++;
                    }
                }
            }
        }
        
        $result['details']['cache_files'] = $deletedCache;
        
        // Clear temporary files
        $tempPath = __DIR__ . '/../../storage/temp/';
        $deletedTemp = 0;
        
        if (is_dir($tempPath)) {
            $tempFiles = glob($tempPath . '*');
            
            foreach ($tempFiles as $file) {
                if (is_file($file) && basename($file) !== '.gitkeep') {
                    if (unlink($file)) {
                        $deletedTemp++;
                    }
                }
            }
        }
        
        $result['details']['temp_files'] = $deletedTemp;
        
        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            $opcacheCleared = opcache_reset();
            $result['details']['opcache'] = $opcacheCleared ? 'cleared' : 'failed';
        } else {
            $result['details']['opcache'] = 'not_available';
        }
        
        $result['message'] = "Cache cleared successfully. Files: {$deletedCache}, Temp: {$deletedTemp}";
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['message'] = 'Error: ' . $e->getMessage();
    }
    
    return $result;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cache Management - Renal Tales Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; background-color: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #005a87; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        .result { margin-top: 20px; padding: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .cache-info { margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 4px; }
        .loading { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cache Management</h1>
        
        <div class="cache-info">
            <h3>Current Cache Status</h3>
            <p><strong>Cache Directory:</strong> <?php echo realpath(__DIR__ . '/../../storage/cache/'); ?></p>
            <p><strong>Temp Directory:</strong> <?php echo realpath(__DIR__ . '/../../storage/temp/'); ?></p>
            <p><strong>Cache Files:</strong> <?php 
                $cacheCount = count(glob(__DIR__ . '/../../storage/cache/*')) - 1; // -1 for .gitkeep
                echo max(0, $cacheCount);
            ?></p>
            <p><strong>Temp Files:</strong> <?php 
                $tempCount = count(glob(__DIR__ . '/../../storage/temp/*')) - 1; // -1 for .gitkeep
                echo max(0, $tempCount);
            ?></p>
            <p><strong>OPcache Status:</strong> <?php echo function_exists('opcache_get_status') && opcache_get_status() ? 'Enabled' : 'Disabled'; ?></p>
        </div>
        
        <button id="clearCacheBtn" class="btn btn-danger">Clear All Cache</button>
        <div id="loading" class="loading">Clearing cache...</div>
        <div id="result"></div>
        
        <div style="margin-top: 30px;">
            <h3>Cache Management Options</h3>
            <ul>
                <li><strong>Clear All Cache:</strong> Removes all cached files and temporary files</li>
                <li><strong>OPcache:</strong> PHP's built-in opcode cache (if enabled)</li>
                <li><strong>File Cache:</strong> Application-specific cached data</li>
                <li><strong>Temporary Files:</strong> Session data and temporary uploads</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="../" class="btn">← Back to Admin</a>
        </div>
    </div>

    <script>
        document.getElementById('clearCacheBtn').addEventListener('click', function() {
            const btn = this;
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            
            btn.disabled = true;
            loading.style.display = 'block';
            result.innerHTML = '';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clear_cache'
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                btn.disabled = false;
                
                const resultClass = data.success ? 'success' : 'error';
                result.innerHTML = `<div class="result ${resultClass}">
                    <strong>${data.success ? 'Success!' : 'Error!'}</strong><br>
                    ${data.message}
                    ${data.details ? '<br><small>Details: ' + JSON.stringify(data.details, null, 2) + '</small>' : ''}
                </div>`;
                
                // Refresh page after 2 seconds to update cache status
                if (data.success) {
                    setTimeout(() => window.location.reload(), 2000);
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                btn.disabled = false;
                result.innerHTML = `<div class="result error">
                    <strong>Error!</strong><br>
                    Failed to clear cache: ${error.message}
                </div>`;
            });
        });
    </script>
</body>
</html>
