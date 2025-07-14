<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Models\LanguageModel;

/**
 * LanguageManager
 *
 * Facade/wrapper for LanguageModel to maintain backward compatibility.
 *
 * @author Ľubomír Polaščín
 * @version 2025.v3.0dev
 */
class LanguageManager {
  private LanguageModel $model;

  public function __construct() {
    $this->model = new LanguageModel();
  }

  public function getAllTexts(): array {
    return $this->model->getAllTexts();
  }

  public function getSupportedLanguages(): array {
    return $this->model->getSupportedLanguages();
  }

  public function getCurrentLanguage(): string {
    return $this->model->getCurrentLanguage();
  }

  public function setLanguage(string $language): bool {
    return $this->model->setLanguage($language);
  }

  public function isSupported(string $language): bool {
    return $this->model->isSupported($language);
  }

  public function getText(string $key, array $parameters = [], string $fallback = ''): string {
    return $this->model->getText($key, $parameters, $fallback);
  }

  public function getLanguageModel(): LanguageModel {
    return $this->model;
  }
}
