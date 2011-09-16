<?php

abstract class BBCode {
  protected $tagName = '';
  // empty type means universal
  protected $type = '';
  protected $canContain = array();
  
  public function __get($name)
  {
    return isset($this->$name) ? $this->$name : null;
  }
   
  public function addContentType($type)
  {
    $this->canContain[] = $type;
  }
  
  public function addContentTypes(array $types)
  {
    foreach ($types as $type)
    {
      $this->addContentType($type);
    }
  }
  
  abstract public function dump(BBNode $element);
  
  // static stuff
  static private $handlers = array();
  static private $defaultHandler = null;
  static private $rootHandler = null;
  
  static public function setDefaultHandler(BBCode $handler)
  {
    self::$defaultHandler = $handler;
  }
  
  static public function setRootHandler(BBCode $handler)
  {
    self::$rootHandler = $handler;
  }
  
  static public function addHandler(BBCode $handler)
  {
    $tagName = $handler->tagName;
    self::$handlers[$tagName] = $handler;
  }
  
  static public function addHandlers(array $handlers)
  {
    foreach ($handlers as $handler)
    {
      self::addHandler($handler);
    }
  }
  
  static public function getHandler(BBNode $node)
  {
    if ($node instanceof BBRoot)
    {
      return self::$rootHandler;
    }
    
    $tagName = $node->tagName;
    // see if there is a bbcode handler that fits this tag name
    if (array_key_exists($tagName, self::$handlers))
    {
      $handler = self::$handlers[$tagName];
      // check if this handler is permitted by the hierarchy
      // empty type means it is universal
      if (!$handler->type || in_array($handler->type, $node->parentCanContain))
      {
        return $handler;
      }
    }

    if (self::$defaultHandler !== null)
    {
      return self::$defaultHandler;
    }
    throw new Exception('No handler for ' . $tagName . ' and no default handler');
  }
}

?>