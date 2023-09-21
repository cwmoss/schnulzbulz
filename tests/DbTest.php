<?php

declare(strict_types=1);

error_reporting(\E_ALL);


use PHPUnit\Framework\TestCase;
use xorc\db;

final class DbTest extends TestCase {

    public $db;

    public function setUp(): void {
        $this->db = new db\pdox("mysql:dbname=oda;host=127.0.0.1;port=3307", "root", "123456", new db\logger);
        $this->db->exec_sql_file(__DIR__ . '/_fix/schema_down.sql');
        $this->db->exec_sql_file(__DIR__ . '/_fix/schema.sql');
        // $con = new ar\connector($dbx);
    }

    public function tearDown(): void {
    }

    public function testBase(): void {
        $res = $this->db->select_first_cell("SELECT CONCAT('hello', 'world') as greet");
        $this->assertSame('helloworld', $res);
    }

    public function testMap(): void {
        $res = $this->db->insert('testarticles', ['title' => 'hello world', 'status' => 'ok']);
        $this->assertSame('1', $res);
        $res = $this->db->insert('testarticles', ['title' => 'h2', 'status' => 'ok']);
        $this->assertSame('2', $res);
        $res = $this->db->insert('testarticles', ['title' => 'h3', 'status' => 'published']);
        $this->assertSame('3', $res);
        $res = $this->db->select_all_map('SELECT id,title FROM testarticles');
        $this->assertSame('hello world', $res[1]);
        $this->assertSame('h3', $res[3]);

        $res = $this->db->select_all_map('SELECT status,id,title FROM testarticles t', [], true);
        $this->assertSame('h3', $res['published'][0]['title']);
        // dd($res);
    }
}
