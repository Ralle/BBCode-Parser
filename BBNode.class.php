<?php

abstract class BBNode
{
  public $parent = null;
  public $children = array();
  public $handler = null;
  // a BBCode instance to handle contents
  
  public function prepend(BBNode $child)
  {
    array_unshift($this->children, $child);
    $child->parent = $this;
  }
  
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
        array_splice($this->children, $i, 1);
        if ($child->parent === $this)
        {
          $child->parent = null;
        }
        break;
      }
    }
  }
}

?>