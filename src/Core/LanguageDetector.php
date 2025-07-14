<?php

declare(strict_types=1);

namespace RenalTales\Core;

/**
 * Language Detector
 *
 * Detects and manages user language preferences
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */
class LanguageDetector {
  /**
   * @var array<string> Supported language codes
   */
  private array $supportedLanguages;

  /**
   * @var string Default language code
   */
  private string $defaultLanguage;

  /**
   * LanguageDetector constructor.
   *
   * @param array<string> $supportedLanguages
   * @param string $defaultLanguage
   */
  public function __construct(
    array $supportedLanguages = [
      'af',
      'am',
      'ar',
      'as',
      'ay',
      'az',
      'bcl',
      'be',
      'bg',
      'bh',
      'bho',
      'bm',
      'bn',
      'bo',
      'ca',
      'ceb',
      'cs',
      'cy',
      'da',
      'de',
      'dv',
      'el',
      'en',
      'en-au',
      'en-ca',
      'en-gb',
      'en-nz',
      'en-us',
      'en-za',
      'eo',
      'es',
      'et',
      'eu',
      'fa',
      'ff',
      'fi',
      'fo',
      'fr',
      'ga',
      'gd',
      'gl',
      'gn',
      'gu',
      'ha',
      'he',
      'hi',
      'hil',
      'hr',
      'ht',
      'hu',
      'hy',
      'id',
      'ig',
      'ilo',
      'is',
      'it',
      'ja',
      'jv',
      'ka',
      'kg',
      'kk',
      'kl',
      'km',
      'kn',
      'ko',
      'ky',
      'la',
      'lb',
      'lg',
      'ln',
      'lo',
      'lt',
      'lua',
      'lv',
      'mai',
      'mg',
      'mk',
      'ml',
      'mn',
      'mr',
      'ms',
      'mt',
      'my',
      'nd',
      'ne',
      'nl',
      'no',
      'nr',
      'nso',
      'ny',
      'om',
      'or',
      'pa',
      'pam',
      'pl',
      'ps',
      'pt',
      'qu',
      'rm',
      'rn',
      'ro',
      'ru',
      'rw',
      'sa',
      'sd',
      'se',
      'sg',
      'si',
      'sk',
      'sl',
      'sn',
      'so',
      'sq',
      'sr',
      'ss',
      'st',
      'su',
      'sv',
      'sw',
      'ta',
      'te',
      'tg',
      'th',
      'ti',
      'tk',
      'tl',
      'tn',
      'tr',
      'ts',
      'ug',
      'uk',
      'ur',
      'uz',
      've',
      'vi',
      'war',
      'wuu',
      'xh',
      'yo',
      'yue',
      'zh',
      'zu'
    ],
    string $defaultLanguage = DEFAULT_LANGUAGE
  ) {
    $this->supportedLanguages = $supportedLanguages;
    $this->defaultLanguage = $defaultLanguage;
  }

  /**
   * Detect user's preferred language.
   * Priority: session > cookie > browser > default
   *
   * @return string
   */
  public function detectLanguage(): string {
    $sessionLang = $this->getSessionLanguage();
    if ($sessionLang !== null && $this->isSupported($sessionLang)) {
      return $sessionLang;
    }

    $cookieLang = $this->getCookieLanguage();
    if ($cookieLang !== null && $this->isSupported($cookieLang)) {
      return $cookieLang;
    }

    $browserLang = $this->getBrowserLanguage();
    if ($browserLang !== null && $this->isSupported($browserLang)) {
      return $browserLang;
    }

    return $this->defaultLanguage;
  }

  /**
   * Check if language is supported
   */
  public function isSupported(string $language): bool {
    return in_array($language, $this->supportedLanguages, true);
  }

  /**
   * Get supported languages
   */
  public function getSupportedLanguages(): array {
    return $this->supportedLanguages;
  }

  /**
   * Set language preference (session and cookie)
   */
  public function setLanguage(string $language): bool {
    if (!$this->isSupported($language)) {
      return false;
    }
    $this->setSessionLanguage($language);
    $this->setCookieLanguage($language);
    return true;
  }

  /**
   * Get language from session
   */
  private function getSessionLanguage(): ?string {
    return (isset($_SESSION['language']) && is_string($_SESSION['language'])) ? $_SESSION['language'] : null;
  }

  /**
   * Set language in session
   */
  private function setSessionLanguage(string $language): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
      $_SESSION['language'] = $language;
    }
  }

  /**
   * Get language from cookie
   */
  private function getCookieLanguage(): ?string {
    return (isset($_COOKIE['language']) && is_string($_COOKIE['language'])) ? $_COOKIE['language'] : null;
  }

  /**
   * Set language in cookie (30 days)
   */
  private function setCookieLanguage(string $language): void {
    setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');
  }

  /**
   * Get language from browser Accept-Language header
   */
  private function getBrowserLanguage(): ?string {
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      return null;
    }
    $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($languages as $lang) {
      $lang = substr(trim(explode(';', $lang)[0]), 0, 2);
      if ($this->isSupported($lang)) {
        return $lang;
      }
    }
    return null;
  }
}
