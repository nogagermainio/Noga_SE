<?php
namespace Src\Router;

use RuntimeException;
use Throwable;

class Router{
    private array $routes = [];
    private array $currentRoutes = [];
    private array $controllers = [];
    private array $middlewares = [];
    private array $groupMiddleware = [];
    private array $groupStack = [];
    private array $globalMiddleware = [];
    private array $listRoutes = [];
    private array $where = [];
    private string $name = "";
    private string $method = "";
    private string $path = "";
    private string $regexRoutes = "#\{(\w+)(?::([^}]+))?\}#";
    private string $regexDefault = "([^/]+)";
    private static ?self $instance = null;
   

    public function __construct()
    {
        // container injection
    }

    private static function instance():Router{
       return self::$instance ??= new static();
    } 

    public function add(string $method,string $path):static{
        $this->method = $method;
        $this->path = $path;

        $this->currentRoutes = [
            "METHOD"=>$method,
            "NAME"=>null,
            "PATH"=>$this->buildPath($path),
            "CONTROLLER"=>[],
            "MIDDLEWARES"=>[],
            "WHERE"=>[],
            "PATTERN"=>null,
            "KEYS"=>null
        ];

        return $this;
    }

    public function get(string $path):static{
        return $this->add("GET",$path);
    }

     public function post(string $path):static{
        return $this->add("POST",$path);
    }

     public function put(string $path):static{
         $this->add("PUT",$path);
        return $this;
    }

     public function delete(string $path):static{
        return $this->add("DELETE",$path);
    }

     public function name(string $name):static{
        $this->name = $name;
        $this->currentRoutes['NAME'] = $name;
        $this->register();
        return $this;
     }

     public function where(array $condition):static{
        $this->where = $condition;
        $this->currentRoutes['WHERE'] = $condition;
        return $this;
     }

    public function controller(array | string $controller):static{
        $controllers = is_string($controller) ? [$controller] : $controller;
        $this->controllers = $controllers;
        $this->currentRoutes['CONTROLLER'] = $this->normalizeController($controllers);
        return $this;
    }

    public function middleware(array | string $middleware):static{
        $middlewares = is_string($middleware) ? [$middleware] : $middleware;
        $this->middlewares = $middlewares;
        $this->currentRoutes['MIDDLEWARES'] = \array_merge(
            $this->currentRoutes['MIDDLEWARES'],
            $this->normalizeMiddleware($middlewares)
        );
        return $this;
    }

    public static function globalMiddleware(array | string $middleware,string $method = "GET"):void{
       $middlewares = \is_string($middleware) ? [$middleware] : $middleware;
       self::instance()->globalMiddleware[$method][] = self::instance()->normalizeMiddleware($middlewares);
    }

    public function group(string $prefix,callable $callback,array | string $middleware = []){
        $middlewares = \is_string($middleware) ? [$middleware] : $middleware;
        $this->groupStack[] = [
            "prefix"=>$prefix,
            "middleware"=> $this->normalizeMiddleware($middlewares)
        ];

        $callback($this);

        array_pop($this->groupStack);

        return $this;
    }

    public function register():void{


        $method = $this->currentRoutes["METHOD"];
        $path = $this->currentRoutes['PATH'];
        
        if(empty($this->currentRoutes['CONTROLLER'])){
            $this->RouteException("no controller in route : method : {$method} path : {$path} ");
        }

        $this->currentRoutes['MIDDLEWARES'] = \array_merge(
            $this->currentRoutes['MIDDLEWARES'],
            $this->collectGroupMiddleware()
        );

        $this->routes["GLOBAL_MIDDLEWARES"] = self::instance()->globalMiddleware;

        $pattern = $this->getPattern($this->currentRoutes);

        $this->currentRoutes['PATTERN'] = $pattern['pattern'];
        $this->currentRoutes['KEYS'] = $pattern['keys'];

        $this->routes[$method][$path] = $this->currentRoutes;

        $this->currentRoutes = [];

    }

    private function normalizePath(string $path):string{
        $paths = '';
        if(str_contains($path,'.')){
            $paths = explode('.',trim($path,'/'));
            $paths = implode('/',$paths);
            
        }else{
            $paths = trim($path,'/');
        }

        return $paths === '/' ? '/' : "/$paths";
    }

    private function buildPath(string $path):string{
        $prefix = '';
        $paths = '';
        foreach($this->groupStack as $group){
            $prefix .= $group['prefix'];
        }

        $paths = "$prefix/$path";

        return $this->normalizePath($paths);
    }

    private function normalizeController(array $controller){
        $contrl = [];
        $c = [];

        foreach($controller as $ctr){

           if(\str_contains($ctr,'.')){
                $contrl = explode('.',$ctr,2);
                $c = ["App\\Controller\\{$contrl[0]}",$contrl[1]];

           }else{
            $c[] = $ctr;
           }

        }

        return $c;
    }

    private function normalizeMiddleware(array $middleware){
        $middle = [];
        $md = [];

        foreach($middleware as $mdl){

           if(\str_contains($mdl,'.')){
                $middle = explode('.',$mdl,2);
                $md[] = ["App\\Controller\\{$middle[0]}",$middle[1]];

           }else{
            $md[] = $mdl;
           }

        }

        return $md;
    }


    public function collectGroupMiddleware(){
        $middleware = [];
        foreach($this->groupStack as $group){
            $middleware = $group['middleware'];
        }

        return $middleware;
    }


    public function route():array{
        return $this->routes;
    }

    private function RouteException(string $message){
        throw new RuntimeException($message);
    }

      private function getPattern(array $route): array {

        preg_match_all(
            $this->regexRoutes,
            $route['PATH'],
            $keys
        );
       $pattern = "";
        $pattern = preg_replace_callback(
            $this->regexRoutes,
            function ($m) use ($route) {
                $param = $m[1];

                if (isset($route["WHERE"][$param])) {
                    return "({$route['WHERE'][$param]})";
                }

                return isset($m[2]) ? "({$m[2]})" : $this->regexDefault;
            },
            $route['PATH']
        );

        return [
            "pattern" => "#^$pattern$#",
            "keys"    =>  $keys[1] ?? [],
        ];
    }


}