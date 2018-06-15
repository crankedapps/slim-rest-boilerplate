<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \App\Models\Todo as Todo;
use \App\Models\Category as Category;
use App\Includes\ValidationRules as ValidationRules;

class TodoController {
    private $logger;
    private $db;
    private $validator;
    
    private $table;

    // Dependency injection via constructor
    public function __construct($depLogger, $depDB, $depValidator) {
        $this->logger = $depLogger;
        $this->db = $depDB;
        $this->validator = $depValidator;
        $this->table = $this->db->table('todo');
    }
    
    // GET /todo
    // Lists all todos items
    public function all(Request $request, Response $response) {
        $this->logger->addInfo('GET /todo');
        $user = $request->getAttribute('user');
        $todo = $user->todos()->get();
        return $response->withJson([
            'data' => $todo ? $todo : []
        ], 200);
    }
    
    // GET /todo/{id}
    // Return todo item data by ID
    public function find(Request $request, Response $response, $args) {
        $this->logger->addInfo('GET /todo/'.$args['id']);
        $user = $request->getAttribute('user');
        $todo = $user->todos()->find($args['id']);
        return $response->withJson(['data' => $todo ? $todo : []], 200);
    }
    
    // POST /todo
    // Create todo item
    public function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $errors = [];
        // The validate method returns the validator instance
        $validator = $this->validator->validate($request, ValidationRules::todoPost());
        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
        }
        // Check category exists
        if (!$errors) {
            $category = $user->categories()->find($data['category']);
            if (!$category) {
                $errors = ['Category ID invalid'];
            }
        }
        // record creation
        if (!$errors) {
            // Input is valid, so let's do something...
            $todo = $user->todos()->firstOrCreate([
                'name' => $data['name'],
                'category_id' => $category->id
            ]);
            return $response->withJson([
                'success' => true,
                'id' => $todo->id
            ], 200);
        }
        // error
        return $response->withJson([
            'success' => false,
            'errors' => $errors
        ], 400);
    }
    
    // PUT /todo/{id}
    // Deletes a todo item
    public function update(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $errors = [];
        // validate inputs
        $validator = $this->validator->validate($request, ValidationRules::todoPut());
        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
        }
        // check todo ID exists
        $todo = $user->todos()->find($args['id']);
        if (!$errors && !$todo) {
            $errors = ['Todo not found: '.$args['id']];
        }
        if (!$errors) {
            if (isset($data['category'])) {
                // validate category input
                if (!$user->categories()->find($data['category'])) {
                    $errors = ['Category not found'];
                }
            }
        }
        // check for duplicate name
        if (!$errors && isset($data['name'])) {
            $whereCond = [
                ['name', '=', $data['name']],
                ['category_id', '=', (isset($data['category']) ? $data['category'] : $todo->category)],
                ['id', '!=', $todo->id]
            ];
            if ($user->todos()->where($whereCond)->first()) {
                $errors = ['Todo item name already exists in category'];
            }
        }
        // No errors? Update
        if (!$errors) {
            if (isset($data['name'])) { $todo->name = $data['name']; }
            if (isset($data['category'])) { $todo->category_id = $data['category']; }
            $todo->save();
            return $response->withJson(['success' => true], 200);
        }
        // Errors found
        return $response->withJson([
            'success' => false,
            'errors' => $errors
        ], 400);
    }
    
    // DELETE /todo/{id}
    // Delete a todo item
    public function delete(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $errors = [];
        // check todo ID exists
        $todo = $user->todos()->withTrashed()->find($args['id']);
        if (!$errors && !$todo) {
            $errors = ['Todo not found: '.$args['id']];
        }
        if (!$errors) {
            $deleted = (isset($data['force']) && !empty($data['force'])) ? $todo->forceDelete() : $todo->delete();
            return $response->withJson(['success' => true], 200);
        }
        // Errors found
        return $response->withJson([
            'success' => false,
            'errors' => $errors
        ], 400);
    }
}