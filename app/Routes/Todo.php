<?php
namespace App\Routes;

class Todo {
    function __construct($app) {
        $app->get('/todo', '\App\Controllers\TodoController:all');
        $app->get('/todo/{id}', '\App\Controllers\TodoController:find');
        $app->post('/todo', '\App\Controllers\TodoController:create');
        $app->put('/todo/{id}', '\App\Controllers\TodoController:update');
        $app->delete('/todo/{id}', '\App\Controllers\TodoController:delete');
    }
}