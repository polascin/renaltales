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
    <title>Page Not Found - RenalTales</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #764ba2;
        }
        
        .btn-primary:hover {
            background: white;
        }
        
        .suggestions {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .suggestions h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .suggestions ul {
            list-style: none;
            text-align: left;
        }
        
        .suggestions li {
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
        }
        
        .suggestions li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .suggestions a {
            color: white;
            text-decoration: none;
            border-bottom: 1px dotted rgba(255, 255, 255, 0.5);
        }
        
        .suggestions a:hover {
            border-bottom-style: solid;
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
            <i class="fas fa-map-marked-alt"></i>
        </div>
        
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        
        <p class="error-message">
            Oops! The page you're looking for seems to have wandered off. 
            It might have been moved, deleted, or you may have mistyped the URL.
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i> Go Home
            </a>
            <a href="/stories" class="btn">
                <i class="fas fa-book"></i> Browse Stories
            </a>
            <a href="javascript:history.back()" class="btn">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
        
        <div class="suggestions">
            <h3>Try these instead:</h3>
            <ul>
                <li><a href="/">Homepage</a> - Return to our main page</li>
                <li><a href="/stories">Stories</a> - Browse community stories</li>
                <?php if (!$currentUser): ?>
                <li><a href="/login">Login</a> - Access your account</li>
                <li><a href="/register">Register</a> - Join our community</li>
                <?php else: ?>
                <li><a href="/profile">Profile</a> - Manage your account</li>
                <li><a href="/story/create">Write Story</a> - Share your experience</li>
                <?php endif; ?>
                <li><a href="/about">About</a> - Learn more about RenalTales</li>
            </ul>
        </div>
    </div>
</body>
</html>
