<?php

class Italic extends BBCode {
  protected $tagName = 'i';
  
  protected $type = 'inline';
  protected $canContain = array('inline');
  
  function dump(BBElement $node, BBDumper $dumper, $format = true)
  {
    return '<i>' . $this->dumpChildren($node, $dumper, $format) . '</i>';
  }
}

?>