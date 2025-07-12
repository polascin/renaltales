<?php

declare(strict_types=1);

require_once 'BaseController.php';
require_once 'LoginController.php';
require_once __DIR__ . '/../views/ApplicationView.php';
require_once __DIR__ . '/../views/ErrorViewFinal.php';
require_once __DIR__ . '/../core/AuthenticationManager.php';
require_once __DIR__ . '/../core/AdminSecurityManager.php';
require_once __DIR__ . '/../core/SessionRegenerationManager.php';

/**
 * ApplicationController - Main application controller
 * 
 * Handles user requests and coordinates between models and views
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */
