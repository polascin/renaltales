<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Services\LanguageService;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\SessionManager;
use RenalTales\Http\Response;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Language Controller
 *
 * Handles language switching and language-related endpoints for the application.
 *
 * @package RenalTales
 * @author Ľubomír Polaščín
 * @version 2025.3.1.dev
 */
class LanguageController extends AbstractController
{
    /**
     * Constructor
     *
     * @param LanguageService $languageService Language service
     * @param SecurityManager $securityManager Security manager
     * @param SessionManager $sessionManager Session manager
     * @param LoggerInterface $logger Logger
     */
    public function __construct(
        LanguageService $languageService,
        SecurityManager $securityManager,
        SessionManager $sessionManager,
        LoggerInterface $logger
    ) {
        parent::__construct($languageService, $securityManager, $sessionManager, $logger);
    }

    /**
     * Handle HTTP request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        switch ($path) {
            case '/api/language/switch':
                return $this->switchLanguage($request);
            case '/api/language/supported':
                return $this->getSupportedLanguages($request);
            case '/api/language/current':
                return $this->getCurrentLanguage($request);
            default:
                return $this->json(['error' => 'Endpoint not found'], 404);
        }
    }

    /**
     * Switch the application's language
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function switchLanguage(ServerRequestInterface $request): ResponseInterface
    {
        $this->logAction('switch_language');
        
        try {
            $requestedLang = $this->getParameter($request, 'lang');
            
            if (!$requestedLang || !is_string($requestedLang)) {
                return $this->json(['success' => false, 'error' => 'Language parameter is required'], 400);
            }
            
            if (!$this->languageService->isLanguageSupported($requestedLang)) {
                return $this->json(['success' => false, 'error' => 'Invalid language code'], 400);
            }
            
            $success = $this->languageService->switchLanguage($requestedLang);
            
            if ($success) {
                if ($this->isAjax($request)) {
                    return $this->json(['success' => true, 'language' => $requestedLang]);
                } else {
                    $referer = $request->getHeaderLine('Referer') ?: '/';
                    return $this->redirect($referer);
                }
            } else {
                return $this->json(['success' => false, 'error' => 'Failed to switch language'], 500);
            }
        } catch (\Exception $e) {
            $this->logError('Error switching language', $e);
            return $this->json(['success' => false, 'error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get the list of supported languages
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getSupportedLanguages(ServerRequestInterface $request): ResponseInterface
    {
        $this->logAction('get_supported_languages');
        
        try {
            return $this->json([
                'supported_languages' => $this->languageService->getSupportedLanguages(),
                'language_names' => $this->languageService->getSupportedLanguagesWithNames(),
                'current_language' => $this->languageService->getCurrentLanguage()
            ]);
        } catch (\Exception $e) {
            $this->logError('Error getting supported languages', $e);
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get the current language
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getCurrentLanguage(ServerRequestInterface $request): ResponseInterface
    {
        $this->logAction('get_current_language');
        
        try {
            return $this->json([
                'current_language' => $this->languageService->getCurrentLanguage(),
                'language_name' => $this->languageService->getLanguageName(
                    $this->languageService->getCurrentLanguage()
                )
            ]);
        } catch (\Exception $e) {
            $this->logError('Error getting current language', $e);
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Create a basic response
     *
     * @param string $body
     * @param int $status
     * @return ResponseInterface
     */
    protected function createResponse(string $body, int $status = 200): ResponseInterface
    {
        return new Response($status, [], $body);
    }

    /**
     * Get controller name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'LanguageController';
    }

    /**
     * Get supported HTTP methods
     *
     * @return array<string>
     */
    public function getSupportedMethods(): array
    {
        return ['GET', 'POST', 'PUT'];
    }
}
