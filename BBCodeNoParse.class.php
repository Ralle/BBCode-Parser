<?php

class BBCodeNoParse extends BBCode {
  protected $tagName = 'noparse';
  protected $type = 'inline';
  
  function dump(BBNode $node)
  {
    $ret = '<!-- noparse -->';
    
    $ret .= $this->dumpChildren($node);
    
    $ret .= '<!-- noparse end -->';
    
    return $ret;
  }
}

?>