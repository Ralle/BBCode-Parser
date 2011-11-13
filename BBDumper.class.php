<?php

class BBDumper {
  public static $debug = true;
  
  private $handlers = array();
  private $defaultHandler = null;
  private $rootHandler = null;

  public function setDefaultHandler(BBCode $handler)
  {
    $handler->dumper = $this;
    $this->defaultHandler = $handler;
  }
  
  public function setRootHandler(BBCode $handler)
  {
    $handler->dumper = $this;
    $this->rootHandler = $handler;
  }
  
  public function addHandler(BBCode $handler)
  {
    $handler->dumper = $this;
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
  
  public function getImmediateHandler(BBNode $node)
  {
    if ($node instanceof BBTag && isset($this->handlers[$node->tagName]))
    {
      return $this->handlers[$node->tagName];
    }
    return null;
  }
  
  public function getHandler(BBNode $node)
  {
    if ($node->handler)
    {
      return $node->handler;
    }
    $this->d('Get handler for ' . get_class($node) . ' with tag name: ' . @$node->tagName);
    switch (get_class($node))
    {
      case 'BBRoot':
        $handler = $this->rootHandler;
        break;
      case 'BBTag':
        $parentHandler = $node->parent->handler;
        $parentCanContain = $parentHandler->canContain;
        
        $iHandler = $this->getImmediateHandler($node);
        // the node can only get the handler if it has a supported content type
        if ($iHandler)
        {
          if (in_array($iHandler->type, $parentCanContain))
          {
            $handler = $iHandler;
          }
          else
          {
            $handler = $this->defaultHandler;
            $this->d('The that is not allowed in this context.');
          }
        }
        else
        {
          $this->d('There is no handler for this tag.');
          $handler = $this->defaultHandler;
        }
        break;
      case 'BBText':
        $handler = $node->parent->handler;
        break;
      default:
        $handler = $this->defaultHandler;
        break;
    }
    $node->handler = $handler;
    return $handler;
  }
  
  // public function getRealHandler(BBNode $node)
  // {
  //   $parent = $node->parent;
  //   $parentHandler = $parent->handler;
  //   if ($parentHandler && $parentHandler !== $this->defaultHandler)
  //   {
  //     return $parentHandler;
  //   }
  //   else
  //   {
  //     return $this->getRealHandler($parent);
  //   }
  // }
  
  // public function assignHandlers(BBNode $node)
  // {
  //   if ($this->defaultHandler === null)
  //   {
  //     throw new Exception('Default handler is null');
  //   }
  //   if ($this->rootHandler === null)
  //   {
  //     throw new Exception('Root handler is null');
  //   }
  //   if ($this->rootHandler === $this->defaultHandler)
  //   {
  //     throw new Exception('rootHandler cannot be same as defaultHandler');
  //   }
  //   
  //   if ($node instanceof BBRoot)
  //   {
  //     $node->handler = $this->rootHandler;
  //   }
  //   else if ($node instanceof BBTag)
  //   {
  //     $parentCanContain = $this->getRealHandler($node)->canContain;
  //     $likelyHandler = isset($this->handlers[$node->tagName]) ? $this->handlers[$node->tagName] : null;
  //     if ($likelyHandler && in_array($likelyHandler->type, $parentCanContain))
  //     {
  //       // the node can only get the handler if it has a supported content type
  //       $node->handler = $likelyHandler;
  //     }
  //     else
  //     {
  //       $node->handler = $this->defaultHandler;
  //     }
  //   }
  //   else if ($node instanceof BBText)
  //   {
  //     $node->handler = $node->parent->handler;
  //   }
  //   else
  //   {
  //     $node->handler = $this->defaultHandler;
  //   }
  //   
  //   if (! ($node->handler instanceof BBCode))
  //   {
  //     throw new Exception('Node did not get a handler');
  //   }
  //   
  //   foreach ($node->children as $child)
  //   {
  //     $this->assignHandlers($child);
  //   }
  // }
  
  public function dump(BBNode $node)
  {
    switch (get_class($node))
    {
      case 'BBRoot':
        return $this->dumpBBRoot($node);
      case 'BBTag':
        return $this->dumpBBTag($node);
      case 'BBEndTag':
        return $this->dumpBBEndTag($node);
      case 'BBText':
        return $this->dumpBBText($node);
      default:
        throw new Exception('Unknown BBNode subclass.');
    }
  }
  
  public function dumpBBRoot(BBRoot $node)
  {
    $handler = $this->getHandler($node);
    return $handler->dump($node);
  }
  
  public function dumpBBTag(BBTag $node)
  {
    $handler = $this->getHandler($node);
    if ($handler->escapeAttributes)
    {
      $node->attributes = array_map('htmlspecialchars', $node->attributes);
    }
    return $handler->dump($node);
    
  }
  
  public function dumpBBEndTag(BBEndTag $node)
  {
    return '[/' . $node->tagName . ']';
  }
  
  public function dumpBBText(BBText $node)
  {
    $handler = $this->getHandler($node);
    $text = $node->text;
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
  
  public function dumpChildren(BBNode $node)
  {
    $ret = '';
    foreach ($node->children as $child)
    {
      $ret .= $this->dump($child);
    }
    return $ret;
  }
  
  public function d($m)
  {
    if (self::$debug)
    {
      echo $m, "\r\n";
    }
  }
}

?>