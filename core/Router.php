<?php
/**
 * 简单路由器
 * 支持 /controller/action 形式
 */
class Router
{
    private $routes = [];

    public function add($method, $pattern, $handler)
    {
        $this->routes[] = [strtoupper($method), $pattern, $handler];
    }

    public function get($pattern, $handler)
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post($pattern, $handler)
    {
        $this->add('POST', $pattern, $handler);
    }

    public function any($pattern, $handler)
    {
        $this->add('GET|POST', $pattern, $handler);
    }

    public function dispatch($path)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // 去掉 query string
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = '/' . trim($path, '/');

        foreach ($this->routes as [$m, $pattern, $handler]) {
            $methods = explode('|', $m);
            if (!in_array($method, $methods)) continue;
            // 转成正则
            $regex = '#^' . preg_replace('#\{([a-z_]+)\}#i', '(?P<$1>[^/]+)', $pattern) . '$#i';
            if (preg_match($regex, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->call($handler, $params);
            }
        }
        // 404
        http_response_code(404);
        if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false || 
            (input('_ajax') || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')) {
            fail('接口不存在', 404);
        }
        require __DIR__ . '/../app/views/errors/404.php';
    }

    private function call($handler, $params)
    {
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                [$class, $method] = explode('@', $handler);
                $obj = new $class();
                return call_user_func_array([$obj, $method], $params);
            }
            // 闭包文件
            $file = __DIR__ . '/../app/routes/' . $handler . '.php';
            if (is_file($file)) {
                $fn = require $file;
                if (is_callable($fn)) {
                    return call_user_func_array($fn, $params);
                }
            }
        }
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        throw new RuntimeException("路由处理器无效: " . print_r($handler, true));
    }
}
