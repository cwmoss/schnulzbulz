<?php

declare(strict_types=1);

error_reporting(\E_ALL);
if (!defined('XORC_APP_BASEURL')) define('XORC_APP_BASEURL', '/');

use PHPUnit\Framework\TestCase;
use xorc\router;

final class RouterTest extends TestCase {
    public $ok;
    public $router;

    public function setUp(): void {
        $router = new router;
        $router->add('GET', '/', ['hello', 'index']);

        $router->add('GET', '/hello/{name}/{from}', ['hello', 'greet2']);
        $router->add('GET', '/hello/{name}', ['hello', 'greet']);
        $router->add('GET', '/AKTION', ['sale', 'index'], 'current_sale');
        $router->add('GET', '/admin/{controller}/{action}[/{id}]', [null, null, 'admin']);
        $router->add('GET', '/{controller}/{action}[/{id}]', null);
        $router->add('POST', '/{controller}/{action}[/{id}]', null);
        $this->router = $router;
    }

    public function testNamed(): void {
        $url = $this->router->url_for(':current_sale');
        $this->assertSame('/AKTION', $url);
        $url = $this->router->url_for(':current_sale', ['campaign' => '123']);
        $this->assertSame('/AKTION?campaign=123', $url);
    }

    public function testRegular(): void {
        $url = $this->router->url_for('hello/greet', ['name' => 'heiko']);
        $this->assertSame('/hello/heiko', $url);
        $url = $this->router->url_for('hello/greet2', ['name' => 'heiko', 'from' => 'munich']);
        $this->assertSame('/hello/heiko/munich', $url);
        $url = $this->router->url_for('hello/index', ['name' => 'heiko', 'from' => 'munich']);
        $this->assertSame('/?name=heiko&from=munich', $url);
    }
    public function testFallback(): void {
        $url = $this->router->url_for('user/profile', ['id' => '123']);
        $this->assertSame('/user/profile/123', $url);
        $url = $this->router->url_for('user/profiles', []);
        $this->assertSame('/user/profiles', $url);
    }

    public function testErrors(): void {
        $url = $this->router->url_for(':current_sales');
        $this->assertSame('route-unknown-name', $url);

        $router = new router;
        $router->add('GET', '/', ['hello', 'index']);
        $router->add('GET', '/hello/{name}/{from}', ['hello', 'greet2']);

        $url = $router->url_for('noway/here', ['campaign' => '123']);
        $this->assertSame('route-unmatched', $url);
    }

    public function testPrefix(): void {
        $url = $this->router->url_for('admin.user/edit', ['id' => '123']);
        $this->assertSame('/admin/user/edit/123', $url);
    }
}
