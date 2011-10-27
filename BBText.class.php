<?php

class BBText extends BBNode {
  public $text;
  
  function __construct($t)
  {
    $this->text = $t;
  }
  
  public function __toString()
  {
    $handler = $this->handler;
    $text = $this->text;
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
}

?>