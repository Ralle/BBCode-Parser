<?php

class BBCodeRoot extends BBCode {
  public function dump(BBNode $node)
  {
    $ret = $node->dumpChildren();
    return $ret;
  }
}

?>