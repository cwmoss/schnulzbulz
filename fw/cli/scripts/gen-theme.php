<?php

/**

    generiert/ verlinkt theme verzeichnisse

    args: name-of-the-theme
 */

$name = $cli->args[0] ?? null;
$base = XORC_APP_BASE;

if (!$name) die("you must provide a theme name.\n");


$dirs = array(
    "public/themes",
    "themes",
    "themes/$name",
    "themes/$name/public",
    "themes/$name/public/gfx",
    "themes/$name/public/js",
    "themes/$name/public/css",
    "themes/$name/view",

);

print ">>>> creating directories for theme $name\n";

foreach ($dirs as $d) {
    print(">> creating directory $d");
    $d = "$base/$d";
    if (is_dir($d)) print(" .. exists\n");
    else {
        mkdir($d, 0775);
        print " .. OK\n";
    }
}


$symlinks = array(
    // "$themes/themes/$name/public" => "$path/public/themes/$name",	
    "../../themes/$name/public" => "$base/public/themes/$name",
);

print ">>>> creating symlinks for theme assets\n";

foreach ($symlinks as $src => $dest) {
    print(">> creating link $src => $dest");
    if (file_exists($dest)) {
        print(" .. exists\n");
    } else {
        `ln -sf $src $dest`;
        print " .. OK\n";
    }
}
