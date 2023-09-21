# Active Record

## Example Usage

```sql
-- blog-schema.sql sqlite
CREATE TABLE posts (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    slug TEXT,
    status TEXT DEFAULT 'draft',
    published_at datetime DEFAULT NULL,
    --    modified_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    created_at datetime DEFAULT CURRENT_TIMESTAMP
);
```

```php
# src/blog.php
require __DIR__ . '/vendor/autoload.php';
use xorc\db\pdox;
use xorc\ar\connector;
use xorc\ar\base;

// create database via schema file
$db = new pdox('sqlite:test.db');
$db->exec_sql_file(__DIR__ . '/blog-schema.sql');

// create a connector for active record classes
$con = new connector($db);

class post extends base {
    static function define_schema() {
        return ['table' => 'posts'];
    }
}

$post = new post;
$post->title = 'Hello World!';
$post->save();

print $post . "\n";

// => {"id":1,"title":"Hello World!","slug":null,"status":"draft","published_at":null,"created_at":"2023-09-14 13:21:45"}

print post::count()."\n";
// => 1

$post->slug = 'hello-world';
$post->save();

print $post . "\n";
// {"id":1,"title":"Hello World!","slug":"hello-world","status":"draft","published_at":null,"created_at":"2023-09-14 13:25:07"}

$post = new post;
$post->title = 'Some News For you';
$post->save();

$post = post::find(2);
print $post . "\n";
// => {"id":2,"title":"Some News For you","slug":null,"status":"draft","published_at":null,"modified_at":null,"created_at":"2023-09-14 13:38:25"}
```
