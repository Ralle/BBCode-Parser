<?php

error_reporting(-1);

require_once __DIR__ . '/BBNode.class.php';
require_once __DIR__ . '/BBRoot.class.php';
require_once __DIR__ . '/BBText.class.php';
require_once __DIR__ . '/BBTag.class.php';
require_once __DIR__ . '/BBEndTag.class.php';

require_once __DIR__ . '/BBParser.class.php';
require_once __DIR__ . '/BBDumper.class.php';

require_once __DIR__ . '/BBCode.class.php';
require_once __DIR__ . '/BBCodeReplace.class.php';
require_once __DIR__ . '/BBCodeDefault.class.php';
require_once __DIR__ . '/BBCodeNoParse.class.php';
require_once __DIR__ . '/BBCodeRoot.class.php';
require_once __DIR__ . '/BBCodeCallback.class.php';

$str = <<<BBCODE
<i>I am trying to make
something italic</i>
[unfiltered]everything <b>inside</b> this tag
is unfiltered and does not have breaks[/unfiltered]
BBCODE;

$str = <<<LOL
[noparse][b]Some [i]text[/i][/b][/noparse]
[single=att]More text[/single]
[multiple first=some second=more]cowbobs[/multiple]
LOL;

$str = '[a][i][/b][/i][/a]';
$str = '[a][b][/b][k][/a][/k]';
$str = '[block]a block[/block][b]Hey [block]a block[/block][/b][noparse][b]hey[/b][/noparse]';
$str = '[noparse ][b]sweden[/b  ][/noparse]';

$str = '[list]
[*]a
[*]b
[/list]';

$str = '[b][/c][/b]';

function cb(BBNode $n, BBCode $c)
{
  return $n->dumpChildren();
}

BBParser::$debug = true;

$parser = new BBParser;
$parser->parse($str);
$parser->tagsWithNoEnd[] = '*';

$allTypes = array('inline', 'block');

$bold = new BBCodeReplace('b', '<b>', '</b>', 'inline', array('inline'));
$italic = new BBCodeReplace('i', '<i>', '</i>', 'inline', array('inline'));
$underline = new BBCodeReplace('u', '<u>', '</u>', 'inline', array('inline'));
$block = new BBCodeReplace('block', '<div>', '</div>', 'block', $allTypes);
$callback = new BBCodeCallback('unfiltered', 'inline', $allTypes, 'cb', false, false);
$noparse = new BBCodeNoParse();
$notag = new BBCodeDefault();
$bbroot = new BBCodeRoot();

$notag->addContentTypes($allTypes);
$bbroot->addContentTypes($allTypes);

$dumper = new BBDumper();

$dumper->addHandlers(array($bold, $italic, $underline, $noparse, $block, $callback));
$dumper->setDefaultHandler($notag);
$dumper->setRootHandler($bbroot);

$node = $parser->tree();

$dumper->assignHandlers($node);

echo $node;

echo "\r\n";

?>
