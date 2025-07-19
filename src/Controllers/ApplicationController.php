<?php

declare(strict_types=1);

namespace RenalTales\Controllers;


use Psr\Http\Message\ServerRequestInterface;
use RenalTales\Contracts\ControllerInterface;
use RenalTales\Http\Response;
use RenalTales\Core\Template;
use Psr\Http\Message\ResponseInterface;

/**
 * Simple Application Controller
 *
 * Streamlined controller with direct template rendering.
 * Implements: $template->render('home', ['data' => $data])
 *
 * @package RenalTales
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class ApplicationController implements ControllerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $page = $this->getRequestedPage($request);
        $data = $this->preparePageData($request, $page);

        $template = new Template();
        return $this->createHtmlResponse(
            $template->render($page, $data)
        );
    }

    private function getRequestedPage(ServerRequestInterface $request): string
    {
        $params = $this->getQueryParams($request);
        $page = $params['page'] ?? 'home';
        return is_string($page) ? trim($page) : 'home';
    }

    private function preparePageData(ServerRequestInterface $request, string $page): array
    {
        $params = $this->getQueryParams($request);
        return [
            'page_title' => ucfirst($page),
            'app_name' => 'RenalTales',
            'language' => $params['lang'] ?? 'en',
            'current_page' => $page,
            'year' => date('Y')
        ];
    }

    /**
     * Parse query parameters from PSR-7 ServerRequestInterface
     */
    private function getQueryParams(ServerRequestInterface $request): array
    {
        parse_str($request->getUri()->getQuery(), $params);
        return $params;
    }

    private function createHtmlResponse(string $html): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $html);
    }

    public function getName(): string
    {
        return 'ApplicationController';
    }
}