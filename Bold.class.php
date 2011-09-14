<?php

class Bold extends BBCode {
  protected $tagName = 'b';
  
  protected $type = 'inline';
  protected $canContain = array('inline');
  
  function dump(BBElement $node, BBDumper $dumper, $format = true)
  {
    return '<b>' . $this->dumpChildren($node, $dumper, $format) . '</b>';
  }
}

?>