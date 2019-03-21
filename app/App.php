<?php
namespace App;

class App {
    private $app;
    
    public function __construct() {
        // initialize Slim App
        $app = new \Slim\App(\App\Config\Config::slim());
        $this->app = $app;
        // initialize dependencies
        $this->dependencies();
        // initialize middlewares
        $this->middleware();
        // initialize routes
        $this->routes();
    }
    
    public function get() {
        return $this->app;
    }
    
    private function dependencies() {
        return new \App\Dependencies($this->app);
    }
    
    private function middleware() {
        return new \App\Middleware($this->app);
    }
    
    private function routes() {
        return [
            new \App\Routes\Categories($this->app),
            new \App\Routes\Todo($this->app),
            new \App\Routes\User($this->app)
        ];
    }
}
