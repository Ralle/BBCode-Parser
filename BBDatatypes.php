<?php

abstract class BBNode
{
  public $parent = null;
  public $children = null;
  
  // for restricting which BBCodes can contain which
  public $parentCanContain = array();
  
  public function add(BBNode $child)
  {
    $this->children[] = $child;
    $child->parent = $this;
  }
  
  public function dump(BBCode $handler)
  {
    if ($this->parent)
    {
      $this->parentCanContain = array_intersect($handler->canContain, $this->parent->parentCanContain);
    }
    else
    {
      $this->parentCanContain = $handler->canContain;
    }
    return $this->toString();
  }
  
  abstract public function toString();
}

class BBRoot extends BBNode
{
  public function toString()
  {
    $handler = BBCode::getHandler($this);
    return $handler->dump($this);
  }
}

class BBText extends BBNode {
  public $text;
  
  function __construct($t)
  {
    $this->text = $t;
  }
  
  public function toString()
  {
    return $this->text;
  }
}

class BBTag extends BBNode {
  public $tagName;
  public $noEndTag = true;
  public $attributes = array();
  public $rawText = '';
  
  function __construct($t, array $a = array())
  {
    $this->tagName = $t;
    $this->attributes = $a;
  }
  
  public function toString()
  {
    $handler = BBCode::getHandler($this);
    return $handler->dump($this);
  }
}

class BBEndTag extends BBNode {
  public $tagName;
  
  function __construct($t)
  {
    $this->tagName = $t;
  }
  
  public function toString()
  {
    return '[/' . $this->tagName . ']';
  }
}

?>