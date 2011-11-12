<?php

class BBCodeNoParse extends BBCode {
  public $tagName = 'noparse';
  public $type = 'inline';
  
  function dump(BBNode $node)
  {
    $ret = '<!-- noparse -->';
    
    $ret .= $node->dumpChildren();
    
    $ret .= '<!-- noparse end -->';
    
    return $ret;
  }
}

?>