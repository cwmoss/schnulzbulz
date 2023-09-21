<?php

function label_tag($label, $for = null, $opts = array()) {
   if ($for) $opts['for'] = $for;
   return sprintf('<label %s>%s</label>', opts_to_html($opts), trim($label));
}

function check_box_tag($name, $value, $checked = false, $opts = array()) {
   if ($checked) $opts['checked'] = "checked";
   $rl = @$opts['label'];
   $ll = @$opts['label_left'];
   unset($opts['label'], $opts['label_left']);
   $cb = sprintf(
      '<input type="checkbox" name="%s" value="%s" %s />',
      $name,
      $value,
      opts_to_html($opts)
   );
   if ($rl || $ll) return label_tag($ll . " " . $cb . " " . $rl, $opts['id'], array("class" => $opts['class']));
   return $cb;
}


function radio_button_tag($name, $value, $checked = false, $opts = array()) {
   if ($checked) $opts['checked'] = "checked";
   $rl = @$opts['label'];
   $ll = @$opts['label_left'];
   $erl = @$opts['label_right_ext'];
   $ell = @$opts['label_left_ext'];
   $lclass = $opts['label_class'];
   if (!$lclass) $lclass = $opts['class'];

   unset($opts['label'], $opts['label_left'], $opts['label_right_ext'], $opts['label_left_ext'], $opts['label_class']);
   $r = sprintf(
      '<input type="radio" name="%s" value="%s" %s />',
      $name,
      $value,
      opts_to_html($opts)
   );


   if ($rl || $ll) {
      return label_tag($ll . " " . $r . " " . $rl, $opts['id'], array("class" => $lclass));
   } elseif ($erl || $ell) {
      $id = $opts['id'] . '_' . $value;
      if ($ell) {
         return label_tag($ell, $opts['id'], array("class" => $lclass)) . $r;
      } else {
         return $r . label_tag($erl, $opts['id'], array("class" => $lclass));
      }
   }
   return $r;
}

function file_field_tag($name, $opts = array()) {
   return sprintf(
      '<input type="file" name="%s" %s />',
      $name,
      opts_to_html($opts)
   );
}

function form_tag($name, $url, $opts = array()) {
   $opts = array_merge(array("method" => "post"), $opts);
   if (!$url) $url = selfurl();
   elseif (is_array($url)) $url = url($url[0], $url[1]);
   else $url = url($url);
   $opts['action'] = $url;
   if (isset($opts['multipart'])) {
      unset($opts['multipart']);
      $opts["enctype"] = "multipart/form-data";
   }
   #return sprintf('<form name="%s" %s />',
   #     $name, opts_to_html($opts));

   return sprintf(
      '<form %s >',
      opts_to_html($opts)
   );
}

function end_form_tag($opts = array()) {
   return "</form>";
}

function hidden_field_tag($name, $value, $opts = array()) {
   return sprintf(
      '<input type="hidden" name="%s" value="%s" %s />',
      $name,
      htmlspecialchars($value ?: ""),
      opts_to_html($opts)
   );
}

function image_submit_tag($name, $src, $opts = array()) {
   return sprintf(
      '<input type="image" value="%s" name="%s" src="%s" %s />',
      $name,
      $name,
      image_path($src),
      opts_to_html($opts)
   );
}

function themed_image_submit_tag($name, $src, $opts = array()) {
   return sprintf(
      '<input type="image" value="%s" name="%s" src="%s" %s />',
      $name,
      $name,
      themed_asset($src),
      opts_to_html($opts)
   );
}

function password_field_tag($name, $value, $opts = array()) {
   return sprintf(
      '<input type="password" name="%s" value="%s" %s />',
      $name,
      htmlspecialchars($value),
      opts_to_html($opts)
   );
}

function select_box_tag($name, $value, $items, $opts = array()) {
   if ($opts['multiple']) {
      $opts['multiple'] = "multiple";
      $name .= "[]";
   }
   $optiontags = options_for_select($items, $value, $opts);
   $sopts = $opts;
   unset($sopts['nullentry'], $sopts['nohash'], $sopts['compare_strings']);
   return (sprintf(
      '<select name="%s"%s>' . "\n %s \n</select>",
      $name,
      opts_to_html($sopts),
      $optiontags
   )
   );
}

function submit_tag($name, $value = null, $opts = array()) {
   if (!$value) $value = $name;
   return sprintf(
      '<input type="submit" name="%s" value="%s" %s />',
      $name,
      htmlspecialchars($value),
      opts_to_html($opts)
   );
}

function button_tag($name, $value = null, $opts = array()) {
   if (!$value) $value = $name;
   return sprintf(
      '<input type="button" name="%s" value="%s" %s />',
      $name,
      htmlspecialchars($value),
      opts_to_html($opts)
   );
}

function text_area_tag($name, $value, $opts = array()) {
   if (isset($opts['size'])) {
      list($opts["cols"], $opts['rows']) = explode("x", $opts['size']);
      unset($opts['size']);
   }
   return sprintf(
      '<textarea name="%s" %s >%s</textarea>',
      $name,
      opts_to_html($opts),
      htmlspecialchars($value)
   );
}

function text_field_tag($name, $value, $opts = array()) {
   if (isset($opts['type'])) {
      $type = $opts['type'];
      unset($opts['type']);
   } else {
      $type = 'text';
   }
   if ($type != 'number' && $opts['max']) {
      $opts['maxlength'] = $opts['max'];
      unset($opts['max']);
   }
   return sprintf(
      '<input type="%s" name="%s" value="%s" %s />',
      $type,
      $name,
      htmlspecialchars($value ?: ""),
      opts_to_html($opts)
   );
}

function number_field_tag($name, $value, $opts = array()) {
   $opts['type'] = 'number';
   return text_field_tag($name, $value, $opts);
}

function email_field_tag($name, $value, $opts = array()) {
   $opts['type'] = 'email';
   return text_field_tag($name, $value, $opts);
}

function tel_field_tag($name, $value, $opts = array()) {
   $opts['type'] = 'tel';
   return text_field_tag($name, $value, $opts);
}

function options_for_select($items, $value = array(), $opts = array()) {
   $html = "";
   $opts['compare_strings'] = $opts['compare_strings'] ? true : false;
   if (!is_array($value)) $value = array($value);
   if (isset($opts['nullentry'])) $html .= sprintf('<option value="">%s</option>' . "\n", $opts['nullentry']);

   if (isset($opts['group'])) {
      foreach ($items as $group => $groupitems) {
         $html .= sprintf('<optgroup %s>', opts_to_html(array("label" => $group)));
         $html .= options_list($groupitems, $value, $opts);
         $html .= "</optgroup>";
      }
   } else {
      $html .= options_list($items, $value, $opts);
   }
   return $html;
}

function options_list($items, $value, $opts = array()) {
   $html = "";
   if (isset($opts['nohash'])) {
      foreach ($items as $v) {
         $hopts = array();
         if (is_array($v)) {
            $hopts['class'] = $v[1];
            $v = $v[0];
         }
         if (in_array($v, $value)) {
            $hopts['selected'] = 'selected';
         }
         $html .= sprintf('<option%s>%s</option>' . "\n", opts_to_html($hopts), $v);
      }
   } else {
      foreach ($items as $k => $v) {
         $hopts = array();
         if (is_array($v)) {
            $hopts['class'] = $v[1];
            $v = $v[0];
         }

         if ($opts['compare_strings']) $k = (string) $k;
         if (in_array($k, $value)) {
            //   if(isset($vkeys[$k])){   
            $hopts['selected'] = 'selected';
         }
         $hopts['value'] = (string)$k;
         $html .= sprintf('<option%s>%s</option>' . "\n", opts_to_html($hopts), $v);
      }
   }
   return $html;
}

function opts_to_html($opts) {
   $html = "";
   foreach ($opts as $k => $v) {
      # $html.=' '.$k.'=\''.trim($v).'\'';
      if (is_array($v)) dd($v);
      $v = trim($v ?: "");
      #if(!$v) continue;
      #log_error("opts-to-html");
      #log_error($v);
      if (!$v && !($v === 0 || $v == "0")) continue;
      #    $html.=' '.$k.'="'.$v.'"';
      $html .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
   }
   return $html;
}

function upload_file_to_hash($name) {
   $phpname = $_FILES[$name];
   if ($phpname['error'] != UPLOAD_ERR_OK) return false;
   if (!$phpname['tmp_name']) return false;

   $upload = array(
      'remote' => $phpname['name'],
      'type'   => $phpname['type'],
      'size'   => $phpname['size'],
      'tmp'    => $phpname['tmp_name']
   );
   return $upload;
}

function upload_to_hash($name) {
   $phpname = $_FILES[$name];

   $upload = array(
      'error' => $phpname['error'],
      'remote' => $phpname['name'],
      'type'   => $phpname['type'],
      'size'   => $phpname['size'],
      'tmp'    => $phpname['tmp_name'],
      'tmp_name'    => $phpname['tmp_name'],
   );
   return $upload;
}

function upload_to_hash_by_file($file, $name = null) {
   $err = is_readable($file) ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE;
   $remote = $name ? $name : basename($file);

   $upload = array(
      'error' => $err,
      'remote' => $remote,
      'type'   => mime_content_type($file),
      'size'   => filesize($file),
      'tmp'    => $file,
      'tmp_name' => $file,
   );
   return $upload;
}
