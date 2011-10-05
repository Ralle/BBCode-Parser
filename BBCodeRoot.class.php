<?php

class BBCodeRoot extends BBCode {
  public function dump(BBNode $node)
  {
    $ret = $this->dumpChildren($node);
    return $ret;
  }
}

?>