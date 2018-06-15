<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \App\Models\Category as Category;
use App\Includes\ValidationRules as ValidationRules;

class CategoryController {
    private $logger;
    private $db;
    private $validator;
    
    private $table;

    // Dependency injection via constructor
    public function __construct($depLogger, $depDB, $depValidator) {
        $this->logger = $depLogger;
        $this->db = $depDB;
        $this->validator = $depValidator;
        $this->table = $this->db->table('categories');
    }
    
    // GET /categories
    // Lists all categories
    public function all(Request $request, Response $response) {
        $user = $request->getAttribute('user');
        $this->logger->addInfo('GET /categories');
        $categories = $user->categories()->withCount('todos')->get();
        return $response->withJson(['data' => $categories], 200);
    }
    
    // GET /categories/{id}
    // Retrieve category data by ID
    public function find(Request $request, Response $response, $args) {
        $this->logger->addInfo('GET /categories/'.$args['id']);
        $user = $request->getAttribute('user');
        $category = $user->categories()->withCount('todos')->find($args['id']);
        if ($category) {
            return $response->withJson([
                'success' => true,
                'data' => $category
            ], 200);
        } else {
            return $response->withJson([
                'success' => false,
                'errors' => 'Category not found'
            ], 400);
        }
    }
    
    // GET /categories/{id}/todos
    // Retrieve category's todo items by ID
    public function todos(Request $request, Response $response, $args) {
        $this->logger->addInfo('GET /categories/'.$args['id'].'/todos');
        $user = $request->getAttribute('user');
        $category = $user->categories()->find($args['id']);
        return $response->withJson(['data' => $category->todos()], 200);
    }
    
    // POST /categories
    // Create category
    public function create(Request $request, Response $response) {
        $this->logger->addInfo('POST /categories');
        $data = $request->getParsedBody();
        $user = $request->getAttribute('user');
        // The validate method returns the validator instance
        $validator = $this->validator->validate($request, ValidationRules::categoriesPost());
        if ($validator->isValid()) {
            // Input is valid, so let's do something...
            $category = $user->categories()->firstOrCreate([
                'name' => $data['name']
            ]);
            return $response->withJson([
                'success' => true,
                'id' => $category->id
            ], 200);
        } else {
            // Validation error
            return $response->withJson([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
        }
    }
    
    // PUT /categories/{id}
    // Updates category
    public function update(Request $request, Response $response, $args) {
        $this->logger->addInfo('PUT /categories/'.$args['id']);
        $data = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $errors = [];
        // validate inputs
        $validator = $this->validator->validate($request, ValidationRules::categoriesPut());
        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
        }
        // check category ID exists
        $category = $user->categories()->find($args['id']);
        if (!$errors && !$category) {
            $errors = ['Category not found: '.$args['id']];
        }
        // check for duplicate
        if (!$errors && isset($data['name']) && $user->categories()->where('name', $data['name'])->where('id', '!=', $category->id)->first()) {
            $errors = ['Category name already exists'];
        }
        // No errors? Update DB
        if (!$errors) {
            if (isset($data['name'])) {
                $category->name = $data['name'];
            }
            $category->save();
            return $response->withJson(['success' => true], 200);
        } else {
            // Errors found
            return $response->withJson([
                'success' => false,
                'errors' => $errors
            ], 400);
        }
    }
    
    // DELETE /categories/{id}
    // Delete a category
    public function delete(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $errors = [];
        // check category ID exists
        $category = $user->categories()->withTrashed()->find($args['id']);
        if (!$errors && !$category) {
            $errors = ['Category not found: '.$args['id']];
        }
        if (!$errors) {
            $deleted = (isset($data['force']) && !empty($data['force'])) ? $category->forceDelete() : $category->delete();
            return $response->withJson(['success' => true], 200);
        } else {
            // Errors found
            return $response->withJson([
                'success' => false,
                'errors' => $errors
            ], 400);
        }
    }
}