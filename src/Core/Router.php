<?php
namespace App\Core;

class Router {
    private array $routes = [];

    private string $baseDir;

    public function __construct() {
        $this->baseDir = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    }

    public function add(string $path, callable|array $callback): void {
        $this->routes[$path] = $callback;
    }

    public function run(): void {
        $parsedUri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        $routePath = $this->parseRoutePath($parsedUri);

        if ($this->processStaticFiles($parsedUri)) {
            return;
        }

        if (array_key_exists($routePath, $this->routes)) {
            $this->executeCallback($this->routes[$routePath]);
        } else {
            $this->render404($parsedUri, $routePath);
        }
    }

    private function parseRoutePath(string $uri): string {
        $cleanPath = substr($uri, strlen($this->baseDir));
        $cleanPath = str_replace('/index.php', '', $cleanPath);

        return empty($cleanPath) || $cleanPath === '/' ? '/' : $cleanPath;
    }

    private function processStaticFiles(string $requestUri): bool {
        $relativePath = str_replace($this->baseDir, '', $requestUri);
        $absolutePath = __DIR__ . '/../../public' . $relativePath;

        if ($relativePath !== '/' && is_readable($absolutePath) && is_file($absolutePath)) {
            $mimeType = mime_content_type($absolutePath);
            header("Content-Type: {$mimeType}");
            readfile($absolutePath);
            return true;
        }

        return false;
    }

    private function executeCallback(callable|array $handler): void {
        if (is_array($handler)) {
            [$controllerClass, $methodName] = $handler;
            $controllerInstance = new $controllerClass();
            $controllerInstance->$methodName();
        } else {
            $handler($this->baseDir);
        }
    }

    private function render404(string $requestUri, string $path): void {
        http_response_code(404);
        echo "<div style='font-family: system-ui; max-width: 600px; margin: 50px auto; text-align: center;'>";
        echo "<h1>404 — Сторінку не знайдено</h1>";
        echo "<p style='color: #86868b;'>На жаль, за цим маршрутом нічого немає.</p>";
        echo "<a href='" . htmlspecialchars($this->baseDir) . "/' style='...'>Повернутися на головну</a>";
        echo "</div>";
    }

    public function getBaseDir(): string {
        return $this->baseDir;
    }
}