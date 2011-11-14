<?php

abstract class BBCode {
  public $dumper = null;
  
  public $tagName = '';
  // empty type means universal
  public $type = '';
  public $canContain = array();
  public $escapeText = true;
  public $escapeAttributes = true;
  public $replaceNewlines = true;
  
  public $trimInsideLeft = false;
  public $trimInsideRight = false;
  
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
  
  abstract public function dump(BBNode $node);
}

?>