<?php

class BBTag extends BBNode {
  public $tagName;
  public $hasEndTag = false;
  public $attributes = array();
  public $rawText = '';
  public $endTag = null;
  
  function __construct($t, array $a = array())
  {
    $this->tagName = strtolower($t);
    $this->attributes = $a;
  }
}

?>