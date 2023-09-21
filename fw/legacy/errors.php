<?php

namespace xorc\legacy;

class errors implements \Countable {
   private $e = array();
   public $cols = array();
   private $base = array();

   function add($e, $msg = null) {
      # var_dump($e);
      if (is_null($msg) && !($e instanceof error)) {
         return $this->add_to_base($e);
      }
      if (!is_null($msg)) {
         $e = new error($e, $msg);
      }
      $this->e[] = $e;
      # print "ADDING MSG ".$e->msg."[";var_dump($e);print "]\n";
      if (!isset($this->cols[$e->col])) $this->cols[$e->col] = array();
      $this->cols[$e->col][] = $e;
   }

   function add_to_base($msg) {
      $this->base[] = new error("", $msg);
   }

   function on($col) {
      $eL = array();
      if (isset($this->cols[$col])) {
         foreach ($this->cols[$col] as $e) {
            $eL[] = $e->msg;
         }
         return $eL;
      }
      return null;
   }

   /*
		alle fehler, die sich auf eine bestimmte spalte beziehen löschen
	*/
   function clear_on($col) {
      if ($this->cols[$col]) {
         unset($this->cols[$col]);
         $this->e = array_filter($this->e, function ($err) use ($col) {
            return $err->col != $col;
         });
         return true;
      }
      // es gibt nichts zu löschen
      return false;
   }

   function string_on($col, $sep = "<br>\n") {
      if ($msg = $this->on($col)) {
         #   log_error("XORCERR: on $col:");
         #   log_error($msg);
         return join($sep, array_filter($msg));
      }
      return false;
   }

   function first_on($col) {
      if (isset($this->cols[$col])) return $this->cols[$col][0]->msg;
      return null;
   }

   function on_base() {
      return $this->base;
   }

   function first_on_base() {
      if (isset($this->base[0])) return $this->base[0]->msg;
   }

   function invalid($col) {
      return isset($this->cols[$col]);
   }

   function all() {
      return array_merge($this->e, $this->base);
   }

   function all_as_string() {
      $ret = "";
      foreach (array_merge($this->e, $this->base) as $err) {
         $ret .= $err->msg . "\n";
      }
      return $ret;
   }

   #[\ReturnTypeWillChange]
   function count(): int {
      return count(array_merge($this->e, $this->base));
   }

   function clear() {
      $this->e = array();
      $this->cols = array();
      $this->base = array();
   }

   function stupid() {
      $this->e[0]->msg = "stupid";
   }
}
