<?php

class BBCodeNoParse extends BBCode {
  public $tagName = 'noparse';
  public $type = 'inline';
  
  function dump(BBNode $node)
  {
    $ret = '<!-- noparse -->';
    $ret .= $this->dumper->dumpChildren($node);
    $ret .= '<!-- noparse end -->';
    return $ret;
  }
}

?>