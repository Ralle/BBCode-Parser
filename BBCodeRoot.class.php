<?php

class BBCodeRoot extends BBCode {
  public function dump(BBNode $node)
  {
    $ret = $this->dumper->dumpChildren($node);
    return $ret;
  }
}

?>