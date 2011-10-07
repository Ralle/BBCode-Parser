<?php

class BBCodeCallback extends BBCode {
  protected $callback = null;
  
  function __construct($tagName, $type, array $canContain, $callback, $escapeText = true, $replaceNewlines = true)
  {
    $this->tagName = $tagName;
    $this->type = $type;
    $this->canContain = $canContain;
    $this->escapeText = $escapeText;
    $this->replaceNewlines = $replaceNewlines;
    $this->callback = $callback;
  }
  
  function dump(BBNode $node)
  {
    $ret = call_user_func($this->callback, $node, $this);
    return $ret;
  }

}

?>