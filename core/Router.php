<?php

namespace Core;

use Core\Exceptions\HttpException;
use Core\Http\Request;
use Core\Http\Response;

final class Router
{
    /** @var array<int, array{method:string,pattern:string,handler:array,middleware:array}> */
    private array $routes = [];

    /** @var array<string, callable> */
    private array $middleware = [];

    /** @var array<string, array{regex:string,params:array<int,string>}> */
    private array $compiled = [];

    public function __construct(private readonly Request $request)
    {
    }

    public function add(string $method, string $pattern, array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => trim($pattern, '/'),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function alias(string $name, callable $factory): void
    {
        $this->middleware[$name] = $factory;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $path = $this->request->path();
        $pathMatched = false;

        foreach ($this->routes as $route) {
            $compiled = $this->compile($route['pattern']);
            if (!preg_match($compiled['regex'], $path, $matches)) {
                continue;
            }

            $pathMatched = true;
            if ($route['method'] !== $method) {
                continue;
            }

            $params = [];
            foreach ($compiled['params'] as $name) {
                $params[] = $matches[$name] ?? null;
            }

            $this->runMiddleware($route['middleware']);
            $this->invoke($route['handler'], $params);
            return;
        }

        if ($pathMatched) {
            throw new HttpException(405, 'Méthode non autorisée');
        }

        throw new HttpException(404, 'Page introuvable');
    }

    /** @param array<int, string> $params */
    private function invoke(array $handler, array $params): void
    {
        [$class, $method] = $handler;

        if (!class_exists($class) || !method_exists($class, $method)) {
            throw new HttpException(500, 'Action introuvable');
        }

        $controller = new $class();
        $controller->{$method}(...$params);
    }

    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $entry) {
            if (!is_string($entry) || $entry === '') {
                continue;
            }

            [$name, $argument] = array_pad(explode(':', $entry, 2), 2, '');
            if (!isset($this->middleware[$name])) {
                throw new HttpException(500, 'Middleware inconnu: ' . $name);
            }

            $resolver = $this->middleware[$name];
            $callback = $resolver($argument);
            if (is_callable($callback)) {
                $callback($this->request);
            }
        }
    }

    /** @return array{regex:string,params:array<int,string>} */
    private function compile(string $pattern): array
    {
        if (isset($this->compiled[$pattern])) {
            return $this->compiled[$pattern];
        }

        $tokens = preg_split('/(\{[^}]+\})/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $regex = '';
        $params = [];

        foreach ($tokens as $token) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $token, $match)) {
                $name = $match[1];
                $params[] = $name;
                $regex .= '(?P<' . $name . '>[^/]+)';
            } else {
                $regex .= preg_quote($token, '#');
            }
        }

        return $this->compiled[$pattern] = [
            'regex' => '#^' . $regex . '$#',
            'params' => $params,
        ];
    }
}
