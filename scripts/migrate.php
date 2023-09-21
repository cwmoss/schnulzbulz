<?php

use xorc\db;

$db = $container->get(db\pdox::class);

$db->exec_sql_file(XORC_APP_BASE . '/migrations/sqlite/start.sql');
