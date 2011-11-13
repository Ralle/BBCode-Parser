<?php

class BBEndTag extends BBNode {
  public $tagName;
  public $rawText = '';
  
  function __construct($t)
  {
    $this->tagName = strtolower($t);
  }
}

?>