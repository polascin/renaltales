<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Services\LanguageService;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\SessionManager;
use RenalTales\Contracts\ControllerInterface;
use RenalTales\Views\HomeView;
use RenalTales\Views\ErrorView;
use RenalTales\Http\Response;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Application Controller
 *
 * Handles the main application logic using dependency injection.
 *
 * @package RenalTales
 */
class ApplicationController extends AbstractController implements ControllerInterface
{
    private ViewController $viewController;
    private string $requestedPage;

    public function __construct(
        LanguageService $languageService,
        SessionManager $sessionManager,
        SecurityManager $securityManager,
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
        try {
            $this->requestedPage = $this->determineRequestedPage($request);
            $this->viewController = new ViewController($this->requestedPage, $this->languageService);
            return $this->view($this->viewController);
        } catch (\Exception $e) {
            $this->logError('Error handling request', $e);
            $errorView = new ErrorView($e, true, null);
            return $this->view($errorView);
        }
    }

    /**
     * Determine the requested page
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    private function determineRequestedPage(ServerRequestInterface $request): string
    {
        $page = $this->getParameter($request, 'page', 'home');
        return is_string($page) ? trim($page) : 'home';
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
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ApplicationController';
    }

    /**
     * Get the requested page
     *
     * @return string The requested page
     */
    public function getRequestedPage(): string
    {
        return $this->requestedPage;
    }

    /**
     * Get the view controller
     *
     * @return ViewController The view controller
     */
    public function getViewController(): ViewController
    {
        return $this->viewController;
    }
}
