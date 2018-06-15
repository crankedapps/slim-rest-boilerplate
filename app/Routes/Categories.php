<?php
namespace App\Routes;

class Categories {
    function __construct($app) {
        
        /* Basic routing */
        $app->get('/categories', '\App\Controllers\CategoryController:all');
        $app->get('/categories/{id}', '\App\Controllers\CategoryController:find');
        $app->post('/categories', '\App\Controllers\CategoryController:create');
        $app->put('/categories/{id}', '\App\Controllers\CategoryController:update');
        $app->delete('/categories/{id}', '\App\Controllers\CategoryController:delete');
        $app->get('/categories/{id}/todos', '\App\Controllers\CategoryController:todos');
        
        /*
         * Route grouping example:
         * 
        $app->group('/categories', function () {
            $this->get('', '\App\Controllers\CategoryController:all');
            $this->post('', '\App\Controllers\CategoryController:create');
            $this->get('/{id}', '\App\Controllers\CategoryController:find');
            $this->put('/{id}', '\App\Controllers\CategoryController:update');
            $this->delete('/{id}', '\App\Controllers\CategoryController:delete');
            $this->get('/{id}/todos', '\App\Controllers\CategoryController:todos');
        });
         */
        
    }
}