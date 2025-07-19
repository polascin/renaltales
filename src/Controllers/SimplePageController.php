<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Contracts\ControllerInterface;
use RenalTales\Http\Response;
use RenalTales\Core\Template;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Simple Page Controller
 *
 * Ultra-simplified controller that directly renders pages using template engine.
 * No dependency injection, no abstract classes, just simple rendering.
 *
 * @package RenalTales\Controllers
 * @version 2025.v3.1.dev  
 * @author Ľubomír Polaščín
 */
class SimplePageController implements ControllerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $page = $this->getPage($request);
        $data = $this->getData($request);
        
        // Direct template rendering: $template->render('home', ['data' => $data])
        $template = new Template();
        $output = $template->render($page, ['data' => $data]);
        
        return $this->htmlResponse($output);
    }
    
    private function getPage(ServerRequestInterface $request): string
    {
        return $request->getQueryParams()['page'] ?? 'home';
    }
    
    private function getData(ServerRequestInterface $request): array
    {
        return [
            'title' => 'RenalTales',
            'language' => $request->getQueryParams()['lang'] ?? 'en',
            'content' => 'Welcome to RenalTales community platform'
        ];
    }
    
    private function htmlResponse(string $html): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }
    
    public function getName(): string
    {
        return 'SimplePageController';
    }
}
