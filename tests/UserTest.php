<?php
use App\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use App\Models\User as User;
require_once('Helper.php');

class AuthTest extends \PHPUnit\Framework\TestCase {
    
    protected $app;
    private $helper;
    
    public function setUp() {
        $this->app = (new App)->get();
        $this->helper = new Helper($this->app);
        // delete user if exists
        if ($user = User::where('username', 'phpunit')->first()) {
            $user->forceDelete();
        }
    }
    
    public function dataUsersInvalidPost() {
        return [
            ['phpunit$$%@', 'Testing123'], // user has symbols
            ['a', 'Testing123'], // user too short
            ['phpunittt', 'a'], // pass too short
            ['abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz', 'testtest'], // user too long
            ['phpunittt', 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz'], // pass too long
        ];
    }
    /**
     * @dataProvider dataUsersInvalidPost
     */
    public function testUsersInvalidPost($username, $password) {
        $data = $this->helper->apiTest('post', '/users', false, ['username' => $username, 'password' => $password]);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    public function testUsersPost() {
        $data = $this->helper->apiTest('post', '/users', false, ['username' => 'phpunit', 'password' => 'phpunit']);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
        return $data['data']['id'];
    }
    
    /**
     * @depends testUsersPost
     */
    public function testUsersDuplicatePost($userId) {
        $this->helper->getAuthToken(); // make sure phpunit user created
        $data = $this->helper->apiTest('post', '/users', false, ['username' => 'phpunit', 'password' => 'phpunit']);
        $this->assertSame($data['code'], 400);
    }
    
    public function dataUsersLoginInvalidPost() {
        return [
            ['phpunit$$%@', 'Testing123'], // user has symbols
            ['a', 'Testing123'], // user too short
            ['phpunittt', 'a'], // pass too short
            ['abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz', 'testtest'], // user too long
            ['phpunit', 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz'], // pass too long
            ['phpunitnotexist', 'Testing123'], // username doesn't exist
            ['phpunit', 'InvalidTesting123'] // user valid, invalid password
        ];
    }
    /**
     * @dataProvider dataUsersLoginInvalidPost
     */
    public function testUsersLoginInvalidPost($username, $password) {
        $this->helper->getAuthToken(); // make sure phpunit user created
        $data = $this->helper->apiTest('post', '/users/login', false, ['username' => $username, 'password' => $password]);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    public function testUsersLoginPost() {
        $this->helper->getAuthToken(); // make sure phpunit user created
        $data = $this->helper->apiTest('post', '/users/login', false, ['username' => 'phpunit', 'password' => 'phpunit']);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
    
    public function tearDown() {
        
    }
}