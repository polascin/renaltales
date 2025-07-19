<?php
/**
 * Error Layout Template
 *
 * Simple error page layout
 */

define('COMPONENT_TEMPLATE', true);
$title = esc_html($data['title'] ?? 'Error');
$message = esc_html($data['message'] ?? 'An error occurred');
$code = (int)($data['code'] ?? 500);
$debugInfo = $data['debug_info'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - RenalTales</title>
    <link rel="stylesheet" href="/assets/css/main.css?v=<?= time() ?>">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">⚠️</div>
            <h1 class="error-title"><?= $title ?></h1>
            <p class="error-message"><?= $message ?></p>
            
            <?php if ($code): ?>
            <p class="error-code">Error Code: <?= $code ?></p>
            <?php endif; ?>
            
            <div class="error-actions">
                <a href="javascript:history.back()" >Go Back</a>
                <a href="/" >Go Home</a>
            </div>
            
            <?php if ($debugInfo): ?>
                <?= render_debug_info($debugInfo) ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

