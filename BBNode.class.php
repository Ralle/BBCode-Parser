<?php

abstract class BBNode
{
  public $parent = null;
  public $children = array();
  // a BBCode instance to handle contents
  public $handler = null;
  
  public function add(BBNode $child)
  {
    $this->children[] = $child;
    $child->parent = $this;
  }
  
  public function remove(BBNode $node)
  {
    foreach ($this->children as $i => $child)
    {
      if ($node === $child)
      {
        unset($this->children[$i]);
        break;
      }
    }
  }
  
  public function handler()
  {
    return $this->handler;
  }
  
  public function __toString()
  {
    return $this->handler()->dump($this);
  }
  
  public function dumpChildren()
  {
    $ret = '';
    foreach ($this->children as $child)
    {
      $ret .= $child;
    }
    return $ret;
  }
}

?>