<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private string $baseDir;

    public function __construct()
    {
        // SCRIPT_NAME = /index.php  (on InfinityFree public/ is web root)
        // Strip the filename so we get the directory, e.g. "" or "/subdir"
        $this->baseDir = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']), '/');
    }

    public function add(string $path, callable|array $callback): void
    {
        $this->routes[$path] = $callback;
    }

    public function run(): void
    {
        $parsedUri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $routePath = $this->parseRoutePath($parsedUri);

        // Serve real static files directly (CSS, JS, images, uploads…)
        if ($this->processStaticFiles($parsedUri)) {
            return;
        }

        if (array_key_exists($routePath, $this->routes)) {
            $this->executeCallback($this->routes[$routePath]);
        } else {
            $this->render404($routePath);
        }
    }

    private function parseRoutePath(string $uri): string
    {
        // Remove base directory prefix from URI
        $cleanPath = $this->baseDir !== ''
            ? substr($uri, strlen($this->baseDir))
            : $uri;

        $cleanPath = str_replace('/index.php', '', $cleanPath);
        $cleanPath = rtrim($cleanPath, '/') ?: '/';

        // Strip query string just in case
        if (($q = strpos($cleanPath, '?')) !== false) {
            $cleanPath = substr($cleanPath, 0, $q);
        }

        return $cleanPath === '' ? '/' : $cleanPath;
    }

    private function processStaticFiles(string $requestUri): bool
    {
        $relativePath = $this->baseDir !== ''
            ? str_replace($this->baseDir, '', $requestUri)
            : $requestUri;

        $absolutePath = __DIR__ . '/../../public' . $relativePath;

        if ($relativePath !== '/' && is_readable($absolutePath) && is_file($absolutePath)) {
            $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
            header("Content-Type: {$mimeType}");
            readfile($absolutePath);
            return true;
        }

        return false;
    }

    private function executeCallback(callable|array $handler): void
    {
        if (is_array($handler)) {
            [$controllerClass, $methodName] = $handler;
            $instance = new $controllerClass();
            $instance->$methodName();
        } else {
            $handler($this->baseDir);
        }
    }

    private function render404(string $path): void
    {
        http_response_code(404);
        echo '<div style="font-family:system-ui;max-width:600px;margin:80px auto;text-align:center;">';
        echo '<h1>404 — Сторінку не знайдено</h1>';
        echo '<p style="color:#86868b;">На жаль, за адресою <code>' . htmlspecialchars($path) . '</code> нічого немає.</p>';
        echo '<a href="' . htmlspecialchars($this->baseDir) . '/" style="color:#0071e3;">← Повернутися на головну</a>';
        echo '</div>';
    }

    public function getBaseDir(): string
    {
        return $this->baseDir;
    }
}
