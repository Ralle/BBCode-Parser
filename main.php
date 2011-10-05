<?php

error_reporting(-1);

require_once __DIR__ . '/BBDatatypes.php';
require_once __DIR__ . '/BBParser.class.php';
// require_once __DIR__ . '/BBDumper.class.php';
require_once __DIR__ . '/BBCode.class.php';
require_once __DIR__ . '/BBCodeReplace.class.php';
require_once __DIR__ . '/BBCodeDefault.class.php';
require_once __DIR__ . '/BBCodeNoParse.class.php';
require_once __DIR__ . '/BBCodeRoot.class.php';
require_once __DIR__ . '/BBCodeQuote.class.php';
require_once __DIR__ . '/BBCodeCode.class.php';

$str = <<<BBCODE
[quote=Ralle]I am not very happy with the way you behave in the chat room.[/quote]
Hello Ralle
I will [i]try[/i] to be better.
Here is some code:
[code=php]<?php
echo '[b]Lolcaps[/b]';
?>
[/quote]
BBCODE;

/*$str = <<<LOL
[noparse][b]Some [i]text[/i][/b][/noparse]
[single=att]More text[/single]
[multiple first=some second=more]cowbobs[/multiple]
LOL;*/

//$str = '[a][/b][/a]';
//$str = '[a][i][/b][/i][/a]';
//$str = '[a][b][/b][k][/a][/k]';
//$str = '[block]a block[/block][b]Hey [block]a block[/block][/b][noparse][b]hey[/b][/noparse]';
//$str = '[noparse ][b]sweden[/b  ][/noparse]';

BBParser::$debug = false;

$parser = new BBParser($str);
$parser->parse();

$bold = new BBCodeReplace('b', '<b>', '</b>', 'inline');
$italic = new BBCodeReplace('i', '<i>', '</i>', 'inline');
$underline = new BBCodeReplace('u', '<u>', '</u>', 'inline');
$block = new BBCodeReplace('block', '<div>', '</div>', 'block');
$noparse = new BBCodeNoParse();
$notag = new BBCodeDefault();
$bbroot = new BBCodeRoot();
$quote = new BBCodeQuote();
$code = new BBCodeCode();

$allTypes = array('inline', 'block');

$bold->addContentType('inline');
$italic->addContentType('inline');
$underline->addContentType('inline');

$notag->addContentType($allTypes);
$bbroot->addContentTypes($allTypes);
$quote->addContentTypes($allTypes);

BBCode::addHandlers(array($bold, $italic, $underline, $noparse, $block, $quote, $code));
BBCode::setDefaultHandler($notag);
BBCode::setRootHandler($bbroot);

$node = $parser->tree();
$node->parentCanContain = $allTypes;

echo 'Input: ', $str, "\r\n";
echo 'Output: ', $node->toString(), "\r\n";

echo "\r\n";

?>
