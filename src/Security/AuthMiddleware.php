<?php
declare(strict_types=1);

namespace RenalTales\Security;

use RenalTales\Core\Config;
use RenalTales\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthMiddleware
{
    private AuthService $authService;
    private Config $config;
    
    public function __construct(AuthService $authService, Config $config)
    {
        $this->authService = $authService;
        $this->config = $config;
    }

    /**
     * Main middleware handler
     */
    public function handle(Request $request, callable $next): Response
    {
        // Check if IP is banned first
        $ipAddress = $this->getClientIP($request);
        if ($this->authService->loginThrottling->isIPBanned($ipAddress)) {
            return $this->createErrorResponse('IP address is banned', 403);
        }

        // Get current user from session
        $user = $this->getCurrentUser($request);
        
        // Add user to request attributes for controllers
        $request->attributes->set('user', $user);
        $request->attributes->set('authenticated', $user !== null);

        // Check route permissions
        $route = $request->getPathInfo();
        $method = $request->getMethod();
        
        if (!$this->checkRoutePermissions($user, $route, $method)) {
            return $this->createUnauthorizedResponse($request);
        }

        // Validate CSRF for state-changing operations
        if ($this->requiresCSRFValidation($request)) {
            if (!$this->validateCSRF($request)) {
                return $this->createErrorResponse('CSRF token validation failed', 403);
            }
        }

        // Check 2FA requirements
        if ($user && $this->requires2FA($user, $route) && !$this->is2FAVerified($request)) {
            return $this->create2FARequiredResponse($request);
        }

        // Rate limiting for API endpoints
        if ($this->isApiRoute($route)) {
            if (!$this->checkRateLimit($request, $user)) {
                return $this->createErrorResponse('Rate limit exceeded', 429);
            }
        }

        // Log security events
        $this->logSecurityEvent($request, $user);

        // Continue to next middleware/controller
        $response = $next($request);

        // Add security headers
        $response = $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Get current authenticated user
     */
    private function getCurrentUser(Request $request): ?User
    {
        // Try session token first
        $sessionToken = $request->cookies->get('session_token');
        if ($sessionToken) {
            $user = $this->authService->validateSession($sessionToken);
            if ($user) {
                return $user;
            }
        }

        // Try API token for API routes
        if ($this->isApiRoute($request->getPathInfo())) {
            $apiToken = $request->headers->get('Authorization');
            if ($apiToken && str_starts_with($apiToken, 'Bearer ')) {
                $token = substr($apiToken, 7);
                return $this->validateApiToken($token);
            }
        }

        return null;
    }

    /**
     * Check route permissions
     */
    private function checkRoutePermissions(?User $user, string $route, string $method): bool
    {
        return $this->authService->hasPermission($user, $route, $method);
    }

    /**
     * Check if CSRF validation is required
     */
    private function requiresCSRFValidation(Request $request): bool
    {
        $method = $request->getMethod();
        $route = $request->getPathInfo();
        
        // Skip CSRF for read-only operations
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return false;
        }

        // Skip CSRF for API routes (they should use proper API authentication)
        if ($this->isApiRoute($route)) {
            return false;
        }

        // Skip CSRF for specific routes (e.g., webhooks)
        $skipCSRFRoutes = $this->config->get('security.skip_csrf_routes', []);
        foreach ($skipCSRFRoutes as $pattern) {
            if ($this->matchRoute($route, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate CSRF token
     */
    private function validateCSRF(Request $request): bool
    {
        $token = $request->headers->get('X-CSRF-TOKEN') 
               ?? $request->request->get('_token')
               ?? $request->query->get('_token');

        if (!$token) {
            return false;
        }

        return $this->authService->validateCSRFToken($token);
    }

    /**
     * Check if route requires 2FA
     */
    private function requires2FA(?User $user, string $route): bool
    {
        if (!$user || !$user->two_factor_enabled) {
            return false;
        }

        $require2FARoutes = $this->config->get('security.require_2fa_routes', []);
        
        foreach ($require2FARoutes as $pattern) {
            if ($this->matchRoute($route, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if 2FA is verified for this session
     */
    private function is2FAVerified(Request $request): bool
    {
        return $request->getSession()->get('2fa_verified', false);
    }

    /**
     * Check if route is an API route
     */
    private function isApiRoute(string $route): bool
    {
        return str_starts_with($route, '/api/');
    }

    /**
     * Check rate limits
     */
    private function checkRateLimit(Request $request, ?User $user): bool
    {
        $identifier = $user ? "user:{$user->id}" : "ip:{$this->getClientIP($request)}";
        $action = "api_request";
        
        $maxRequests = $this->config->get('security.api_rate_limit', 100);
        $timeWindow = $this->config->get('security.api_rate_window', 3600);
        
        return $this->authService->checkRateLimit($action, $identifier, $maxRequests, $timeWindow);
    }

    /**
     * Validate API token
     */
    private function validateApiToken(string $token): ?User
    {
        // This would typically validate against API tokens stored in database
        // For now, returning null (not implemented)
        return null;
    }

    /**
     * Get client IP address
     */
    private function getClientIP(Request $request): string
    {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server->get($header);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->getClientIp() ?? '0.0.0.0';
    }

    /**
     * Match route pattern
     */
    private function matchRoute(string $route, string $pattern): bool
    {
        $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
        $regex = str_replace('*', '.*', $regex);
        $regex = '#^' . $regex . '$#';
        
        return preg_match($regex, $route) === 1;
    }

    /**
     * Log security events
     */
    private function logSecurityEvent(Request $request, ?User $user): void
    {
        $route = $request->getPathInfo();
        $method = $request->getMethod();
        $ip = $this->getClientIP($request);
        
        // Log sensitive operations
        $sensitiveRoutes = ['/admin', '/user/delete', '/user/edit', '/settings'];
        foreach ($sensitiveRoutes as $sensitiveRoute) {
            if (str_starts_with($route, $sensitiveRoute)) {
                $this->authService->logSecurityEvent('sensitive_route_access', [
                    'route' => $route,
                    'method' => $method,
                    'user_id' => $user?->id,
                    'ip_address' => $ip,
                    'user_agent' => $request->headers->get('User-Agent')
                ]);
                break;
            }
        }
    }

    /**
     * Create error response
     */
    private function createErrorResponse(string $message, int $statusCode): Response
    {
        return new JsonResponse([
            'error' => $message,
            'status' => $statusCode
        ], $statusCode);
    }

    /**
     * Create unauthorized response
     */
    private function createUnauthorizedResponse(Request $request): Response
    {
        if ($this->isApiRoute($request->getPathInfo())) {
            return new JsonResponse([
                'error' => 'Unauthorized',
                'message' => 'Authentication required'
            ], 401);
        }

        // For web routes, redirect to login
        $response = new Response('', 302);
        $response->headers->set('Location', '/login?redirect=' . urlencode($request->getUri()));
        return $response;
    }

    /**
     * Create 2FA required response
     */
    private function create2FARequiredResponse(Request $request): Response
    {
        if ($this->isApiRoute($request->getPathInfo())) {
            return new JsonResponse([
                'error' => '2FA Required',
                'message' => 'Two-factor authentication is required for this action'
            ], 403);
        }

        // For web routes, redirect to 2FA verification
        $response = new Response('', 302);
        $response->headers->set('Location', '/verify-2fa?redirect=' . urlencode($request->getUri()));
        return $response;
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders(Response $response): Response
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => $this->getCSPHeader(),
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
        ];

        foreach ($headers as $name => $value) {
            if (!$response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    /**
     * Get Content Security Policy header
     */
    private function getCSPHeader(): string
    {
        $csp = $this->config->get('security.csp', [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline'",
            'style-src' => "'self' 'unsafe-inline'",
            'img-src' => "'self' data: https:",
            'font-src' => "'self'",
            'connect-src' => "'self'",
            'frame-ancestors' => "'none'"
        ]);

        $cspString = '';
        foreach ($csp as $directive => $sources) {
            $cspString .= $directive . ' ' . $sources . '; ';
        }

        return trim($cspString);
    }

    /**
     * Require authentication for route
     */
    public static function requireAuth(callable $next): callable
    {
        return function (Request $request) use ($next) {
            $user = $request->attributes->get('user');
            if (!$user) {
                return new JsonResponse(['error' => 'Authentication required'], 401);
            }
            return $next($request);
        };
    }

    /**
     * Require specific permission for route
     */
    public static function requirePermission(string $permission, callable $next): callable
    {
        return function (Request $request) use ($permission, $next) {
            $user = $request->attributes->get('user');
            if (!$user || !$user->hasPermission($permission)) {
                return new JsonResponse(['error' => 'Insufficient permissions'], 403);
            }
            return $next($request);
        };
    }

    /**
     * Require role for route
     */
    public static function requireRole(string $role, callable $next): callable
    {
        return function (Request $request) use ($role, $next) {
            $user = $request->attributes->get('user');
            if (!$user || !$user->hasRole($role)) {
                return new JsonResponse(['error' => 'Insufficient role'], 403);
            }
            return $next($request);
        };
    }
}
