<?php
use App\App;
use Slim\Http\Environment;
use Slim\Http\Request;
require_once('Helper.php');

class HandlerTest extends \PHPUnit\Framework\TestCase {
    
    protected $app;
    private $helper;
    private $token;
    
    public function setUp() {
        $this->app = (new App)->get();
        $this->helper = new Helper($this->app);
        $this->token = $this->helper->getAuthToken();
    }
    
    public function testHandlerCustom404() {
        $data = $this->helper->apiTest('get', '/bad/url/here', $this->token);
        $this->assertSame($data['code'], 404);
    }
}