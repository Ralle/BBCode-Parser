<?php

error_reporting(-1);

// require_once __DIR__ . '/BBNode.class.php';
// require_once __DIR__ . '/BBRoot.class.php';
// require_once __DIR__ . '/BBText.class.php';
// require_once __DIR__ . '/BBTag.class.php';
// require_once __DIR__ . '/BBEndTag.class.php';

require_once __DIR__ . '/BBParser.class.php';
require_once __DIR__ . '/BBDumper.class.php';

// require_once __DIR__ . '/BBCode.class.php';
// require_once __DIR__ . '/BBCodeReplace.class.php';
// require_once __DIR__ . '/BBCodeDefault.class.php';
// require_once __DIR__ . '/BBCodeRoot.class.php';
// require_once __DIR__ . '/BBCodeCallback.class.php';

$str = 'Before list
[list]
[*]Cow
[/list]
Less';

$parser = new BBParser;
$dumper = new BBDumper;

const TYPE_INLINE = 'inline';
const TYPE_BLOCK = 'block';
const TYPE_LISTITEM = 'listitem';

$normalTypes = array(TYPE_INLINE, TYPE_BLOCK);
$inlineType = array(TYPE_INLINE);

// simple replacement 
$bold = new BBCodeReplace('b', '<b>', '</b>', TYPE_INLINE, $inlineType);
$italic = new BBCodeReplace('i', '<i>', '</i>', TYPE_INLINE, $inlineType);
$underline = new BBCodeReplace('u', '<u>', '</u>', TYPE_INLINE, $inlineType);
$block = new BBCodeReplace('block', '<div>', '</div>', TYPE_BLOCK, $normalTypes);
$noparse = new BBCodeReplace('noparse', '<!-- noparse -->', '<!-- noparse end -->', TYPE_INLINE);
$dumper->addHandlers(array($bold, $italic, $underline, $block, $noparse));

// the default handler. It handles if a tag has no handler or is not permitted in a certain context.
$notag = new BBCodeDefault();
$notag->addContentTypes($normalTypes);
$dumper->setDefaultHandler($notag);

// the root handler. It bootstraps the application by allowing all content types in the parent node.
$root = new BBCodeRoot();
$root->addContentTypes($normalTypes);
$dumper->setRootHandler($root);

$list = new BBCodeCallback('list', TYPE_BLOCK, array(TYPE_LISTITEM), 'handle_list');
$list->trimInsideLeft = true;
$list->trimInsideRight = true;
$dumper->addHandler($list);

$listitem = new BBCodeReplace('*', '<li>', '</li>', TYPE_LISTITEM, $normalTypes);
$listitem->trimInsideLeft = true;
$listitem->trimInsideRight = true;
$dumper->addHandler($listitem);
// tell the parser that * tags don't have end tags.
$parser->tagsWithNoEnd[] = '*';

// the callback function for the list tag. This function modifies the structure of the nodes in the subtree of the tag. Each "li" list item has no children, but they should. Therefore we move all the following siblings into the last seen "li".
// we also look at the first item and see if it is either empty after trim() or else we create a new "li" and add it to it.
function handle_list(BBNode $node, BBCode $handler)
{
  $currentItem = null;
  $nodeChildren = $node->children;
  foreach ($nodeChildren as $child)
  {
    $validText = ($child instanceof BBText && trim($child->text));
    // if the child is a list item, the following children (which are not list items) should be added to the child.
    if ($child instanceof BBTag && $child->tagName == '*')
    {
      $currentItem = $child;
    }
    // else if the child is a non-empty text or not a text, it should be added to the last list item.
    else if ($validText || !($child instanceof BBText))
    {
      if ($currentItem == null)
      {
        // this means the first component of the list is not a list item
        $currentItem = new BBTag('*');
        array_unshift($node->children, $currentItem);
        $currentItem->parent = $node;
      }
      $node->remove($child);
      $currentItem->add($child);
    }
  }
  return '<ul class="bblist">' . $handler->dumper->dumpChildren($node) . '</ul>';
}

$node = $parser->parse($str);

echo $dumper->dump($node);

echo "\r\n";

?>
