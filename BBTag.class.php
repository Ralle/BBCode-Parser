<?php

class BBTag extends BBNode {
  public $tagName;
  public $noEndTag = true;
  public $attributes = array();
  public $rawText = '';
  public $endTag = null;
  
  function __construct($t, array $a = array())
  {
    $this->tagName = strtolower($t);
    $this->attributes = $a;
  }
  
  public function __toString()
  {
    $handler = $this->handler();
    if ($handler->escapeAttributes)
    {
      $this->attributes = array_map('htmlspecialchars', $this->attributes);
    }
    return $handler->dump($this);
  }
}

?>