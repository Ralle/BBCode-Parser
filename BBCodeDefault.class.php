<?php

class BBCodeDefault extends BBCode {
  function dump(BBNode $node)
  {
    switch (get_class($node))
    {
      case 'BBTag':
        $ret = $node->rawText;
        $ret .= $this->dumper->dumpChildren($node);
        
        if (/*$node->hasEndTag && */$node->endTag instanceof BBEndTag)
        {
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