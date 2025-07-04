<?php
/**
 * Router Class
 * Handles URL routing and request dispatching
 */

class Router {
    private $routes = [];
    private $params = [];

    public function get($uri, $action) {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action) {
        $this->addRoute('POST', $uri, $action);
    }

    public function put($uri, $action) {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete($uri, $action) {
        $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute($method, $uri, $action) {
        $uri = trim($uri, '/');
        $this->routes[$method][$uri] = $action;
    }

    public function route($method, $uri) {
        $uri = trim($uri, '/');
        
        // Try exact match first
        if (isset($this->routes[$method][$uri])) {
            return $this->dispatch($this->routes[$method][$uri]);
        }

        // Try pattern matching
        foreach ($this->routes[$method] as $pattern => $action) {
            if ($this->matchPattern($pattern, $uri)) {
                return $this->dispatch($action);
            }
        }

        // No route found
        $this->handleNotFound();
    }

    private function matchPattern($pattern, $uri) {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Remove the full match from the beginning
            array_shift($matches);
            $this->params = $matches;
            return true;
        }

        return false;
    }

    private function dispatch($action) {
        if (is_string($action)) {
            list($controller, $method) = explode('@', $action);
            
            $controllerFile = CONTROLLERS_PATH . '/' . $controller . '.php';
            
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: {$controllerFile}");
            }

            require_once $controllerFile;

            if (!class_exists($controller)) {
                throw new Exception("Controller class not found: {$controller}");
            }

            $controllerInstance = new $controller();

            if (!method_exists($controllerInstance, $method)) {
                throw new Exception("Method not found: {$controller}::{$method}");
            }

            // Call the controller method with parameters
            return call_user_func_array([$controllerInstance, $method], $this->params);
        }

        if (is_callable($action)) {
            return call_user_func_array($action, $this->params);
        }

        throw new Exception("Invalid route action");
    }

    private function handleNotFound() {
        http_response_code(404);
        
        // Check if 404 view exists
        $notFoundView = VIEWS_PATH . '/errors/404.php';
        if (file_exists($notFoundView)) {
            include $notFoundView;
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The requested page could not be found.</p>';
        }
        exit;
    }

    public function getParams() {
        return $this->params;
    }

    public function getParam($index, $default = null) {
        return isset($this->params[$index]) ? $this->params[$index] : $default;
    }

    public function redirect($url, $code = 302) {
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }

    public static function url($path = '') {
        $baseUrl = rtrim(APP_URL, '/');
        $path = ltrim($path, '/');
        return $path ? "{$baseUrl}/{$path}" : $baseUrl;
    }

    public static function asset($path) {
        $baseUrl = rtrim(APP_URL, '/');
        $path = ltrim($path, '/');
        return "{$baseUrl}/assets/{$path}";
    }
}
