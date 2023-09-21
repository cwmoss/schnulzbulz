<?php

declare(strict_types=1);

error_reporting(\E_ALL);
if (!defined('XORC_APP_BASEURL')) define('XORC_APP_BASEURL', '/');


use PHPUnit\Framework\TestCase;
use xorc\router;
use myapp\controller\mypage;
use myapp\controller\another_page;


require_once(__DIR__ . '/_fix/mypage.php');
require_once(__DIR__ . '/_fix/mock_functions.php');

final class RoutableTest extends TestCase {

    public $ctrl;
    public $router;

    public function setUp(): void {
        $router = new router;
        $router->add('GET', '/', ['hello', 'index']);

        $router->add('GET', '/hello/{name}/{from}', ['hello', 'greet2']);
        $router->add('GET', '/hello/{name}', ['hello', 'greet']);
        $router->add('GET', '/AKTION', ['sale', 'index'], 'current_sale');
        $router->add('GET', '/{controller}/{action}[/{id}]', null);
        $router->add('POST', '/{controller}/{action}[/{id}]', null);
        $this->router = $router;
        $this->ctrl = new mypage($router);
        //dd($this->ctrl);
    }

    public function testBasic(): void {
        $url = $this->ctrl->url('imprint');
        $this->assertSame('/mypage/imprint', $url);
        $url = $this->ctrl->url('imprint', ['page' => 1]);
        $this->assertSame('/mypage/imprint?page=1', $url);

        $url = $this->ctrl->url('imprint', ['id' => 1]);
        $this->assertSame('/mypage/imprint/1', $url);

        $url = $this->ctrl->url('imprint/german', ['id' => 1]);
        $this->assertSame('/imprint/german/1', $url);

        $url = $this->ctrl->url('imprint', ['q' => 'find location']);
        $this->assertSame('/mypage/imprint?q=find+location', $url);

        $url = $this->ctrl->url('imprint', ['q' => 'cat="info"']);
        $this->assertSame('/mypage/imprint?q=cat%3D%22info%22', $url);

        $url = $this->ctrl->url('hello/index');
        $this->assertSame('/', $url);

        $ctrl = new another_page($this->router);
        $url = $ctrl->url('imprint');
        $this->assertSame('/another_page/imprint', $url);
    }

    public function testAlternateSyntax(): void {
        $url = $this->ctrl->url(['imprint']);
        $this->assertSame('/mypage/imprint', $url);

        $url = $this->ctrl->url(['imprint/german', ['id' => 1]]);
        $this->assertSame('/imprint/german/1', $url);
    }

    public function testRedirect(): void {
        xorc\Output::reset();
        $this->ctrl->redirect('imprint');
        $this->assertContains('Location: /mypage/imprint', xorc\Output::$headers);

        xorc\Output::reset();
        $this->ctrl->redirect('another/imprint', ['q' => 'cat="info"']);
        $this->assertContains('Location: /another/imprint?q=cat%3D%22info%22', xorc\Output::$headers);
    }

    public function testLink(): void {
        $link = $this->ctrl->link('imprint', 'Imprint', ['id' => 1]);
        $this->assertSame('<a href="&#x2F;mypage&#x2F;imprint&#x2F;1">Imprint</a>', $link);
        $link = $this->ctrl->link('imprint', 'Imprint', ['q' => 'cat="info"', 'order' => 'recent']);
        $this->assertSame('<a href="&#x2F;mypage&#x2F;imprint&#x3F;q&#x3D;cat&#x25;3D&#x25;22info&#x25;22&amp;order&#x3D;recent">Imprint</a>', $link);
    }
}
