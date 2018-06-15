<?php
use App\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use App\Models\Todo as Todo;
require_once('Helper.php');

class TodoTest extends \PHPUnit\Framework\TestCase {
    
    protected $app;
    private $helper;
    private $token;
    private $category;
    
    public function setUp() {
        $this->app = (new App)->get();
        $this->helper = new Helper($this->app);
        $this->token = $this->helper->getAuthToken();
        $this->category = $this->helper->apiTest('post', '/categories', $this->token, ['name' => 'PHPUnit'])['data']['id'];
    }
    
    public function testTodoGet() {
        $data = $this->helper->apiTest('get', '/todo', $this->token);
        $this->assertSame($data['code'], 200);
    }
    
    public function dataTodoInvalidPost() {
        return [
            [null, 'abc $#$@'], // symbols
            [null, 'a'], // too short
            [null, 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz'], // too long
            ['test', 'abc $#$@'], // invalid non-numeric category
            [99999, 'PHPUnit Test'], // invalid category
        ];
    }
    /**
     * @dataProvider dataTodoInvalidPost
     */
    public function testTodoInvalidPost($category, $name) {
        $category = $category ? $category : $this->category;
        $data = $this->helper->apiTest('post', '/todo', $this->token, ['category' => $category, 'name' => $name]);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    public function testTodoPost() {
        $data = $this->helper->apiTest('post', '/todo', $this->token, ['category' => $this->category, 'name' => 'PHPUnit Todo']);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
        return $data['data']['id'];
    }
    
    /**
     * @depends testTodoPost
     */
    public function testTodoDuplicatePost($todoId) {
        $data = $this->helper->apiTest('post', '/todo', $this->token, ['category' => $this->category, 'name' => 'PHPUnit Todo2']);
        $this->assertSame($data['code'], 200);
        return $data['data']['id'];
    }
    
    /**
     * @depends testTodoPost
     */
    public function testTodoIdGet($todoId) {
        $data = $this->helper->apiTest('get', '/todo/'.$todoId, $this->token);
        $this->assertSame($data['code'], 200);
        $this->assertSame($data['data']['data']['id'], $todoId);
    }
    
    public function dataTodoInvalidPut() {
        return [
            [null, 'Todo Item #$#@#$', null], // symbols
            [null, 'a', null], // too short
            [null, 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz', null], // too long
            [null, 'Testing', 9999], // invalid todo ID
            [null, 'PHPUnit Todo2', null], // duplicate (created in testTodoDuplicatePost method above)
            [9999, 'Testing', null] // invalid category
        ];
    }
    /**
     * @dataProvider dataTodoInvalidPut
     * @depends testTodoPost
     */
    public function testTodoInvalidPut($category, $name, $overwriteTodoId, $todoId) {
        $category = $category ? $category : $this->category;
        $todoId = $overwriteTodoId ? $overwriteTodoId : $todoId;
        $data = $this->helper->apiTest('put', '/todo/'.$todoId, $this->token, ['category' => $category, 'name' => $name]);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    /**
     * @depends testTodoPost
     */
    public function testTodoUserRelationship($todoId) {
        $todo = Todo::find($todoId);
        $this->assertTrue(is_numeric($todo->user->id));
    }
    
    /**
     * @depends testTodoPost
     */
    public function testTodoPut($todoId) {
        $data = $this->helper->apiTest('put', '/todo/'.$todoId, $this->token, ['name' => 'PHPUnit Todo U '.time()]);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
    
    public function dataTodoInvalidDelete() {
        return [
            ['testabc123'], // string
            [9999] // invalid
        ];
    }
    /**
     * @dataProvider dataTodoInvalidDelete
     */
    public function testTodoInvalidDelete($todoId) {
        $data = $this->helper->apiTest('delete', '/todo/'.$todoId, $this->token);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    /**
     * @depends testTodoPost
     */
    public function testTodoDelete($todoId) {
        $data = $this->helper->apiTest('delete', '/todo/'.$todoId, $this->token);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
    
    /**
     * @depends testTodoPost
     */
    public function testTodoDeleteForce($todoId) {
        $data = $this->helper->apiTest('delete', '/todo/'.$todoId, $this->token, ['force' => true]);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
    
    /**
     * @depends testTodoDuplicatePost
     */
    public function testTodoTeardown($todoDupId) {
        $data = $this->helper->apiTest('delete', '/categories/'.$this->category, $this->token, ['force' => true]);
        $data = $this->helper->apiTest('delete', '/todo/'.$todoDupId, $this->token, ['force' => true]);
        $this->assertSame($data['code'], 200);
    }
    
    public function tearDown() {
        
    }
}