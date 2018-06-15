<?php
namespace App;

class Dependencies {
    private $container;
    
    function __construct($app) {
        $container = $app->getContainer(); // Dependency injection container
        $this->container = $container;
        $this->dependencies(); // Load dependencies into container
        $this->inject($app); // Inject dependencies into controllers
        $this->handlers(); // Set custom handlers
    }
    
    // Setup dependency container
    function dependencies() {
        // Monolog
        $this->container['logger'] = function($c) {
            $logger = new \Monolog\Logger('myLogger');
            $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
            $logger->pushHandler($file_handler);
            return $logger;
        };
        // Eloquent ORM
        $this->container['db'] = function($c) {
            $capsule = new \Illuminate\Database\Capsule\Manager;
            $capsule->addConnection($c['settings']['db']);

            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule;
        };
        // awurth/SlimValidation
        $this->container['validator'] = function($c) {
            return new \Awurth\SlimValidation\Validator();
        };
    }
    
    // Inject dependencies into controllers
    function inject($app) {
        // User
        $this->container['\App\Controllers\UserController'] = function($c) use ($app) {
            return new \App\Controllers\UserController($c->get('logger'), $c->get('db'), $c->get('validator'));
        };
        // Todo
        $this->container['\App\Controllers\TodoController'] = function($c) use ($app) {
            return new \App\Controllers\TodoController($c->get('logger'), $c->get('db'), $c->get('validator'));
        };
        // Category
        $this->container['\App\Controllers\CategoryController'] = function($c) use ($app) {
            return new \App\Controllers\CategoryController($c->get('logger'), $c->get('db'), $c->get('validator'));
        };
    }
    
    // Custom handlers
    function handlers() {
        // 404 custom response
        $this->container['notFoundHandler'] = function($c) {
            return function($request, $response) use ($c) {
                return $c['response']->withJson(['errors' => 'Resource not found'], 404);
            };
        };
    }
}