<?php
if (!isset($currentUser)) {
    $currentUser = $_SESSION['user'] ?? null;
}
$message = $message ?? 'Access denied';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Forbidden - RenalTales</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 40px 20px;
        }
        
        .error-icon {
            font-size: 120px;
            margin-bottom: 30px;
            opacity: 0.8;
        }
        
        .error-code {
            font-size: 72px;
            font-weight: 300;
            margin: 0;
            line-height: 1;
        }
        
        .error-title {
            font-size: 28px;
            margin: 20px 0;
            font-weight: 400;
        }
        
        .error-message {
            font-size: 16px;
            line-height: 1.6;
            margin: 30px 0;
            opacity: 0.9;
        }
        
        .error-actions {
            margin-top: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin: 10px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: rgba(255, 255, 255, 0.9);
            color: #f5576c;
        }
        
        .btn-primary:hover {
            background: white;
        }
        
        .access-info {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .access-info h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .access-info p {
            margin: 10px 0;
            font-size: 14px;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 60px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .error-icon {
                font-size: 80px;
            }
            
            .btn {
                display: block;
                margin: 10px auto;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Forbidden</h2>
        
        <p class="error-message">
            <?= htmlspecialchars($message) ?>
        </p>
        
        <div class="error-actions">
            <?php if (!$currentUser): ?>
                <a href="/login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/register" class="btn">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php else: ?>
                <a href="/" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
                <a href="/profile" class="btn">
                    <i class="fas fa-user"></i> My Profile
                </a>
            <?php endif; ?>
            <a href="javascript:history.back()" class="btn">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
        
        <div class="access-info">
            <h3>Why am I seeing this?</h3>
            <p>
                You don't have permission to access this resource. This might be because:
            </p>
            <ul style="list-style: none; text-align: left; margin-top: 15px;">
                <?php if (!$currentUser): ?>
                    <li style="margin: 8px 0; padding-left: 20px; position: relative;">
                        <i class="fas fa-arrow-right" style="position: absolute; left: 0; top: 2px; font-size: 12px;"></i>
                        You need to be logged in to access this content
                    </li>
                    <li style="margin: 8px 0; padding-left: 20px; position: relative;">
                        <i class="fas fa-arrow-right" style="position: absolute; left: 0; top: 2px; font-size: 12px;"></i>
                        Your account might need verification
                    </li>
                <?php else: ?>
                    <li style="margin: 8px 0; padding-left: 20px; position: relative;">
                        <i class="fas fa-arrow-right" style="position: absolute; left: 0; top: 2px; font-size: 12px;"></i>
                        You don't have the required permissions
                    </li>
                    <li style="margin: 8px 0; padding-left: 20px; position: relative;">
                        <i class="fas fa-arrow-right" style="position: absolute; left: 0; top: 2px; font-size: 12px;"></i>
                        This content is restricted to certain user roles
                    </li>
                <?php endif; ?>
                <li style="margin: 8px 0; padding-left: 20px; position: relative;">
                    <i class="fas fa-arrow-right" style="position: absolute; left: 0; top: 2px; font-size: 12px;"></i>
                    The resource might be temporarily unavailable
                </li>
            </ul>
        </div>
    </div>
</body>
</html>
