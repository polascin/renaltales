<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Http\Response;
use RenalTales\Helpers\Translation;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Language Controller
 *
 * Simplified controller for language operations.
 * Direct route handling without complex service layers.
 *
 * @package RenalTales\Controllers
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class LanguageController
{
    /**
     * @var Translation Translation instance
     */
    private Translation $translation;

    /**
     * Constructor
     *
     * @param Translation|null $translation Translation instance (optional)
     */
    public function __construct(?Translation $translation = null)
    {
        $this->translation = $translation ?? new Translation();
    }

    /**
     * Switch to a different language
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function switch(ServerRequestInterface $request): ResponseInterface
    {
        // Get language from request
        $data = json_decode($request->getBody()->getContents(), true);
        $language = $data['language'] ?? $request->getQueryParams()['lang'] ?? '';

        if (empty($language)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Language not specified'
            ], 400);
        }

        // Validate language
        if (!$this->translation->isLanguageSupported($language)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Unsupported language',
                'current_language' => $this->translation->getCurrentLanguage(),
                'supported_languages' => $this->translation->getSupportedLanguages()
            ], 400);
        }

        // Switch language
        $success = $this->translation->switchTo($language);

        if ($success) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Language switched successfully',
                'current_language' => $this->translation->getCurrentLanguage(),
                'supported_languages' => $this->translation->getSupportedLanguages()
            ]);
        }

        return $this->jsonResponse([
            'success' => false,
            'error' => 'Failed to switch language',
            'current_language' => $this->translation->getCurrentLanguage(),
            'supported_languages' => $this->translation->getSupportedLanguages()
        ], 500);
    }

    /**
     * Get current language information
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function current(ServerRequestInterface $request): ResponseInterface
    {
        $data = [
            'current_language' => $this->translation->getCurrentLanguage(),
            'supported_languages' => $this->getSupportedLanguagesWithNames(),
            'translations_count' => count($this->translation->getAllTranslations())
        ];

        return $this->jsonResponse($data);
    }

    /**
     * Get all supported languages
     *
     * @return array Supported languages with names
     */
    public function list(): array
    {
        $languages = [];
        $supportedLanguages = $this->translation->getSupportedLanguages();

        // Simple language names mapping
        $languageNames = [
            'en' => 'English',
            'sk' => 'Slovenčina',
            'cs' => 'Čeština',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'es' => 'Español',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'ja' => '日本語',
            'zh' => '中文',
            'ar' => 'العربية',
            'hi' => 'हिन्दी',
            'ko' => '한국어',
            'th' => 'ไทย',
            'vi' => 'Tiếng Việt',
            'tr' => 'Türkçe',
            'pl' => 'Polski',
            'nl' => 'Nederlands',
            'sv' => 'Svenska',
            'da' => 'Dansk',
            'no' => 'Norsk',
            'fi' => 'Suomi',
            'el' => 'Ελληνικά',
            'he' => 'עברית',
            'hu' => 'Magyar',
            'ro' => 'Română',
            'bg' => 'Български',
            'hr' => 'Hrvatski',
            'sr' => 'Српски',
            'sl' => 'Slovenščina',
            'et' => 'Eesti',
            'lv' => 'Latviešu',
            'lt' => 'Lietuvių',
            'uk' => 'Українська',
            'be' => 'Беларуская',
            'ca' => 'Català',
            'eu' => 'Euskera',
            'gl' => 'Galego',
            'cy' => 'Cymraeg',
            'ga' => 'Gaeilge',
            'gd' => 'Gàidhlig',
            'is' => 'Íslenska',
            'fo' => 'Føroyskt',
            'mt' => 'Malti',
            'sq' => 'Shqip',
            'mk' => 'Македонски',
            'la' => 'Latina'
        ];

        foreach ($supportedLanguages as $code) {
            $languages[$code] = [
                'code' => $code,
                'name' => $languageNames[$code] ?? ucfirst($code),
                'is_current' => $code === $this->translation->getCurrentLanguage()
            ];
        }

        return [
            'languages' => $languages,
            'current_language' => $this->translation->getCurrentLanguage(),
            'total_count' => count($supportedLanguages)
        ];
    }

    /**
     * Handle AJAX language switch request
     *
     * @return void
     */
    public function handleAjaxSwitch(): void
    {
        header('Content-Type: application/json');

        // Check if request is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        // Get language from POST data
        $input = json_decode(file_get_contents('php://input'), true);
        $language = $input['language'] ?? $_POST['language'] ?? '';

        if (empty($language)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Language not specified']);
            return;
        }

        // Switch language
        $result = $this->switch($language);
        
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    }

    /**
     * Handle regular form-based language switch
     *
     * @param string $redirectUrl URL to redirect after switching
     * @return void
     */
    public function handleFormSwitch(string $redirectUrl = '/'): void
    {
        $language = $_POST['language'] ?? $_GET['lang'] ?? '';

        if (!empty($language)) {
            $this->switch($language);
        }

        // Redirect back to the referring page or specified URL
        $referer = $_SERVER['HTTP_REFERER'] ?? $redirectUrl;
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Reset language preferences to default
     *
     * @param string $defaultLanguage Default language to reset to
     * @return array Response data
     */
    public function reset(string $defaultLanguage = 'en'): array
    {
        try {
            $this->translation->clearPreferences($defaultLanguage);
            
            return [
                'success' => true,
                'message' => 'Language preferences reset successfully',
                'current_language' => $this->translation->getCurrentLanguage(),
                'supported_languages' => $this->translation->getSupportedLanguages()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to reset language preferences: ' . $e->getMessage(),
                'current_language' => $this->translation->getCurrentLanguage(),
                'supported_languages' => $this->translation->getSupportedLanguages()
            ];
        }
    }

    /**
     * Get supported languages with names
     *
     * @return array
     */
    private function getSupportedLanguagesWithNames(): array
    {
        $languageNames = [
            'en' => 'English',
            'sk' => 'Slovenčina',
            'cs' => 'Čeština', 
            'de' => 'Deutsch',
            'fr' => 'Français',
            'es' => 'Español',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'ja' => '日本語',
            'zh' => '中文',
            'ar' => 'العربية',
            'la' => 'Latina'
        ];

        $languages = [];
        $currentLanguage = $this->translation->getCurrentLanguage();
        $supportedLanguages = $this->translation->getSupportedLanguages();

        foreach ($supportedLanguages as $code) {
            $languages[$code] = [
                'code' => $code,
                'name' => $languageNames[$code] ?? ucfirst($code),
                'is_current' => $code === $currentLanguage
            ];
        }

        return $languages;
    }

    /**
     * Create JSON response
     *
     * @param array $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    private function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return new Response(
            $statusCode,
            ['Content-Type' => 'application/json; charset=utf-8'],
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Get translation instance
     *
     * @return Translation Translation instance
     */
    public function getTranslation(): Translation
    {
        return $this->translation;
    }
}
