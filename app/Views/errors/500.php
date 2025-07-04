<?php
if (!isset($currentUser)) {
    $currentUser = $_SESSION['user'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - RenalTales</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
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
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
            color: #feb47b;
        }
        
        .btn-primary:hover {
            background: white;
        }
        
        .error-details {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .error-details h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .error-details p {
            margin: 10px 0;
            font-size: 14px;
            opacity: 0.8;
        }
        
        .help-section {
            margin-top: 30px;
            text-align: left;
        }
        
        .help-section ul {
            list-style: none;
            padding-left: 0;
        }
        
        .help-section li {
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
        }
        
        .help-section li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: rgba(255, 255, 255, 0.7);
            font-weight: bold;
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
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Server Error</h2>
        
        <p class="error-message">
            Something went wrong on our end. Our team has been automatically notified 
            and is working to fix this issue as quickly as possible.
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i> Go Home
            </a>
            <a href="javascript:history.back()" class="btn">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <a href="javascript:location.reload()" class="btn">
                <i class="fas fa-sync-alt"></i> Try Again
            </a>
        </div>
        
        <div class="error-details">
            <h3>What happened?</h3>
            <p>
                A temporary server error occurred while processing your request. 
                This could be due to high traffic, maintenance, or a temporary technical issue.
            </p>
            
            <div class="help-section">
                <h3>What can you do?</h3>
                <ul>
                    <li>Wait a few minutes and try again</li>
                    <li>Check your internet connection</li>
                    <li>Clear your browser cache and cookies</li>
                    <li>Try accessing the page from a different browser</li>
                    <li>Contact support if the problem persists</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
