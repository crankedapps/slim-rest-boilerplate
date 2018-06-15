<?php
use App\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use App\Models\Category as Category;
require_once('Helper.php');

class CategoriesTest extends \PHPUnit\Framework\TestCase {
    
    protected $app;
    private $helper;
    private $token;
    
    public function setUp() {
        $this->app = (new App)->get();
        $this->helper = new Helper($this->app);
        $this->token = $this->helper->getAuthToken();
    }
    
    public function testCategoriesNoAuthGet() {
        $data = $this->helper->apiTest('get', '/categories');
        $this->assertSame($data['code'], 401);
    }
    
    public function testCategoriesGet() {
        $data = $this->helper->apiTest('get', '/categories', $this->token);
        $this->assertSame($data['code'], 200);
    }
    
    public function dataCategoriesInvalidPost() {
        return [
            ['PHPUnit #$W@@$ '.time()], // symbols
            ['a'], // too short
            ['abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz'] // too long
        ];
    }
    /**
     * @dataProvider dataCategoriesInvalidPost
     */
    public function testCategoriesInvalidPost($name) {
        $data = $this->helper->apiTest('post', '/categories', $this->token, [
            'name' => $name
        ]);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    public function testCategoriesPost() {
        $data = $this->helper->apiTest('post', '/categories', $this->token, [
            'name' => 'PHPUnit'
        ]);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
        return $data['data']['id'];
    }
    
    /**
     * @depends testCategoriesPost
     */
    public function testCategoriesUserRelationship($categoryId) {
        $category = Category::find($categoryId);
        $this->assertTrue(is_numeric($category->user->id));
    }
    
    public function testCategoriesPostDuplicate() {
        $data = $this->helper->apiTest('post', '/categories', $this->token, [
            'name' => 'PHPUnit2'
        ]);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
    
    public function dataCategoriesInvalidIdGet() {
        return [
            ['stringID'], // non-numeric
            [-10], // negative
            [9999] // invalid
        ];
    }
    /**
     * @dataProvider dataCategoriesInvalidIdGet
     */
    public function testCategoriesInvalidIdGet($categoryId) {
        $data = $this->helper->apiTest('get', '/categories/'.$categoryId, $this->token);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    /**
     * @depends testCategoriesPost
     */
    public function testCategoriesIdGet($categoryId) {
        $data = $this->helper->apiTest('get', '/categories/'.$categoryId, $this->token);
        $this->assertSame($data['code'], 200);
        $this->assertSame($data['data']['data']['id'], $categoryId);
    }
    
    /**
     * @depends testCategoriesPost
     */
    public function testCategoriesTodosGet($categoryId) {
        $data = $this->helper->apiTest('get', '/categories/'.$categoryId.'/todos', $this->token);
        $this->assertSame($data['code'], 200);
        $this->assertTrue(is_array($data['data']['data']));
    }
    
    public function dataCategoriesPutInvalid() {
        return [
            [null, 'PHPUnit #$W@@$ '.time()], // symbols
            [null, 'a'], // too short
            [null, 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz'], // too long
            [9999, 'testing'], // invalid category
            [null, 'PHPUnit2'] // duplicate name (created in testCategoriesPostDuplicate method above)
        ];
    }
    /**
     * @dataProvider dataCategoriesPutInvalid
     * @depends testCategoriesPost
     */
    public function testCategoriesPutInvalid($overwiteCategory, $name, $categoryId) {
        $categoryId = $overwiteCategory ? $overwiteCategory : $categoryId;
        $data = $this->helper->apiTest('put', '/categories/'.$categoryId, $this->token, ['name' => $name]);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    /**
     * @depends testCategoriesPost
     */
    public function testCategoriesPut($categoryId) {
        $data = $this->helper->apiTest('put', '/categories/'.$categoryId, $this->token, [
            'name' => 'PHPUnit U '.time()
        ]);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
    
    public function dataCategoriesDeleteInvalid() {
        return [
            ['IDasString'], // string
            [99999] // invalid
        ];
    }
    /**
     * @dataProvider dataCategoriesDeleteInvalid
     */
    public function testCategoriesDeleteInvalid($categoryId) {
        $data = $this->helper->apiTest('delete', '/categories/'.$categoryId, $this->token);
        $this->assertSame($data['code'], 400);
        $this->assertFalse($data['data']['success']);
    }
    
    /**
     * @depends testCategoriesPost
     */
    public function testCategoriesDeleteSoft($categoryId) {
        $data = $this->helper->apiTest('delete', '/categories/'.$categoryId, $this->token);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
    
    /**
     * @depends testCategoriesPost
     */
    public function testCategoriesDeleteForce($categoryId) {
        $data = $this->helper->apiTest('delete', '/categories/'.$categoryId, $this->token, ['force' => true]);
        $this->assertSame($data['code'], 200);
        $this->assertTrue($data['data']['success']);
    }
}