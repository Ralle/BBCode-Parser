<?php

class BBCodeReplace extends BBCode {
  private $start, $end;
  
  function __construct($tagName, $start, $end, $type)
  {
    $this->tagName = $tagName;
    $this->type = $type;
    $this->start = $start;
    $this->end = $end;
  }
  
  function dump(BBNode $node)
  {
    $ret = $this->start;
    foreach ($node->children as $child)
    {
      $ret .= $child->dump($this);
    }
    $ret .= $this->end;
    return $ret;
  }
}

?>