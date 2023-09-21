<?php

/**
  converts a yaml file to serialized php data
  need: source file
  optional: destination file

  if no destination file is given source file ending will be removed and replaced with ".db"
  ex: names.yaml ==> names.db
 */

$src = $cli->args[0] ?? null;
$dest = $cli->args[1] ?? null;

if (!$src) die("please provide source yaml file.\n");
if (!$dest) {
   $info = pathinfo($src);
   $dest = $info['dirname'] . "/" . $info['filename'] . ".db";
}

use Symfony\Component\Yaml\Yaml;

print "converting $src ==> $dest\n";

$data = parse_files($src);

file_put_contents($dest, serialize($data));

function parse_files($src) {

   $docs = explode('------', file_get_contents($src));

   $data = array();

   if (count($docs) > 1) {
      foreach ($docs as $doc) {
         // print "doc: $doc";
         if (!$doc) continue;
         list($name, $content) = explode("\n", $doc, 2);
         if (!trim($content)) {
            $content = $name;
            $name = '__sys';
         }
         $data[trim($name)] = parse($content);
         if ($data['__sys']['import'] ?? null) {
            $import = $data['__sys']['import'];
            $data = merge_docs(parse_files(dirname($src) . '/' . $import), $data);
         }
      }
   } else {
      $data = parse($docs[0]);
   }

   return $data;
}

function merge_docs($f1, $f2) {
   $merge = [];
   $docs = array_unique(array_merge(array_keys($f1), array_keys($f2)));
   foreach ($docs as $doc) {
      $d1 = $f1[$doc] ?: [];
      $d2 = $f2[$doc] ?: [];
      $merge[$doc] = array_merge($d1, $d2);
   }
   return $merge;
}

function parse($content) {
   $data = Yaml::parse($content);

   #print_r($data);

   if ($data['$$templates$$'] ?? null) {
      print "  \$\$templates\$\$ found.\n";
      $tpldata = array();
      $tpl = $data['$$templates$$'];
      $yaml = "";
      foreach ($tpl as $t) {
         print "  - evaluating template \"{$t['name']}\"\n";
         $y = Yaml::dump($t['tpl']);

         foreach (range(0, $t['iteration'] - 1) as $i) {
            $yi = $y;
            foreach ($t['vars'] as $v => $arr) {
               $yi = str_replace("<$v>", $arr[$i], $yi);
            }
            $yaml .= $yi;
         }
      }
      // print "    RESULT\n---\n".$yaml."\n---\n";
      unset($data['$$templates$$']);

      $datat = Yaml::parse($yaml);

      $data = array_merge($data, $datat);
   }

   return $data;
}
