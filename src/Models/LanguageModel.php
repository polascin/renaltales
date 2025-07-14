<?php

declare(strict_types=1);

namespace RenalTales\Models;


/**
 * Language Model
 *
 * Handles dynamic language loading, support checks, translation lookup, and user language preference for a multilingual web application.
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */

// File: src/Models/LanguageModel.php


class LanguageModel {
  /**
   * @var string Path to language files
   */
  private string $languagePath;

  /**
   * @var array<string> List of supported language codes
   */
  private array $supportedLanguages = [];

  /**
   * @var string Current language code
   */
  private string $currentLanguage;

  /**
   * @var array<string, string> Loaded translations for current language
   */
  private array $translations = [];

  /**
   * LanguageModel constructor.
   * Loads supported languages and sets current language.
   *
   * @param string|null $languagePath
   * @param string|null $defaultLanguage
   */
  public function __construct(?string $languagePath = null, ?string $defaultLanguage = null) {
    $this->languagePath = $languagePath ?? dirname(__DIR__, 2) . '/resources/lang/';
    $this->loadSupportedLanguages();
    $this->currentLanguage = $this->detectLanguage($defaultLanguage ?? (defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en'));
    $this->loadTranslations($this->currentLanguage);
  }

  /**
   * Detect user's preferred language (session > cookie > browser > default)
   *
   * @param string $defaultLanguage
   * @return string
   */
  public function detectLanguage(string $defaultLanguage = 'en'): string {
    // Session
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['language'])) {
      $sessionLang = $_SESSION['language'];
      if ($this->isSupported($sessionLang)) {
        return $sessionLang;
      }
    }
    // Cookie
    if (isset($_COOKIE['language'])) {
      $cookieLang = $_COOKIE['language'];
      if ($this->isSupported($cookieLang)) {
        return $cookieLang;
      }
    }
    // Browser Accept-Language
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      foreach ($languages as $lang) {
        $lang = substr(trim(explode(';', $lang)[0]), 0, 2);
        if ($this->isSupported($lang)) {
          return $lang;
        }
      }
    }
    // Fallback
    return $defaultLanguage;
  }

  /**
   * Get the current language code
   */
  public function getCurrentLanguage(): string {
    return $this->currentLanguage;
  }

  /**
   * Set the current language and persist to session/cookie
   *
   * @param string $language
   * @return bool
   */
  public function setLanguage(string $language): bool {
    if (!$this->isSupported($language)) {
      return false;
    }
    $this->currentLanguage = $language;
    $this->loadTranslations($language);
    // Session
    if (session_status() === PHP_SESSION_ACTIVE) {
      $_SESSION['language'] = $language;
    }
    // Cookie (30 days)
    setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');
    return true;
  }

  /**
   * Get all supported language codes
   *
   * @return array<string>
   */
  public function getSupportedLanguages(): array {
    return $this->supportedLanguages;
  }

  /**
   * Check if a language code is supported
   *
   * @param string $language
   * @return bool
   */
  public function isSupported(string $language): bool {
    return in_array($language, $this->supportedLanguages, true);
  }

  /**
   * Get a translated string by key, with optional parameters and fallback
   *
   * @param string $key
   * @param array<string, string|int|float> $parameters
   * @param string $fallback
   * @return string
   */
  public function getText(string $key, array $parameters = [], string $fallback = ''): string {
    $text = $this->translations[$key] ?? $fallback ?: $key;
    foreach ($parameters as $param => $value) {
      $text = str_replace('{' . $param . '}', (string)$value, $text);
    }
    return $text;
  }

  /**
   * Get all translations for the current language
   *
   * @return array<string, string>
   */
  public function getAllTexts(): array {
    return $this->translations;
  }

  /**
   * Load all supported languages from the language directory
   */
  private function loadSupportedLanguages(): void {
    if (!is_dir($this->languagePath)) {
      $this->supportedLanguages = ['en'];
      return;
    }
    $files = glob($this->languagePath . '*.php');
    $languages = [];
    foreach ($files as $file) {
      $language = basename($file, '.php');
      // Accept language codes like en, en-us, zh, zh-cn, etc.
      if (preg_match('/^[a-z]{2}(-[a-z]{2})?$/i', $language)) {
        $languages[] = strtolower($language);
      }
    }
    sort($languages);
    $this->supportedLanguages = $languages ?: ['en'];
  }

  /**
   * Load translations for a specific language
   *
   * @param string $language
   */
  private function loadTranslations(string $language): void {
    $filePath = $this->languagePath . $language . '.php';
    if (file_exists($filePath)) {
      $translations = include $filePath;
      $this->translations = is_array($translations) ? $translations : [];
    } else {
      // Fallback to English if available
      $defaultPath = $this->languagePath . 'en.php';
      if (file_exists($defaultPath)) {
        $translations = include $defaultPath;
        $this->translations = is_array($translations) ? $translations : [];
      } else {
        $this->translations = [];
      }
    }
  }
}