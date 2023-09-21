<?php

use app\model\comment;
use xorc\db;

$q = " WITH RECURSIVE dates(x) AS ( 
    SELECT '2015-01-01' 
        UNION ALL 
    SELECT DATE(x, '+1 MONTHS') FROM dates WHERE x<'2016-01-01' 
) 
SELECT * FROM dates";

$q2 = " WITH RECURSIVE dates(x) AS ( 
    SELECT ?
        UNION ALL 
    SELECT DATE(x, '+1 MONTHS') FROM dates WHERE x<? 
) 
SELECT * FROM dates";


$db = $container->get(db\pdox::class);

$res = $db->queryx($q2, ['2015-01-01', '2016-01-01']);
print_r(iterator_to_array($res));


/*

with top as (select * from comments where parent_id is null order by bumped_at desc limit 2) select * from top UNION ALL select c.* from comments c join top where c.parent_id = top.id;


select r.* from comments c join comments r on r.parent_id = c.id where c.id in(select id from comments where parent_id is null);

select c.* 
from comments c 
LEFT JOIN(
    select r.*, 
    ROW_NUMBER() OVER (
        PARTITION BY parent_id
        ) as row_num 
    FROM comments r
   
) as r2 ON r2.parent_id = c.id WHERE row_num < 4;

select r2.* 
from comments c 
LEFT JOIN(
    select r.*, 
    ROW_NUMBER() OVER (
        PARTITION BY parent_id
        ) as row_num 
    FROM comments r
   
) as r2 ON r2.parent_id = c.id WHERE row_num < 4;

-- bad!!!
select c.* 
from comments c 
LEFT JOIN(
    select r.*, 
    ROW_NUMBER() OVER (
        PARTITION BY parent_id
        ) as row_num 
    FROM comments r
    WHERE row_num = 1 
) as r ON r.parent_id = c.id;

SELECT *
FROM author
LEFT JOIN (
    SELECT
        *,
        ROW_NUMBER() OVER (
            PARTITION BY author_id
        ) as row_num
    FROM article
    WHERE row_num = 1
) as article ON article.author_id = author.id;

WITH RECURSIVE dates(x) AS ( SELECT '2015-01-01'  UNION ALL SELECT DATE(x, '+1 MONTHS') FROM dates WHERE x<'2016-01-01')
SELECT * FROM dates

*/