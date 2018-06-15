<?php
namespace App\Routes;

class User {
    function __construct($app) {
        $app->post('/users', '\App\Controllers\UserController:create');
        $app->post('/users/login', '\App\Controllers\UserController:login');
    }
}