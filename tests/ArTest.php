<?php

declare(strict_types=1);

error_reporting(\E_ALL);


use PHPUnit\Framework\TestCase;
use testapp\model\article;
use testapp\model\feature;
use testapp\model\car;
use xorc\db;
use xorc\ar;
use xorc\ar\operator as OP;

require_once(__DIR__ . '/_fix/ar_classes.php');

final class ArTest extends TestCase {

    public $db;

    public function _reset() {
        ar\reflection::reset();
        ar\connector::reset();

        $this->db = new db\pdox("mysql:dbname=oda;host=127.0.0.1;port=3307", "root", "123456", new db\logger);
        // $this->db = new db\pdox('sqlite:test.db', null, null, new db\logger);
        $con = new ar\connector($this->db);
        $this->db->exec_sql_file(__DIR__ . '/_fix/schema_down.sql');
        $this->db->exec_sql_file(__DIR__ . '/_fix/schema.sql');
        // $this->db->exec_sql_file(__DIR__ . '/_fix/schema-sqlite.sql');
        return $con;
    }

    public function testInit(): void {
        $con = $this->_reset();
        $this->assertSame('_db', $con->name);
    }

    /**
     * @depends testInit
     */
    public function testBase(): void {
        $a = new article;
        $a->title = 'hello world';
        $this->assertSame('hello world', $a->title);
        $this->assertSame(null, $a->id);
        $a->save();
        $this->assertSame(1, $a->id);

        # dd($a);
        // $a->save();
    }

    /**
     * @depends testInit
     */
    public function testDefault(): void {
        $a = new article;
        $a->title = 'hello world';
        $a->save();
        $this->assertSame('draft', $a->status);
        # dd($a);
        // $a->save();
    }

    /**
     * @depends testInit
     */
    public function testNoSchemainfo(): void {
        $a = new car;
        $a->name = 'VW Golf';
        $a->save();
        $this->assertSame('in-stock', $a->status);
        # dd($a);
        // $a->save();
    }

    /**
     * @depends testInit
     */
    public function testInsert(): array {
        $this->_reset();
        $test = array_map(function ($title) {
            $a = new article;
            $a->title = $title;
            $a->save();
            return $a;
        }, ['a1', 'a2', 'a3', 'a4']);

        article::new(['title' => 'b1'])->save();
        article::new(['title' => 'b2'])->save();
        $b3 = article::new(['title' => 'b3']);
        $this->assertSame('b3', $b3->title);

        $this->assertSame('draft', $test[0]->status);
        $this->assertSame('a1', $test[0]->title);
        $this->assertSame('draft', $test[3]->status);
        $this->assertSame('a4', $test[3]->title);
        $this->assertSame(article::count(), 6);
        return $test;
    }

    /**
     * @depends testInsert
     */
    public function testUpdate(array $test): void {
        $this->assertSame('draft', $test[0]->status);
        $this->assertSame(article::count(), 6);
        $a = article::find(3);
        $a->title .= ' is good';
        $a->save();
        $a = article::find(3);
        $this->assertSame('a3 is good', $a->title);
    }

    /**
     * @depends testInsert
     */
    public function testFindAll(array $test): void {
        $this->assertSame(article::count(), 6);
        $q = article::new_query_builder()->where('status', 'draft')->and('id', 3, OP::lt);
        $found = article::find_all($q);
        $this->assertSame(2, count(iterator_to_array($found)));

        $q = article::new_query_builder()->where('title', 'b%', 'LIKE');
        $found = article::find_all($q);
        $this->assertSame(2, count(iterator_to_array($found)));

        $q = article::new_query_builder()->where('title', 'b%', 'LIKE')->order('title DESC');
        $found = iterator_to_array(article::find_all($q));
        $this->assertSame(2, count($found));
        $this->assertSame('b2', $found[0]->title);
    }

    /**
     * @depends testInsert
     */
    public function testDelete(array $test): void {
        $this->assertSame(article::count(), 6);
        $a = article::find(2)->destroy();
        $this->assertSame(article::count(), 5);
        article::delete_all(['id' => [[1, 3], 'IN']]);
        $this->assertSame(article::count(), 3);
        article::delete_all(['title' => ['a%', 'LIKE']]);
        $this->assertSame(article::count(), 2);

        $q = article::new_query_builder()->where('title', 'b%', OP::like)->order('title DESC');
        $res = [...article::find_all($q)];
        $this->assertSame('b2', $res[0]->title);
        $this->assertSame('b1', $res[1]->title);
    }

    public function testDeleteAll(): void {
        $this->_reset();
        $test = array_map(
            fn ($title) => article::new(['title' => $title])->save(),
            ['a1', 'a2', 'a3', 'a4', 'b1', 'b2', 'c1']
        );
        $this->assertSame(article::count(), 7);
        $aff = article::delete_all(['1=1']);

        $this->assertSame(article::count(), 0);
        // $this->assertSame($aff, 7);

        $test = array_map(
            fn ($title) => article::new(['title' => $title])->save(),
            ['a1', 'a2', 'a3', 'a4', 'b1', 'b2', 'c1']
        );
        $this->assertSame(article::count(), 7);
        article::delete_all();
        $this->assertSame(article::count(), 0);
    }

    public function testDestroyAll(): void {
        $this->_reset();
        $test = array_map(
            fn ($title) => article::new(['title' => $title])->save(),
            ['a1', 'a2', 'a3', 'a4', 'b1', 'b2', 'c1']
        );
        $this->assertSame(article::count(), 7);
        $aff = article::destroy_all(['title' => 'b1']);
        $this->assertSame(article::count(), 6);
        $this->assertSame($aff, 1);
        article::destroy_all();
        $this->assertSame(article::count(), 0);
    }

    public function testUpdateAll(): void {
        $this->_reset();
        $test = array_map(
            fn ($title) => article::new(['title' => $title])->save(),
            ['a1', 'a2', 'a3', 'a4', 'b1', 'b2', 'c1']
        );
        $this->assertSame(article::count(), 7);
        $a = article::update_all(['title' => 'b1']);
        $b1 = article::find_all(['title' => 'b1']);
        $this->assertSame(count([...$b1]), 7);
        article::update_all(['title' => 'bx'], ['id' => 1]);
        $b1 = article::find_all(['title' => 'b1']);
        $this->assertSame(count([...$b1]), 6);
        $q = article::new_query_builder()->where('id', 2)->set('title', 'by');
        article::update_all($q);
        $b1 = article::find_all(['title' => 'b1']);
        $this->assertSame(count([...$b1]), 5);

        $b1 = article::find_all(['title' => 'by']);
        $this->assertSame(count([...$b1]), 1);
    }

    public function xxxtestTableprefix(): void {
        $con = new ar\connector($this->db, null, ['prefix' => 'test']);
        $this->assertSame('test_cars', car::table());
    }

    public function testPagination(): void {
        $this->_reset();
        $test = array_map(
            fn ($title) => article::new(['title' => $title])->save(),
            ['a1', 'a2', 'a3', 'a4', 'b1', 'b2', 'b3', 'c1', 'c2']
        );
        $q = article::new_query_builder()->where('title LIKE ? OR title LIKE ?', ['b%', 'a%'], OP::fragment)->order('title ASC')->limit(2, 2);
        // dd($q->build());
        $res = article::find_all($q);
        $res = [...$res];
        $this->assertSame(count($res), 2);
        $this->assertSame($res[0]->title, 'a3');
        $pager = article::get_pager($q);
        $this->assertSame($pager->totalpages, 4);

        $q->limit(2, 3);
        $res = article::find_all($q);
        $res = [...$res];
        $this->assertSame(count($res), 2);
        $this->assertSame($res[0]->title, 'b1');
        $pager = article::get_pager($q);
        $this->assertSame($pager->totalpages, 4);
        $this->assertSame($pager->currentpage, 3);
    }
}

/*

$db->exec("CREATE TABLE IF NOT EXISTS `articles` (
    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` text NOT NULL,
    `status` varchar(16) NOT NULL
  )");

CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `status` varchar(16) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/