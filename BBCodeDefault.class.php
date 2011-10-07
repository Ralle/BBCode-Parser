<?php

class BBCodeDefault extends BBCode
{
  protected $tagName = '';
  protected $type = '';
  protected $canContain = array();
  protected $escapeText = true;
  
  function dump(BBNode $node)
  {
    switch (get_class($node))
    {
      case 'BBTag':
        $ret = $node->rawText;
        $ret .= $this->dumpChildren($node);
        
        if (!$node->noEndTag)
        {
          assert($node->endTag instanceof BBEndTag);
          $ret .= $node->endTag->rawText;
        }
        return $ret;
        break;
        
      case 'BBEndTag':
        return $node->rawText;
        break;
      
      default:
        throw new Exception('Unknown class');
    }
  }
}

?>