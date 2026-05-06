<?php
namespace App\Core;

class Router {
    private array $routes = [];
    private string $baseDir;

    public function __construct() {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $this->baseDir = str_replace('/index.php', '', $scriptName);
    }

    public function add(string $path, callable|array $callback): void {
        $this->routes[$path] = $callback;
    }

    public function run(): void {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestUri = urldecode($requestUri);
        
        $path = substr($requestUri, strlen($this->baseDir));
        $path = ($path === '' || $path === '/') ? '/' : $path;
        $path = str_replace('/index.php', '', $path);
        $path = ($path === '') ? '/' : $path;

        if ($this->handleStatic($requestUri)) return;

        if (isset($this->routes[$path])) {
            $callback = $this->routes[$path];

            if (is_array($callback)) {
                [$controllerClass, $method] = $callback;
                $controller = new $controllerClass();
                $controller->$method();
            } else {
                $callback($this->baseDir);
            }
        } else {
            $this->render404($requestUri, $path);
        }
    }

    private function handleStatic($requestPath): bool {
        $relativePath = str_replace($this->baseDir, '', $requestPath);
        $fullPathToFile = __DIR__ . '/../../public' . $relativePath;

        if ($relativePath !== '/' && is_file($fullPathToFile)) {
            $mimeType = mime_content_type($fullPathToFile);
            header("Content-Type: $mimeType");
            readfile($fullPathToFile);
            exit;
        }
        return false;
    }

    private function render404($requestUri, $path): void {
        http_response_code(404);
        echo "<h1>404 - Сторінку не знайдено</h1>";
        echo "<b>DEBUG INFO:</b><br>";
        echo "Повний запит: " . htmlspecialchars($requestUri) . "<br>";
        echo "Базова папка: " . htmlspecialchars($this->baseDir) . "<br>";
        echo "Очищений шлях: " . htmlspecialchars($path) . "<br>";
        echo '<a href="' . $this->baseDir . '/">Повернутися на головну</a>';
    }

    public function getBaseDir(): string {
        return $this->baseDir;
    }
}