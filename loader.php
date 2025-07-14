<?php

/**
 * Loader to load all needed classes
 *
 * @package RenalTales
 * @version 2025.v3.0dev
 * @author Ľubomír Polaščín
 */

// File: /loader.php

// Load classes
require_once CONTROLLERS_DIR . DS . 'ApplicationController.php';
require_once CORE_DIR . DS . 'Logger.php';
require_once CORE_DIR . DS . 'SecurityManager.php';
require_once CORE_DIR . DS . 'SessionManager.php';
require_once CORE_DIR . DS . 'LanguageDetector.php';
require_once CORE_DIR . DS . 'LanguageManager.php';
require_once MODELS_DIR . DS . 'LanguageModel.php';
require_once VIEWS_DIR . DS . 'HomeView.php';
require_once VIEWS_DIR . DS . 'LoginView.php';
require_once VIEWS_DIR . DS . 'ErrorView.php';
