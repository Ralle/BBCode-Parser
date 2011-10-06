<?php

class BBCodeCallback extends BBCode {
  protected $tagName = '';
  protected $type = '';
  protected $canContain = array();
  protected $callback = null;
  
  function __construct($tagName, $type, array $canContain, $callback)
  {
    $this->tagName = $tagName;
    $this->type = $type;
    $this->canContain = $canContain;
    $this->callback = $callback;
  }
  
  function dump(BBNode $node)
  {
    $ret = call_user_func($this->callback, $node, $this);
    return '<div>' . $ret . '</div>';
  }

}

?>