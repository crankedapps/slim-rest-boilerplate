<?php
namespace App\Includes;

use Respect\Validation\Validator as V;

class ValidationRules {
    function common() {
        return [
            'username' => V::length(3, 25)->alnum('-')->noWhitespace(),
            'password' => V::length(3, 25)->alnum('-')->noWhitespace()
        ];
    }
    // POST /categories
    function categoriesPost() {
        return [
            'name' => V::length(3, 25)->alnum('-')
        ];
    }
    
    // PUT /categories
    function categoriesPut() {
        return [
            'name' => [
                'rules' => V::optional(V::length(3, 25)->alnum('-')), // optional
                'message' => 'Invalid name' // custom error message (optional flag from rule supresses standard errors? suspicious of bug in awurth/slim-validation)
            ]
        ];
    }
    
    // POST /todo
    function todoPost() {
        return [
            'name' => V::length(3, 25)->alnum('-'),
            'category' => [
                'rules' => V::numeric()->positive(),
                'message' => 'Invalid category ID' // custom error message
            ]
        ];
    }
    
    // PUT /todo
    function todoPut() {
        return [
            'name' => [
                'rules' => V::optional(V::length(3, 25)->alnum('-')), // optional
                'message' => 'Invalid name'
            ],
            'category' => [
                'rules' => V::optional(V::numeric()->positive()), // optional
                'message' => 'Invalid category ID'
            ]
        ];
    }
    
    // POST /users
    function usersPost() {
        return [
            'username' => self::common()['username'],
            'password' => self::common()['password']
        ];
    }
    
    // POST /auth
    function authPost() {
        return [
            'username' => self::common()['username'],
            'password' => self::common()['password']
        ];
    }
}
?>