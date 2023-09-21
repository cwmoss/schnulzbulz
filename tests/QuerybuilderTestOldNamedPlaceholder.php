<?php

declare(strict_types=1);

error_reporting(\E_ALL);


use PHPUnit\Framework\TestCase;
use xorc\db;
use xorc\ar\querybuilder;
use xorc\ar\operator as OP;

use function DI\string;

final class QuerybuilderTest extends TestCase {


    public function setUp(): void {
    }

    public function testWhere(): void {
        $q = (new querybuilder())->where('id', 1);
        #dd($q);
        $this->assertSame('WHERE id = :id', (string) $q);

        $q = (new querybuilder())->where('id', 1, '=');
        $this->assertSame('WHERE id = :id', (string) $q);

        $q = (new querybuilder())->where(['id' => 1]);
        $this->assertSame('WHERE id = :id', (string) $q);

        $q = (new querybuilder())->where(['id' => [1, '=']]);
        $this->assertSame('WHERE id = :id', (string) $q);

        $q = (new querybuilder())->where(['id' => [null, OP::notnull]]);
        #dd($q);
        $this->assertSame('WHERE id IS NOT NULL', (string) $q);

        $q = (new querybuilder())->where(['id' => OP::notnull]);
        #dd($q);
        $this->assertSame('WHERE id IS NOT NULL', (string) $q);

        $q = (new querybuilder())->where('id', OP::notnull);
        #dd($q);
        $this->assertSame('WHERE id IS NOT NULL', (string) $q);

        $q = (new querybuilder())->where(['id' => 1, 'name IS NOT NULL']);
        // dd($q);
        $this->assertSame('WHERE id = :id AND name IS NOT NULL', (string) $q);

        $q = (new querybuilder())->where(['id' => 1, 'name' => OP::notnull]);
        //dd($q);
        //dbg_flush('uu');
        // die();
        $this->assertSame('WHERE id = :id AND name IS NOT NULL', (string) $q);

        $q = (new querybuilder())->where(['name' => ['harry', OP::regexp_like]]);
        $this->assertSame('WHERE REGEXP_LIKE(name, :name)', (string) $q);

        $q = (new querybuilder())->where(['name' => ['harry', 'REGEXP_LIKE']]);
        $this->assertSame('WHERE REGEXP_LIKE(name, :name)', (string) $q);
    }

    public function testUpdate(): void {
        $q = (new querybuilder('contacts'))->update()->set(['name' => 'harry']);
        $this->assertSame('UPDATE contacts SET name = :name', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set('name', 'harry');
        $this->assertSame('UPDATE contacts SET name = :name', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set(['name' => 'harry', 'email' => 'h@rr.de']);
        $this->assertSame('UPDATE contacts SET name = :name, email = :email', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set('name', 'harry')->set('email', 'h@rr.de');
        $this->assertSame('UPDATE contacts SET name = :name, email = :email', (string) $q);
    }
}
