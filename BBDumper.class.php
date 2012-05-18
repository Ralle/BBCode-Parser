<?php

require_once __DIR__ . '/BBCode.class.php';
require_once __DIR__ . '/BBCodeReplace.class.php';
require_once __DIR__ . '/BBCodeDefault.class.php';
require_once __DIR__ . '/BBCodeRoot.class.php';
require_once __DIR__ . '/BBCodeCallback.class.php';

/*
  The BBDumper is a class used to output a BBNode tree as HTML or text.
  
  A BBNode tree consists of a series of instances of BBText and BBTag inside of a BBRoot node. In order to output any node, the dumper needs handlers. A handler is an instance of the BBCode class.
  You will need to set the default handler for nodes that have no handler.
  You will also need to set the root handler to boostrap the dumping.
  The handlers each have a context type and a list of context types that are allowed inside of it. The root handler will contain the list of context types allowed in the root.
*/

class BBDumper {
  // enable debugging to output what happens when the dumping occurs.
  public $debug = false;
  // contains the array of handlers with their tag name as key
  private $handlers = array();
  // the default handler handles BBText and unhandled BBTags
  private $defaultHandler = null;
  // the root handler as described above
  private $rootHandler = null;
  
  // set the default handler for this dumper
  public function setDefaultHandler(BBCode $handler)
  {
    // every handler needs a reference to their dumper
    $handler->dumper = $this;
    $this->defaultHandler = $handler;
  }
  
  // set the root handler for this dumper
  public function setRootHandler(BBCode $handler)
  {
    $handler->dumper = $this;
    $this->rootHandler = $handler;
  }
  
  // to add a handler to the dumper, you will use this function. Optionally you can change the tagName for which the handler will be added.
  // this could for example be used to add both a [b] and a [bold] tag with the same handler.
  public function addHandler(BBCode $handler, $otherName = '')
  {
    $handler->dumper = $this;
    $tagName = $otherName ? $otherName : $handler->tagName;
    $this->handlers[$tagName] = $handler;
  }
  
  // add multiple handlers as an array.
  public function addHandlers(array $handlers)
  {
    foreach ($handlers as $handler)
    {
      $this->addHandler($handler);
    }
  }
  
  // get the immediate handler for a BBNode.
  public function getImmediateHandler(BBNode $node)
  {
    // if we have a BBTag and a handler with that tag name, return it, otherwise return null.
    if ($node instanceof BBTag && isset($this->handlers[$node->tagName]))
    {
      return $this->handlers[$node->tagName];
    }
    return null;
  }
  
  // get the absolute handler for a BBNode. Here we check whether or not the handler is allowed in the context of the node. This is the one we will be using.
  public function getHandler(BBNode $node)
  {
    if ($node->handler)
    {
      return $node->handler;
    }
    $this->d('Get handler for ' . get_class($node) . ' with tag name: ' . ($node instanceof BBTag ? $node->tagName : 'Non-tag'));
    switch (get_class($node))
    {
      case 'BBRoot':
        $handler = $this->rootHandler;
        break;
        
      case 'BBTag':
        $parentHandler = $node->parent->handler;
        $parentCanContain = $parentHandler->canContain;
        
        $iHandler = $this->getImmediateHandler($node);
        // check to see if the node has a handler and if the parent permits this context type
        if ($iHandler && $node->hasEndTag)
        {
          if (in_array($iHandler->type, $parentCanContain))
          {
            $handler = $iHandler;
          }
          else
          {
            // else use the default handler
            $handler = $this->defaultHandler;
            $this->d('The handler ' . $iHandler->tagName . ' is not allowed in this context.');
          }
        }
        else
        {
          // there exists no handler to this tag, use the default one
          $this->d('There is no handler for this tag.');
          $handler = $this->defaultHandler;
        }
        break;
        
      case 'BBText':
        // just use the parent's handler
        $handler = $node->parent->handler;
        break;
        
      default:
        // this case should never be used. In any case, just use the default
        $handler = $this->defaultHandler;
        break;
    }
    $node->handler = $handler;
    return $handler;
  }
  
  // this function is the whole purpose of the dumper. Depending on which kind of BBNode we wish to dump, we call different dump methods.
  public function dump(BBNode $node)
  {
    if (!$this->defaultHandler) {
      throw new Exception('Missing defaultHandler');
    }
    if (!$this->rootHandler) {
      throw new Exception('Missing rootHandler');
    }
    
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
  
  // dump the BBRoot
  public function dumpBBRoot(BBRoot $node)
  {
    $handler = $this->getHandler($node);
    return $handler->dump($node);
  }
  
  // dump the BBTag and escape the attributes if requested by the handler
  public function dumpBBTag(BBTag $node)
  {
    $handler = $this->getHandler($node);
    if ($handler->escapeAttributes)
    {
      $node->attributes = array_map('htmlspecialchars', $node->attributes);
    }
    return $handler->dump($node);
  }
  
  // dump an end tag. This will only occur if we have an orphan end tag
  public function dumpBBEndTag(BBEndTag $node)
  {
    return '[/' . $node->tagName . ']';
  }
  
  // dump BBText and escape it if requested
  public function dumpBBText(BBText $node)
  {
    $handler = $this->getHandler($node);
    $text = $node->text;
    // escape the text
    if ($handler->escapeText)
    {
      $text = htmlspecialchars($text);
    }
    // make linebreaks
    if ($handler->replaceNewlines)
    {
      $text = nl2br($text);
    }
    return $text;
  }
  
  // if the first child is a BBText, call ltrim on it.
  public function trimInsideLeft(BBNode $node)
  {
    $first = reset($node->children);
    if ($first !== false && $first instanceof BBText)
    {
      $first->text = ltrim($first->text);
    }
  }
  
  // if the last child is a BBText, call rtrim on it.
  public function trimInsideRight(BBNode $node)
  {
    $last = end($node->children);
    if ($last !== false && $last instanceof BBText)
    {
      $last->text = rtrim($last->text);
    }
  }
  
  // if the first child is a BBText, remove the first linebreak in it.
  public function removeFirstLinebreak(BBNode $node)
  {
    $first = reset($node->children);
    if ($first !== false && $first instanceof BBText)
    {
      $first->text = preg_replace('#^(\r\n|\r|\n)#', '', $first->text);
    }
  }
  
  // if the last child is a BBText, remove the last linebreak in it.
  public function removeLastLinebreak(BBNode $node)
  {
    $last = end($node->children);
    if ($last !== false && $last instanceof BBText)
    {
      $last->text = preg_replace('#(\r\n|\r|\n)$#', '', $last->text);
    }
  }
  
  // dump the children of a BBNode. Only BBTag and BBRoot can have children. All the different trim functions get called
  public function dumpChildren(BBNode $node)
  {
    $ret = '';
    if ($node->handler->trimInsideLeft)
    {
      $this->trimInsideLeft($node);
    }
    if ($node->handler->removeFirstLinebreak)
    {
      $this->removeFirstLinebreak($node);
    }
    if ($node->handler->removeLastLinebreak)
    {
      $this->removeLastLinebreak($node);
    }
    if ($node->handler->trimInsideRight)
    {
      $this->trimInsideRight($node);
    }
    // loop through all the children and dump them
    for ($i = 0; $i < count($node->children); $i++)
    {
      $child = $node->children[$i];
      // get the handler
      $childHandler = $this->getHandler($child);
      // get the next sibling
      $nextSibling = array_key_exists($i+1, $node->children) ? $node->children[$i+1] : null;
      // get the next sibling's handler
      $nextSiblingHandler = $nextSibling !== null ? $this->getHandler($nextSibling) : null;
      
      // if the child is a text and the next sibling is a tag which has removeLinebreaksBefore, remove the last linebreak of the child.
      if ($child instanceof BBText && $nextSibling instanceof BBTag && $nextSiblingHandler->removeLinebreakBefore)
      {
        // remove the childs last linebreak.
        $child->text = preg_replace('#(\r\n|\r|\n)$#', '', $child->text);
      }
      // if the child is a tag and the next sibling is a text which has removeLinebreaksAfter, remove the first linebreak of the next sibling.
      else if ($child instanceof BBTag && $nextSibling instanceof BBText && $childHandler->removeLinebreakAfter)
      {
        // remove the next siblings first linebreak.
        $nextSibling->text = preg_replace('#^(\r\n|\r|\n)#', '', $nextSibling->text);
      }
      // dump the child
      $ret .= $this->dump($child);
    }
    return $ret;
  }
  
  // the debug function. Give it a string and it gets printed if debugging is enabled.
  public function d($m)
  {
    if ($this->debug)
    {
      echo $m, "\r\n";
    }
  }
}

?>