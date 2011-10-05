<?php

class BBCodeQuote extends BBCode {
  protected $tagName = 'quote';
  protected $canContain = array();
  protected $type = 'block';
  
  function dump(BBNode $node)
  {
    assert($node instanceof BBTag);
    $ret = '';
    if (isset($node->attributes[BBParser::SINGLE_ATTRIBUTE_NAME]))
    {
      $ret .= '<h1>' . $node->attributes[BBParser::SINGLE_ATTRIBUTE_NAME] . '</h1>';
    }
    $ret .= '<div style="background: red">';
    $ret .= $this->dumpChildren($node);
    $ret .= '</div>';
    return $ret;
  }
}

?>