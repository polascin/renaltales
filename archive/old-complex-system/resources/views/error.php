<!DOCTYPE html>
<html lang="<?= $language ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            background: white;
            padding: 60px 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        
        .error-code {
            font-size: 4rem;
            font-weight: 700;
            color: #d32f2f;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(211, 47, 47, 0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .error-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .error-message {
            background: #f8f9fa;
            border-left: 4px solid #d32f2f;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: left;
        }
        
        .error-message strong {
            color: #d32f2f;
        }
        
        .actions {
            margin-top: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 40px 20px;
            }
            
            .error-code {
                font-size: 3rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?= $error_code ?></div>
        <h1><?= $error_title ?></h1>
        <p class="error-description"><?= $error_description ?></p>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <strong>Details:</strong> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="/" class="btn btn-primary"><?= $back_to_home ?></a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 0.9rem;">
            &copy; <?= $year ?> <?= $footer_copyright ?>
        </div>
    </div>
</body>
</html>
