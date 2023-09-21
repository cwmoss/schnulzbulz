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
        $this->assertSame('WHERE id = ?', (string) $q);

        $q = (new querybuilder())->where('id', 1, '=');
        $this->assertSame('WHERE id = ?', (string) $q);

        $q = (new querybuilder())->where(['id' => 1]);
        $this->assertSame('WHERE id = ?', (string) $q);

        $q = (new querybuilder())->where(['id' => [1, '=']]);
        $this->assertSame('WHERE id = ?', (string) $q);

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
        $this->assertSame('WHERE id = ? AND name IS NOT NULL', (string) $q);

        $q = (new querybuilder())->where(['id' => 1, 'name' => OP::notnull]);
        $this->assertSame('WHERE id = ? AND name IS NOT NULL', (string) $q);

        $q = (new querybuilder())->where(['name' => ['harry', OP::regexp_like]]);
        $this->assertSame('WHERE REGEXP_LIKE(name, ?)', (string) $q);

        $q = (new querybuilder())->where(['name' => ['harry', 'REGEXP_LIKE']]);
        $this->assertSame('WHERE REGEXP_LIKE(name, ?)', (string) $q);

        $q = (new querybuilder())->where(['id' => 1, 'name' => OP::notnull, 'lang' => [['de', 'fr'], 'IN']]);
        $this->assertSame('WHERE id = ? AND name IS NOT NULL AND lang IN (?, ?)', (string) $q);

        $q = (new querybuilder())->where(['id' => 1, 'name' => OP::notnull, 'lang' => [['de', 'fr'], 'IN']]);
        $this->assertSame('WHERE id = ? AND name IS NOT NULL AND lang IN (?, ?)', (string) $q);

        $q = (new querybuilder())->where(['id' => 1, 'name' => OP::notnull])->and('lang', ['de', 'fr'], OP::in);
        $this->assertSame('WHERE id = ? AND name IS NOT NULL AND lang IN (?, ?)', (string) $q);

        $q = (new querybuilder())->where(['id' => 1, 'year' => [[1989, 2006], 'BETWEEN']]);
        $this->assertSame('WHERE id = ? AND year BETWEEN ? AND ?', (string) $q);
        $q = (new querybuilder())->where(['id' => 1])->and('year', [1989, 2006], 'BETWEEN');
        $this->assertSame('WHERE id = ? AND year BETWEEN ? AND ?', (string) $q);

        $q = (new querybuilder())->where('year', [1989, 2006], 'BETWEEN')->and(['id' => 1]);
        $this->assertSame('WHERE year BETWEEN ? AND ? AND id = ?', (string) $q);
    }

    public function testLimit(): void {
        $q = (new querybuilder('contacts'))->where(['id' => 1, 'name' => OP::notnull])->limit(3);
        $this->assertSame('SELECT * FROM contacts WHERE id = ? AND name IS NOT NULL LIMIT 3', (string) $q);
    }

    public function testOrder(): void {
        $q = (new querybuilder('contacts'))->where(['id' => 1, 'name' => OP::notnull])->order('name');
        $this->assertSame('SELECT * FROM contacts WHERE id = ? AND name IS NOT NULL ORDER BY name', (string) $q);

        $q = (new querybuilder('contacts'))->where(['id' => 1, 'name' => OP::notnull])->order('name DESC');
        $this->assertSame('SELECT * FROM contacts WHERE id = ? AND name IS NOT NULL ORDER BY name DESC', (string) $q);

        $q = (new querybuilder('contacts'))->where(['id' => 1, 'name' => OP::notnull])->limit(3)->order('name');
        $this->assertSame('SELECT * FROM contacts WHERE id = ? AND name IS NOT NULL ORDER BY name LIMIT 3', (string) $q);
    }

    public function testCount(): void {
        $q = (new querybuilder('contacts'))->where(['name' => ['h%', 'LIKE']])->limit(10, 3);
        $this->assertSame('SELECT * FROM contacts WHERE name LIKE ? LIMIT 10 OFFSET 20', (string) $q);
        $q->count();
        $this->assertSame('SELECT COUNT(*) FROM contacts WHERE name LIKE ?', (string) $q);
    }

    public function testUpdate(): void {
        $q = (new querybuilder('contacts'))->update()->set(['name' => 'harry']);
        $this->assertSame('UPDATE contacts SET name = ?', (string) $q);

        $q = (new querybuilder('contacts'))->update(['name' => 'harry']);
        $this->assertSame('UPDATE contacts SET name = ?', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set('name', 'harry');
        $this->assertSame('UPDATE contacts SET name = ?', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set(['name' => 'harry', 'email' => 'h@rr.de']);
        $this->assertSame('UPDATE contacts SET name = ?, email = ?', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set('name', 'harry')->set('email', 'h@rr.de');
        $this->assertSame('UPDATE contacts SET name = ?, email = ?', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set('name', 'harry')->set('email', 'h@rr.de')->where('name', 'harrie');
        $this->assertSame('UPDATE contacts SET name = ?, email = ? WHERE name = ?', (string) $q);

        $q = (new querybuilder('contacts'))->update()->set('name', 'harry')->set('email', 'h@rr.de')->where(['id' => 2]);
        $this->assertSame('UPDATE contacts SET name = ?, email = ? WHERE id = ?', (string) $q);
    }

    public function testInsert(): void {
        $q = (new querybuilder('contacts'))->insert()->value(['name' => 'harry']);
        $this->assertSame('INSERT INTO contacts (?) VALUES (?)', (string) $q);

        $q = (new querybuilder('contacts'))->insert()->value(['name' => 'harry']);
        $this->assertSame('INSERT INTO contacts (?) VALUES (?)', (string) $q);

        $q = (new querybuilder('contacts'))->insert()->value(['name' => 'harry', 'email' => 'h@rr.de']);
        $this->assertSame('INSERT INTO contacts (?, ?) VALUES (?, ?)', (string) $q);

        $q = (new querybuilder('contacts'))->insert()->value('name', 'harry')->value('email', 'h@rr.de');
        $this->assertSame('INSERT INTO contacts (?, ?) VALUES (?, ?)', (string) $q);
    }

    public function testDelete(): void {
        $q = (new querybuilder('contacts'))->delete()->build();
        $this->assertSame('DELETE FROM contacts', $q[0]);
        $this->assertSame([], $q[1]);

        $q = (new querybuilder('contacts'))->delete()->where(['name' => 'harry'])->build();
        $this->assertSame('DELETE FROM contacts WHERE name = ?', $q[0]);
        $this->assertSame(['harry'], $q[1]);

        $q = (new querybuilder('contacts'))->delete()->where('name', ['harry', 'larry'], OP::in)->build();
        $this->assertSame('DELETE FROM contacts WHERE name IN (?, ?)', $q[0]);
        $this->assertSame(['harry', 'larry'], $q[1]);
    }

    public function testParams(): void {
        $q = (new querybuilder('contacts'))->update()->set(['name' => 'harry']);
        $this->assertSame(['harry'], $q->build()[1]);

        $q = (new querybuilder('contacts'))->update()->set(['name' => 'harry'])->where(['id' => 1]);
        $this->assertSame('UPDATE contacts SET name = ? WHERE id = ?', $q->build()[0]);
        $this->assertSame(['harry', 1], $q->build()[1]);

        $q = (new querybuilder('contacts'))->update()->where(['id' => 1])->set(['name' => 'harry']);
        $this->assertSame('UPDATE contacts SET name = ? WHERE id = ?', $q->build()[0]);
        $this->assertSame(['harry', 1], $q->build()[1]);

        $q = (new querybuilder('contacts'))->where(['id' => 1])->set(['name' => 'harry'])->update();
        $this->assertSame('UPDATE contacts SET name = ? WHERE id = ?', $q->build()[0]);
        $this->assertSame(['harry', 1], $q->build()[1]);

        $q = (new querybuilder())->where(['id' => 1, 'name' => OP::notnull])->and('lang', ['de', 'fr'], OP::in)->build();
        $this->assertSame('WHERE id = ? AND name IS NOT NULL AND lang IN (?, ?)', $q[0]);
        $this->assertSame([1, 'de', 'fr'], $q[1]);

        $q = (new querybuilder())->where('lang', ['de', 'fr'], OP::in)->and(['id' => 1, 'name' => OP::notnull])->build();
        $this->assertSame('WHERE lang IN (?, ?) AND id = ? AND name IS NOT NULL', $q[0]);
        $this->assertSame(['de', 'fr', 1], $q[1]);

        $q = (new querybuilder('contacts'))->insert()->value(['name' => 'harry', 'email' => 'h@rr.de']);
        // dd($q);
        $this->assertSame('INSERT INTO contacts (?, ?) VALUES (?, ?)', $q->build()[0]);
        $this->assertSame(['name', 'email', 'harry', 'h@rr.de'], $q->build()[1]);
    }
}
