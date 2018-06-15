<?php
use App\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use App\Models\User as User;

class Helper {
    private $app;
    private $token;
    function __construct($app) {
        $this->app = $app;
    }
    // Simulates queries to our REST API using mock environment
    function apiTest($method, $endpoint, $token = false, $postData = []) {
        $envOptions = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $endpoint
        ];
        if ($postData) {
            $envOptions['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        }
        //Authorization: Bearer 
        $env = Environment::mock($envOptions);
        if ($token) {
            $request = $postData ? Request::createFromEnvironment($env)->withHeader('Authorization', 'Bearer '.$token)->withParsedBody($postData) : Request::createFromEnvironment($env)->withHeader('Authorization', 'Bearer '.$token);
        } else {
            $request = $postData ? Request::createFromEnvironment($env)->withParsedBody($postData) : Request::createFromEnvironment($env);
        }
        $this->app->getContainer()['request'] = $request;
        $response = $this->app->run(true);
        return [
            'code' => $response->getStatusCode(),
            'data' => json_decode($response->getBody(), true)
        ];
    }
    // Creates phpunit user, returns token
    function getAuthToken() {
        $user = User::where('username', 'phpunit')->first();
        if (!$user) {
            $user = User::create([
                'username' => 'phpunit',
                'password' => 'phpunit'
            ]);
        }
        $token = $user->tokenCreate();
        return $token['token'];
    }
}
?>