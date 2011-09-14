<?php

abstract class BBCode {
  protected $tagName = '';
  protected $type = '';
  protected $canContain = array();
  
  function __get($name)
  {
    switch ($name)
    {
      case 'tagName':
        return $this->tagName;
    }
  }
  
  protected function dumpChildren(BBElement $node, BBDumper $dumper, $format = true)
  {
    $ret = '';
    foreach ($node->children as $child)
    {
      $ret .= $dumper->pdump($child, $format);
    }
    return $ret;
  }
  
  abstract function dump(BBElement $node, BBDumper $dumper, $format = true);
}

?>