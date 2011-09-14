<?php

class BBElement {
  var $parent = null;
  function add(BBElement $child)
  {
    $this->children[] = $child;
    $child->parent = $this;
  }
}

class BBText extends BBElement {
  var $text;
  function __construct($t)
  {
    $this->text = $t;
  }
}

class BBTag extends BBElement {
  var $tagName;
  var $noEndTag = true;
  var $children = array();
  var $attributes = array();
  function __construct($t, array $a = array())
  {
    $this->tagName = $t;
    $this->attributes = $a;
  }
}

class BBEndTag extends BBElement {
  var $tagName;
  function __construct($t)
  {
    $this->tagName = $t;
  }
}

?>