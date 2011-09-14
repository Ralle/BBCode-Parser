<?php

class NoParse extends BBCode {
  protected $tagName = 'noparse';
  protected $type = '';
  protected $canContain = array();
  
  function dump(BBElement $node, BBDumper $dumper, $format = true)
  {
    return '<!-- noparse -->' . $this->dumpChildren($node, $dumper, false) . '<!-- noparse end -->';
  }
}

?>