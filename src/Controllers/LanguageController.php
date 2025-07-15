<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Models\LanguageModel;

// -*- coding: utf-8 -*-

/**
 * LanguageController
 *
 * Handles language switching and language-related endpoints for the application.
 *
 * @package RenalTales
 * @author Ľubomír Polaščín
 * @version 2025.3.1.dev
 */

class LanguageController {
  private LanguageModel $languageModel;

  public function __construct(LanguageModel $languageModel) {
    $this->languageModel = $languageModel;
  }

  /**
   * Switch the application's language (expects 'lang' parameter via GET/POST)
   * Responds with JSON for AJAX or redirects for standard requests.
   */
  public function switchLanguage(string $request): void {
    $requestedLang = $_POST['lang'] ?? $_GET['lang'] ?? $request ?? $this->languageModel->getCurrentLanguage();
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($requestedLang && $this->languageModel->isSupported($requestedLang)) {
      $this->languageModel->setLanguage($requestedLang);
      if ($isAjax) {
        $this->jsonResponse(['success' => true, 'language' => $requestedLang]);
      } else {
        // Redirect back or to home
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
      }
    } else {
      if ($isAjax) {
        $this->jsonResponse(['success' => false, 'error' => 'Invalid language code.'], 400);
      } else {
        // Redirect with error (could be improved with flash messaging)
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
      }
    }
    exit;
  }

  /**
   * Get the list of supported languages (for API/AJAX)
   */
  public function getSupportedLanguages(): void {
    $this->jsonResponse([
      'supported_languages' => $this->languageModel->getSupportedLanguages(),
      'current_language' => $this->languageModel->getCurrentLanguage()
    ]);
    exit;
  }

  /**
   * Get the current language (for API/AJAX)
   */
  public function getCurrentLanguage(): void {
    $this->jsonResponse([
      'current_language' => $this->languageModel->getCurrentLanguage()
    ]);
    exit;
  }

  /**
   * Helper to send a JSON response
   */
  private function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
  }
}
