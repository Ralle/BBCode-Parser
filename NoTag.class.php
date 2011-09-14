<?php

class NoTag extends BBCode
{
  protected $type = '';
  protected $canContain = array();
  
  protected $tagName = '';
  function dump(BBElement $node, BBDumper $dumper, $format = true)
  {
    switch (get_class($node))
    {
      case 'BBText':
        return $node->text;
        
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
        $ret .= $this->dumpChildren($node, $dumper, $format);
        if ($node instanceof BBTag && !$node->noEndTag)
        {
          $ret .= '[/' . $node->tagName . ']';
        }
        return $ret;
        break;
        
      case 'BBEndTag':
        return '[/' . $node->tagName . ']';
        break;
      
      case 'BBElement':
        return $this->dumpChildren($node, $dumper, $format);
        break;
      
      default:
        throw new Exception('Unknown class');
    }
  }
}

?>