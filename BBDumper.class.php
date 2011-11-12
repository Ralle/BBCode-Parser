<?php

class BBDumper {
  private $handlers = array();
  private $defaultHandler = null;
  private $rootHandler = null;

  public function setDefaultHandler(BBCode $handler)
  {
    $this->defaultHandler = $handler;
  }
  
  public function setRootHandler(BBCode $handler)
  {
    $this->rootHandler = $handler;
  }
  
  public function addHandler(BBCode $handler)
  {
    $tagName = $handler->tagName;
    $this->handlers[$tagName] = $handler;
  }
  
  public function addHandlers(array $handlers)
  {
    foreach ($handlers as $handler)
    {
      $this->addHandler($handler);
    }
  }
  
  public function getRealHandler(BBNode $node)
  {
    $parent = $node->parent;
    $parentHandler = $parent->handler;
    if ($parentHandler && $parentHandler !== $this->defaultHandler)
    {
      return $parentHandler;
    }
    else
    {
      return $this->getRealHandler($parent);
    }
  }
  
  public function assignHandlers(BBNode $node)
  {
    if ($this->defaultHandler === null)
    {
      throw new Exception('Default handler is null');
    }
    if ($this->rootHandler === null)
    {
      throw new Exception('Root handler is null');
    }
    if ($this->rootHandler === $this->defaultHandler)
    {
      throw new Exception('rootHandler cannot be same as defaultHandler');
    }
    
    if ($node instanceof BBRoot)
    {
      $node->handler = $this->rootHandler;
    }
    else if ($node instanceof BBTag)
    {
      $parentCanContain = $this->getRealHandler($node)->canContain;
      $likelyHandler = isset($this->handlers[$node->tagName]) ? $this->handlers[$node->tagName] : null;
      if ($likelyHandler && in_array($likelyHandler->type, $parentCanContain))
      {
        // the node can only get the handler if it has a supported content type
        $node->handler = $likelyHandler;
      }
      else
      {
        $node->handler = $this->defaultHandler;
      }
    }
    else if ($node instanceof BBText)
    {
      $node->handler = $node->parent->handler;
    }
    else
    {
      $node->handler = $this->defaultHandler;
    }
    
    if (! ($node->handler instanceof BBCode))
    {
      throw new Exception('Node did not get a handler');
    }
    
    foreach ($node->children as $child)
    {
      $this->assignHandlers($child);
    }
  }
}

?>