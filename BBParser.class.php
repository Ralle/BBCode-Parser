<?php

require_once __DIR__ . '/BBCode.class.php';
require_once __DIR__ . '/BBCodeRoot.class.php';
require_once __DIR__ . '/BBDatatypes.php';

class NotTagException extends Exception {}

class BBParser {
  private $raw = '';
  private $tokens = array();
  private $objects = array();
  private $tree = array();
  
  public static $debug = false;
  
  const START_BRACKET = '[';
  const END_BRACKET = ']';
  const CLOSE_SYMBOL = '/';
  const ATTRIBUTE_DELIMETER = '=';
  const STRING_SYMBOL = '"';
  const SINGLE_ATTRIBUTE_NAME = '_default';
  
  function tokenize()
  {
    $this->tokens = preg_split('#([\[\]"\/= ])#', $this->raw, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
  }
  
  function validName($n)
  {
    return preg_match('#^([a-z]+)$#', $n);
  }
  
  private $tpos = 0;
  private $pos = 0;
  
  function isToken($offset=0)
  {
    return array_key_exists($this->tpos+$offset, $this->tokens);
  }
  
  function skipSpace()
  {
    while (trim($this->ttoken()) == '' && $this->isToken())
    {
      $this->tpos++;
    }
  }
  
  function getStr()
  {
    $this->d('Finding string');
    $str = '';
    if ($this->ttoken() == self::STRING_SYMBOL)
    {
      $this->tpos++;
      for ($i=0; $this->isToken($i); $i++)
      {
        if ($this->ttoken($i) == self::STRING_SYMBOL)
        {
          $this->tpos += $i+1;
          $this->d('Found string');
          return $str;
        }
        else
        {
          $str .= $this->ttoken($i);
        }
      }
      throw new NotTagException('Not a valid string');
    }
    else
    {
      $ret = $this->ttoken();
      $this->tpos += 1;
      $this->d('Assuming single token as string');
      return $ret;
    }
  }
  
  function ttoken($offset=0)
  {
    if ($this->isToken($offset))
    {
      return $this->tokens[$this->tpos+$offset];
    }
    $this->d('Reached end of line');
  }
  
  function token($offset=0)
  {
    return $this->tokens[$this->pos+$offset];
  }
  
  function detectEndTag()
  {
    if ($this->ttoken() == self::CLOSE_SYMBOL)
    {
      $this->tpos++;
      $this->skipSpace();
      $tagName = $this->ttoken();
      if ($this->validName($tagName))
      {
        $this->tpos++;
        $this->skipSpace();
        if ($this->ttoken() == self::END_BRACKET)
        {
          $this->tpos++;
          $obj = new BBEndTag($tagName);
          $obj->rawText = $this->getPassedTokensString();
          $this->objects[] = $obj;
          $this->pos = $this->tpos;
          return true;
        }
        else
        {
          throw new NotTagException('No closing bracket for end tag');
        }
      }
      else
      {
        throw new Exception('Tagname for end tag invalid');
      }
    }
  }
  
  function detectSingleAttributeTag($tagName)
  {
    if ($this->ttoken() == self::ATTRIBUTE_DELIMETER)
    {
      $this->tpos++;
      $attrValue = $this->getStr();
      $this->skipSpace();
      if ($this->ttoken() == self::END_BRACKET)
      {
        $this->tpos++;
        $attribs = array(self::SINGLE_ATTRIBUTE_NAME => $attrValue);
        $obj = new BBTag($tagName, $attribs);
        $obj->rawText = $this->getPassedTokensString();
        $this->objects[] = $obj;
        $this->pos = $this->tpos;
        return true;
      }
      throw new NotTagException('Missing closing bracket');
    }
  }
  
  function detectMultipleAttributeTag($tagName)
  {
    // we must have multiple attributes
    $attributes = array();
    while (1)
    {
      $this->d('Finding attributes');
      $this->skipSpace();
      if ($this->ttoken() == self::END_BRACKET)
      {
        $this->tpos++;
        $obj = new BBTag($tagName, $attributes);
        $obj->rawText = $this->getPassedTokensString();
        $this->objects[] = $obj;
        $this->pos = $this->tpos;
        return true;
      }
      $this->skipSpace();
      $attrName = $this->ttoken();
      if ($this->validName($attrName))
      {
        $this->tpos++;
        $this->skipSpace();
        if ($this->ttoken() == self::ATTRIBUTE_DELIMETER)
        {
          $this->tpos++;
          $this->skipSpace();
          $attrValue = $this->getStr();
          $attributes[$attrName] = $attrValue;
        }
        else
        {
          throw new NotTagException('Expecting equality token');
        }
      }
      else
      {
        throw new NotTagException('Not valid attribute name: "' . $attrName . '"');
      }
    }
  }
  
  function detectNoAttributeTag($tagName)
  {
    if ($this->ttoken() == self::END_BRACKET)
    {
      // tag ended here
      $this->tpos++;
      $obj = new BBTag($tagName);
      $obj->rawText = $this->getPassedTokensString();
      $this->objects[] = $obj;
      $this->pos = $this->tpos;
      return true;
    }
  }
  
  function detectBeginTag()
  {
    $tagName = $this->ttoken();
    if ($this->validName($tagName))
    {
      $this->tpos++;
      $this->skipSpace();
      
      if ($this->detectNoAttributeTag($tagName))
      {
        return true;
      }
      
      if ($this->detectSingleAttributeTag($tagName))
      {
        return true;
      }
      
      if ($this->detectMultipleAttributeTag($tagName))
      {
        return true;
      }
    }
    else
    {
      throw new NotTagException('Invalid tagName');
    }
  }
  
  function detectTag()
  {
    $this->tpos = $this->pos;
    
    if ($this->ttoken() == self::START_BRACKET)
    {
      $this->tpos++;
      $this->skipSpace();
      
      if ($this->detectEndTag())
      {
        return true;
      }
      
      if ($this->detectBeginTag())
      {
        return true;
      }
    }
    else
    {
      throw new NotTagException('Missing starting token');
    }
  }
  
  function getPassedTokens()
  {
    $length = $this->tpos - $this->pos + 1;
    $passedTokens = array_slice($this->tokens, $this->pos, $length);
    return $passedTokens;
  }
  
  function getPassedTokensString()
  {
    return implode('', $this->getPassedTokens());
  }
  
  function parseTokens()
  {
    $str = '';
    while (array_key_exists($this->pos, $this->tokens))
    {
      $this->d('Current token: ' . $this->token());
      if ($this->token() == self::START_BRACKET)
      {
        if ($str)
        {
          $this->objects[] = new BBText($str);
          $str = '';
        }
        try
        {
          $this->d('Testing tag');
          $this->detectTag();
          $this->d('Found tag');
        }
        catch (NotTagException $e)
        {
          $this->d("Tag Error: " . $e->getMessage() . "\r\n");
          $length = $this->tpos - $this->pos + 1;
          $passedTokens = $this->getPassedTokensString();
          $this->objects[] = new BBText($passedTokens);
          $this->pos = $this->tpos+1;
        }
      }
      else
      {
        $str .= $this->token();
        $this->pos++;
      }
    }
    if ($str)
    {
      $this->objects[] = new BBText($str);
    }
  }
  
  function d($m)
  {
    if (self::$debug)
    {
      echo $m, "\r\n";
    }
  }
  
  function makeTree()
  {
    $first = new BBRoot();
    $current = $first;
    foreach ($this->objects as $object)
    {
      $this->d(($object instanceof BBEndTag ? '/' : '') . @$object->tagName);
      // add object as child to the current node
      if (!($object instanceof BBEndTag))
      {
        $current->add($object);
      }
      // we have a tag, which may have children
      if ($object instanceof BBTag)
      {
        $current = $object;
      }
      // we have an end tag
      if ($object instanceof BBEndTag)
      {
        // we have found the closing tag for the last opening tag
        if ($current->tagName == $object->tagName)
        {
          $this->d('End tag matches current tag');
          $current->noEndTag = false;
          $current->endTag = $object;
          $current = $current->parent;
        }
        // we have found a tag that does not match the current tag
        else
        {
          $this->d('End tag does not match current tag');
          // check to see if this closing tag matches any previous opening tag
          $tcurrent = $current;
          $matchingAncestor = null;
          while ($tcurrent !== $first)
          {
            // we have found an ancestor for which this tag closes
            if ($tcurrent->tagName == $object->tagName)
            {
              $matchingAncestor = $tcurrent;
              break;
            }
            $tcurrent = $tcurrent->parent;
          }
          // check if not ancestor but previous sibling
          foreach ($current->children as $child)
          {
            if ($child === $object)
            {
              break;
            }
            if ($child instanceof BBTag && $child->tagName == $object->tagName)
            {
              $this->d('Previous sibling matches end tag');
              $matchingAncestor = $child;
              break;
            }
          }
          // ancestor found?
          if ($matchingAncestor)
          {
            // yes
            $this->d('End tag matches ancestor');
            /*
            // go back to the ancestor again to mark all tags on the way noEndTag
            while ($current !== $matchingAncestor)
            {
              $current->noEndTag = true;
              $current = $current->parent;
            }
            // the last ancestor has a closing tag
            $current->noEndTag = false;
            */
            $matchingAncestor->noEndTag = false;
            $matchingAncestor->endTag = $object;
            $current = $current->parent;
          }
          // this is an orphan closing tag
          else
          {
            $this->d('End tag is orphan');
            $current->add($object);
          }
        }
      }
    }
    $this->tree = $first;
  }
  
  function tree()
  {
    return $this->tree;
  }
  
  function parse($input)
  {
    $this->raw = $input;
    
    $this->d('Tokenizing');
    $this->tokenize();
    $this->d('Finding tags');
    $this->parseTokens();
    $this->d('Generating tree');
    $this->makeTree();
    $this->d('Done');
    unset($this->tokens, $this->objects, $this->raw);
  }
}

?>