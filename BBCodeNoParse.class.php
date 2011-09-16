<?php

class BBCodeNoParse extends BBCode {
  protected $tagName = 'noparse';
  protected $type = '';
  protected $canContain = array();
  
  function dump(BBNode $node)
  {
    $ret = '<!-- noparse -->';
    
    foreach ($node->children as $child)
    {
      $ret .= $child->dump($this);
    }
    
    $ret .= '<!-- noparse end -->';
    
    return $ret;
  }
}

?>