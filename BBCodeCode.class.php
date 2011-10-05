<?php

class BBCodeCode extends BBCode {
  protected $tagName = 'code';
  protected $canContain = array();
  protected $type = 'block';
  
  function dump(BBNode $node)
  {
    if (isset($node->attributes[BBParser::SINGLE_ATTRIBUTE_NAME]))
    {
      $mode = $node->attributes[BBParser::SINGLE_ATTRIBUTE_NAME];
      $ret = '';
      foreach ($node->children as $child)
      {
        $ret .= $child->dump($this);
      }
      return '<div>' . highlight_string($ret) . '</div>';
    }
  }
}

?>