<?php

class BBCodeCallback extends BBCode {
  protected $tagName = '';
  protected $type = '';
  protected $canContain = array();
  protected $escapeText = true;
  protected $callback = null;
  
  function __construct($tagName, $type, array $canContain, $escapeText, $callback)
  {
    $this->tagName = $tagName;
    $this->type = $type;
    $this->canContain = $canContain;
    $this->escapeText = $escapeText;
    $this->callback = $callback;
  }
  
  function dump(BBNode $node)
  {
    $ret = call_user_func($this->callback, $node, $this);
    return '<div>' . $ret . '</div>';
  }

}

?>