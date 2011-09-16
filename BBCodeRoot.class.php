<?php

class BBCodeRoot extends BBCode {
  public function dump(BBNode $node)
  {
    $ret = '';
    foreach ($node->children as $node)
    {
      $ret .= $node->dump($this);
    }
    return $ret;
  }
}

?>