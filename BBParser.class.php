<?php

require_once __DIR__ . '/BBNode.class.php';
require_once __DIR__ . '/BBRoot.class.php';
require_once __DIR__ . '/BBText.class.php';
require_once __DIR__ . '/BBTag.class.php';
require_once __DIR__ . '/BBEndTag.class.php';

/*
  BBParser is a class to break up a text into a tree structure of different BBNodes.
  The parsing is broken up into the following stages:
  - tokenize: breaks the text up into an array of strings to be parsed later.
  - parseTokens: parse the tokens into an array of BBTag, BBText and BBEndTag.
  - makeTree: loops through the list and creates a tree of the nodes.
  
  The parser has an array called $tagsWithNoEnd which is a list of tags for which the parser should not look for an end tag.
  
*/

// an exception used throughout the BBParser. Whenever it is thrown, it is because a broken tag was found. For example "[something else]"
class NotTagException extends Exception {}

class BBParser {
  // the raw text is put here
  private $raw = '';
  // after tokenizing, the tokens are here
  private $tokens = array();
  // after parsing tokens, the objects are here
  private $objects = array();
  // finally, the tree is put here
  private $tree = null;
  
  // a list of tags that do not have an end tag
  public $tagsWithNoEnd = array();
  
  // enable debugging to see what is going on
  public $debug = false;
  
  // position in the list of tokens when parsing.
  private $pos = 0;
  // temporary position used when detecting if a tag is valid.
  private $tpos = 0;
  
  // symbols used for BBCode in general
  const START_BRACKET = '[';
  const END_BRACKET = ']';
  const CLOSE_SYMBOL = '/';
  const KEY_VALUE_DELIMETER = '=';
  const ATTRIBUTE_DELIMETER = ' ';
  const STRING_SYMBOL = '"';
  const SINGLE_ATTRIBUTE_NAME = '_default';
  
  // split the raw text into an array of tokens.
  function tokenize()
  {
    $joinedSymbols = '\\' . implode('\\', array(
      self::START_BRACKET,
      self::END_BRACKET,
      self::CLOSE_SYMBOL,
      self::KEY_VALUE_DELIMETER,
      self::ATTRIBUTE_DELIMETER,
      self::STRING_SYMBOL,
      self::SINGLE_ATTRIBUTE_NAME,
    ));
    $this->tokens = preg_split('#([' . $joinedSymbols . '])#', $this->raw, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
  }
  
  // detect valid attribute and tag name.
  function validName($n)
  {
    return preg_match('#^([a-zA-Z0-9\*]+)$#', $n);
  }
  
  // detect if a token exists at the current position or with an offset.
  function isToken($offset=0)
  {
    return array_key_exists($this->tpos+$offset, $this->tokens);
  }
  
  // when detecting a tag, spaces are permitted in many places. Here we skip to the next token, that is not a space.
  function skipSpace()
  {
    while (trim($this->ttoken()) == '' && $this->isToken())
    {
      $this->tpos++;
    }
  }
  
  // attributes in a BBTag can use double quotes STRING_SYMBOL to allow spaces. If there is one, we continue till we find the end symbol and return the string, otherwise just return the next token.
  function getStr()
  {
    $this->d('Finding string');
    $str = '';
    // find beginning string symbol
    if ($this->ttoken() == self::STRING_SYMBOL)
    {
      $this->tpos++;
      for ($i=0; $this->isToken($i); $i++)
      {
        // continue until we find the ending string symbol.
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
      // else just return the next token.
      $ret = $this->ttoken();
      $this->tpos += 1;
      $this->d('Assuming single token as string');
      return $ret;
    }
  }
  
  // 
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
        throw new NotTagException('Tagname for end tag invalid');
      }
    }
  }
  
  function detectSingleAttributeTag($tagName)
  {
    if ($this->ttoken() == self::KEY_VALUE_DELIMETER)
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
        if ($this->ttoken() == self::KEY_VALUE_DELIMETER)
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
    $length = $this->tpos - $this->pos;
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
          $this->pos = $this->tpos;
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
    if ($this->debug)
    {
      echo $m, "\r\n";
    }
  }
  
  function makeTree()
  {
    $first = new BBRoot();
    $first->hasEndTag = true;
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
      if ($object instanceof BBTag && !in_array($object->tagName, $this->tagsWithNoEnd))
      {
        $current = $object;
      }
      if ($object instanceof BBTag && in_array($object->tagName, $this->tagsWithNoEnd))
      {
        $object->hasEndTag = true;
      }
      // we have an end tag
      if ($object instanceof BBEndTag)
      {
        // we have found the closing tag for the last opening tag
        if ($current instanceof BBTag && $current->tagName == $object->tagName)
        {
          $this->d('End tag matches current tag');
          $current->hasEndTag = true;
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
              $this->d('Previous parent matches end tag');
              $matchingAncestor = $tcurrent;
              break;
            }
            $tcurrent = $tcurrent->parent;
          }
          if (!$matchingAncestor)
          {
            $this->d('Did not find any matching ancestor');
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
          }
          // ancestor found?
          if ($matchingAncestor)
          {
            // yes
            $this->d('End tag matches ancestor');
            $matchingAncestor->hasEndTag = true;
            $matchingAncestor->endTag = $object;
            $current = $matchingAncestor->parent;
          }
          // this is an orphan closing tag
          else
          {
            $this->d('End tag is orphan');
            // this causes an error where current is set to the parent of BBRoot
            // $current = $current->parent;
            $current->add($object);
          }
        }
      }
    }
    $this->tree = $first;
  }
  
  function parse($input)
  {
    $this->raw = $input;
    
    $this->d('Tokenizing');
    $this->tokenize();
    $this->raw = '';
    
    $this->d('Finding tags');
    $this->parseTokens();
    $this->tokens = array();
    
    $this->d('Generating tree');
    $this->makeTree();
    $this->objects = array();
    
    $this->d('Done');
    
    $tree = $this->tree;
    $this->tree = null;
    
    return $tree;
  }
}

?>