<?php

abstract class BBNode
{
  public $parent = null;
  public $children = array();
  
  // for restricting which BBCodes can contain which
  public $parentCanContain = array();
  
  public function add(BBNode $child)
  {
    $this->children[] = $child;
    $child->parent = $this;
  }
  
  public function dump(BBCode $handler)
  {
    // echo 'Dump: ' . get_class($this) . ' with handler: ' . get_class($handler), "\r\n";
    if ($this->parent)
    {
      $this->parentCanContain = array_intersect($handler->canContain, $this->parent->parentCanContain);
    }
    else
    {
      $this->parentCanContain = $handler->canContain;
    }
    return $this->toString($handler);
  }
  
  abstract public function toString();
}

class BBRoot extends BBNode
{
  public function toString(BBCode $handler = null)
  {
    $handler = BBCode::getHandler($this); // root handler
    // the root node relies on the handlers canContain
    $this->parentCanContain = $handler->canContain;
    return $handler->dump($this);
  }
}

class BBText extends BBNode {
  public $text;
  
  function __construct($t)
  {
    $this->text = $t;
  }
  
  public function toString(BBCode $handler = null)
  {
    $text = $this->text;
    if ($handler)
    {
      if ($handler->escapeText)
      {
        $text = htmlspecialchars($text);
      }
      if ($handler->replaceNewlines)
      {
        $text = nl2br($text);
      }
      return $text;
    }
    return $this->text;
  }
}

class BBTag extends BBNode {
  public $tagName;
  public $noEndTag = true;
  public $attributes = array();
  public $rawText = '';
  public $endTag = null;
  
  function __construct($t, array $a = array())
  {
    $this->tagName = strtolower($t);
    $this->attributes = $a;
  }
  
  public function toString(BBCode $handler = null)
  {
    $handler = BBCode::getHandler($this);
    return $handler->dump($this);
  }
}

class BBEndTag extends BBNode {
  public $tagName;
  public $rawText = '';
  
  function __construct($t)
  {
    $this->tagName = strtolower($t);
  }
  
  public function toString(BBCode $handler = null)
  {
    return '[/' . $this->tagName . ']';
  }
}

?>