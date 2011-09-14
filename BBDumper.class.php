<?php

class BBDumper {
  protected $tree;
  protected $handlers = array();
  protected $defaultHandler = null;
  
  function setTree(BBElement $tree)
  {
    $this->tree = $tree;
  }
  
  function addHandler(BBCode $b)
  {
    $this->handlers[$b->tagName] = $b;
  }
  
  function setDefaultHandler(BBCode $b)
  {
    $this->defaultHandler = $b;
  }
  
  function hasHandler($name)
  {
    return array_key_exists($name, $this->handlers);
  }
  
  function getHandler(BBElement $node)
  {
    switch (get_class($node))
    {
      case 'BBTag':
        if ($this->hasHandler($node->tagName))
        {
          return $this->handlers[$node->tagName];
        }
      case 'BBText':
      case 'BBEndTag':
      case 'BBElement':
        return $this->defaultHandler;
      default:
        throw new Exception('getHandler does not handle ' . get_class($node));
    }
  }
  
  function pdump(BBElement $node, $format = true)
  {
    if ($node instanceof BBTag && $node->noEndTag)
    {
      $format = false;
    }
    
    if (!$format)
    {
      $handler = $this->defaultHandler;
    }
    else
    {
      $handler = $this->getHandler($node);
    }
    if (!($handler instanceof BBCode))
    {
      throw new Exception('Handler not instance of BBCode but of ' . get_class($handler));
    }
    return $handler->dump($node, $this, $format);
  }
  
  function dump()
  {
    // we don't care about the root BBElement object
    return $this->pdump($this->tree);
  }
}

?>