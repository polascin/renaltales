<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Contracts\ControllerInterface;
use RenalTales\Http\Response;
use RenalTales\Core\Template;
use Psr\Http\Message\ServerRequestInterface;
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
        $page = $request->getQueryParams()['page'] ?? 'home';
        return is_string($page) ? trim($page) : 'home';
    }

    private function preparePageData(ServerRequestInterface $request, string $page): array
    {
        return [
            'page_title' => ucfirst($page),
            'app_name' => 'RenalTales',
            'language' => $request->getQueryParams()['lang'] ?? 'en',
            'current_page' => $page,
            'year' => date('Y')
        ];
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
