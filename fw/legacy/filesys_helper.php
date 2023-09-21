<?php

function mkdirs($strPath, $mode=0755){
	if(is_dir($strPath)) return true;
	$pStrPath = dirname($strPath);
//	print("trying ~$strPath~");
	if(!mkdirs($pStrPath, $mode)) return false;
//	print("trying $strPath~");
	return mkdir($strPath, $mode);
}


//rp like, working with absolute/relative path & a little bit shorter :p

function unrealpath($path) {
   $out=array();
   foreach(explode('/', $path) as $i=>$fold){
      if ($fold=='' || $fold=='.') continue;
      if ($fold=='..' && $i>0 && end($out)!='..') array_pop($out);
      else $out[]= $fold;
   } 
   return ($path{0}=='/'?'/':'').join('/', $out);
}

function unrealpath_relative($start_dir, $final_dir, $dirsep = DIRECTORY_SEPARATOR){
       
   //Directory separator consistency
   $start_dir = str_replace('/',$dirsep,$start_dir);
   $final_dir = str_replace('/',$dirsep,$final_dir);
   $start_dir = str_replace('\\',$dirsep,$start_dir);
   $final_dir = str_replace('\\',$dirsep,$final_dir);

   //'Splode!
   $firstPathParts = explode($dirsep, $start_dir);
   $secondPathParts = explode($dirsep, $final_dir);

   //Get the number of parts that are the same.
   $sameCounter = 0;
   for($i = 0; $i < min( count($firstPathParts), count($secondPathParts) ); $i++) {
      if( strtolower($firstPathParts[$i]) !== strtolower($secondPathParts[$i]) ) {
         break;
      }
      $sameCounter++;
   }
   //If they do not share any common directories/roots, just return 2nd path.
   if( $sameCounter == 0 ) {
      return $final_dir;
   }
   //init newpath.
   $newPath = '';
   //Go up the directory structure count(firstpathparts)-sameCounter times (so, go up number of non-matching parts in the first path.)
   for($i = $sameCounter; $i < count($firstPathParts); $i++) {
      if( $i > $sameCounter ) {
         $newPath .= $dirsep;
      }
      $newPath .= "..";
   }
   //if we did not have to go up at all, we're still in start_dir.
   if( strlen($newPath) == 0 ) {
      $newPath = ".";
   }
   //now we go down as much as needed to get to final_dir.
   for($i = $sameCounter; $i < count($secondPathParts); $i++) {
      $newPath .= $dirsep;
      $newPath .= $secondPathParts[$i];
   }
   //
   return $newPath;
}




function xorc_max_upload_size(){
   $max_upload_size = min(humsize_to_bytes(ini_get('post_max_size')), humsize_to_bytes(ini_get('upload_max_filesize')));
   return (($max_upload_size/(1024*1024))."MB");
}       

function xorc_mime_type($file){
   if(!file_exists($file) || !(is_file($file) && is_readable($file)))
      return false;
   $file = escapeshellarg($file);
   $t=`file -bi $file`;
   if(!$t) $t="application/ocet-stream";
   $t = explode(';', $t);
   return $t[0];
}       

function humsize_to_bytes($v){
   $l = substr($v, -1);
   $ret = substr($v, 0, -1);
   switch(strtoupper($l)){
   case 'P':
     $ret *= 1024;
   case 'T':
     $ret *= 1024;
   case 'G':
     $ret *= 1024;
   case 'M':
     $ret *= 1024;
   case 'K':
     $ret *= 1024;
     break;
   }
   return $ret;
}
   
function humsize_from_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 0).$units[$i];
}
   
   


?>