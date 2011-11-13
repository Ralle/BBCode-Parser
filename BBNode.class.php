<?php

abstract class BBNode
{
  public $parent = null;
  public $children = array();
  public $handler = null;
  // a BBCode instance to handle contents
  
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
}

?>