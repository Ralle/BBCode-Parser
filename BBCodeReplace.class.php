<?php

class BBCodeReplace extends BBCode {
  public $start;
  public $end;
  
  function __construct($tagName, $start, $end, $type, array $canContain = array())
  {
    $this->tagName = $tagName;
    $this->type = $type;
    $this->start = $start;
    $this->end = $end;
    $this->canContain = $canContain;
  }
  
  function dump(BBNode $node)
  {
    $ret = $this->start;
    $ret .= $node->dumpChildren();
    $ret .= $this->end;
    return $ret;
  }
}

?>