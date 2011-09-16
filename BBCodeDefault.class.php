<?php

class BBCodeDefault extends BBCode
{
  protected $type = '';
  protected $canContain = array();
  
  protected $tagName = '';
  function dump(BBNode $node)
  {
    switch (get_class($node))
    {
      case 'BBTag':
        if (isset($node->attributes[BBParser::SINGLE_ATTRIBUTE_NAME]))
        {
          $attr = '="' . $node->attributes[BBParser::SINGLE_ATTRIBUTE_NAME] . '"';
        }
        else
        {
          $attr = array();
          foreach ($node->attributes as $k => $v)
          {
            $attr[] = ' ' . $k . '="' . $v . '"';
          }
          $attr = implode('', $attr);
        }
        $ret = '[' . $node->tagName . $attr . ']';
        foreach ($node->children as $child)
        {
          $ret .= $child->dump($this);
        }
        
        if ($node instanceof BBTag && !$node->noEndTag)
        {
          $ret .= '[/' . $node->tagName . ']';
        }
        return $ret;
        break;
        
      case 'BBEndTag':
        return '[/' . $node->tagName . ']';
        break;
      
      default:
        throw new Exception('Unknown class');
    }
  }
}

?>