<?php

abstract class BBCode {
  public $dumper = null;
  
  public $tagName = '';
  // the content type of this handler
  public $type = '';
  // list of content types that a given handler can contain
  public $canContain = array();
  // escape the BBTexts inside
  public $escapeText = true;
  // automatically escapes all attributes given to a BBCode handler
  public $escapeAttributes = true;
  // replaces \r\n with <br /> in BBTexts
  public $replaceNewlines = true;
  // remove the first linebreak from the content of the handler    
  public $removeFirstLinebreak = false;
  // remove the last linebreak
  public $removeLastLinebreak = false;
  // remove the last linebreak before this handler
  public $removeLinebreakBefore = false;
  // remove the first linebreak after
  public $removeLinebreakAfter = false;
  // call the ltrim() function on the contents of this handler
  public $trimInsideLeft = false;
  // call the rtrim() function
  public $trimInsideRight = false;
   
  public function addContentType($type)
  {
    $this->canContain[$type] = $type;
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