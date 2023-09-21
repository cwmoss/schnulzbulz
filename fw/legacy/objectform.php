<?php

namespace xorc\legacy;

class objectform {
   var $obj = null;
   var $name = null;
   var $opts;

   function __construct($o, $oname, $opts = array()) {
      $this->obj = $o;
      $this->name = $oname;
      $this->opts = $opts + ['class' => null, 'autoid' => null, 'canonical_classnames' => null];
   }

   /*
   function form_for($o, $name, $opts=array()){

   }
   
   function fields_for(){
      
   }
*/
   function start($url, $opts = array()) {
      if (!$opts['class']) $opts['class'] = "xorcformdouble";
      #      if(is_array($url)){
      #         $url[1]['id']=$this->obj->id;
      #      }else{
      $url = array($url, $this->obj->id);
      #      }
      #      print_r($url);
      return form_tag($this->name, $url, $opts);
   }

   function finish() {
      return end_form_tag();
   }

   function check_box($name, $opts = array(), $cv = 1, $ucv = 0) {
      $fname = $this->fieldname($name);
      #      $id=$opts['id']?$opts['id']:$this->name."_".$name;
      #      $opts['id']=$id;
      $opts = $this->default_options($name, $opts);

      $checked = ($this->value($name) == $cv);
      if ($this->error_message_on($name)) $opts['class'] .= " inputerror";

      $html = hidden_field_tag($fname, $ucv);
      $html .= check_box_tag($fname, $cv, $checked, $opts);
      return $html;
   }

   function select_box($name, $items, $opts = array()) {
      $opts = $this->default_options($name, $opts);
      $fname = $this->fieldname($name);

      if ($this->error_message_on($name)) $opts['class'] .= " inputerror";
      if ($opts['req']) {
         $opts['class'] .= " req";
         unset($opts['req']);
      }
      return select_box_tag($fname, $this->value($name), $items, $opts);
   }

   function file_field($name, $opts = array()) {
      $opts = $this->default_options($name, $opts);
      $fname = $this->fieldname($name);
      //$val=$this->obj->$name;
      return file_field_tag($fname, $opts);
   }


   function hidden_field($name, $opts = array()) {
      $opts = $this->default_options($name, $opts);
      $fname = $this->fieldname($name);

      return hidden_field_tag($fname, $this->value($name), $opts);
   }

   function password_field($name, $opts = array()) {
      $opts = $this->default_options($name, $opts);
      $fname = $this->fieldname($name);

      if ($this->error_message_on($name)) $opts['class'] .= " inputerror";
      return password_field_tag($fname, $this->value($name), $opts);
   }

   function radio_button($name, $val, $opts = array()) {
      $opts = $this->default_options($name, $opts, $val);
      $fname = $this->fieldname($name);
      $checked = ($val == $this->value($name));
      if ($opts['compare_strings']) {
         unset($opts['compare_strings']);
         $checked = ((string)$val === $this->value($name));
      }
      if ($this->error_message_on($name)) $opts['class'] .= " inputerror";
      return radio_button_tag($fname, $val, $checked, $opts);
   }

   function text_area($name, $opts = array()) {
      $opts = $this->default_options($name, $opts);
      $fname = $this->fieldname($name);
      if ($this->error_message_on($name)) $opts['class'] .= " inputerror";
      return text_area_tag($fname, $this->value($name), $opts);
   }

   function text_field($name, $opts = array()) {
      $opts = $this->default_options($name, $opts);
      $fname = $this->fieldname($name);

      if ($this->error_on($name)) $opts['class'] .= " inputerror"; #.$opts['class'];
      if ($this->opts['canonical_classnames']) {
         $opts['class'] .= " text";
      }
      if ($opts['req']) {
         $opts['class'] .= " req required";
         unset($opts['req']);
      }
      return text_field_tag($fname, $this->value($name, $opts), $opts);
   }

   function number_field($name, $opts = array()) {
      $opts['type'] = 'number';
      return $this->text_field($name, $opts);
   }

   function email_field($name, $opts = array()) {
      $opts['type'] = 'email';
      return $this->text_field($name, $opts);
   }

   function tel_field($name, $opts = array()) {
      $opts['type'] = 'tel';
      return $this->text_field($name, $opts);
   }

   function value($name, $opts = []) {
      if (is_object($this->obj->$name)) return $this->obj->$name->to_form();
      $val = $this->obj->$name;
      if (isset($opts['modifier']) && is_callable($opts['modifier'])) {
         $val = $opts['modifier']($val);
      }
      return $val;
   }

   function error_on($name) {
      if (!$this->obj->errors) return;
      return $this->obj->errors->invalid($name);
   }

   function error_message_on($name, $opts = array()) {
      if (!$this->obj->errors) return;
      return $this->obj->errors->string_on($name);
   }

   function error_messages($opts = array()) {
      if (!count($this->obj->errors)) return;
      $html = '<ul class="all_errors_explained">';
      foreach ($this->obj->errors->all() as $e) {
         $html .= "<li>" . $e->msg . "</li>\n";
      }
      return $html . "</ul>\n";
   }

   function fieldname($name) {
      return $this->name . "[" . $name . "]";
   }

   function idname($name) {
      return $name;
   }

   function default_options($name, $opts = array(), $val = null) {
      $def = ['class' => '', 'modifier' => null, 'compare_strings' => null, 'req' => false, 'multiple' => null, 'max' => null];
      # achtung! opts kann auch wiederum arrays enthalten
      $nopts = array();
      foreach ($opts as $k => $v) {
         if (is_array($v)) $nopts = array_merge($nopts, $v);
         else $nopts[$k] = $v;
      }

      if ($this->opts['autoid']) {
         $def['id'] = $this->idname($name);
         if (!is_null($val)) $def['id'] .= "_$val";
      }
      return array_merge($def, $nopts);
   }

   function fieldname_dottydot($name) {
      $n = explode(".", $name);
      switch (sizeof($n)) {
         case 3:
            $name = $n[0] . "[" . $n[1] . "]" . "[]";
            break;
         case 2:
            $name = $n[0] . "[" . $n[1] . "]";
            break;
         default:;
      }
      return array($name, $n[1]);
   }

   /**
    * @var Xorc_Objectform_Captcha
    */
   private $captcha;

   /**
    * @param $options captcha-creation-options (once captcha is created, options are ignored)
    * @params $output return what ? (complete 'html' or 'image' or 'input')
    */
   public function captcha($options = array(), $output = 'html') {
      require_once("xorc/div/xorc_captcha.class.php");
      assert(!$options || is_array($options));
      if (!$this->captcha) $this->captcha = new Xorc_Captcha($options);
      return $this->captcha->$output();
   }

   # validatable_get_validator()->js_html_tag(null, 'create', ['prefix'=>'reg', 'vopts'=>['onsubmit'=>true]])

   function validations($ev = 'save', $opts = null) {
      if (!is_callable([$this->obj, 'validatable_get_validator'])) return;

      if (!is_array($opts)) {
         if (!is_bool($opts)) $opts = true;
         $opts = ['onsubmit' => $opts];
      }
      if (!isset($opts['onsubmit'])) $opts['onsubmit'] = true;
      #print_r($opts); die();
      return $this->obj->validatable_get_validator()->js_html_tag(null, $ev, ['prefix' => $this->name, 'vopts' => $opts]);
   }
}
