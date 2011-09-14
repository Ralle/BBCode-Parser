<?php

error_reporting(-1);

require_once __DIR__ . '/BBDatatypes.php';
require_once __DIR__ . '/BBParser.class.php';
require_once __DIR__ . '/BBDumper.class.php';
require_once __DIR__ . '/BBCode.class.php';
require_once __DIR__ . '/Bold.class.php';
require_once __DIR__ . '/NoTag.class.php';
require_once __DIR__ . '/NoParse.class.php';
require_once __DIR__ . '/Italic.class.php';

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

$str = <<<LOL
[noparse][b]Some [i]text[/i][/b][/noparse]
[single=att]More text[/single]
[multiple first=some second=more]cowbobs[/multiple]
LOL;

$str = '[a][/b][/a]';
$str = '[a][i][/b][/i][/a]';

$str = '[a][b][/b][k][/a][/k]';

echo $str, "\r\n";

BBParser::$debug = true;

$parser = new BBParser($str);
$parser->parse();
print_r($parser->tree());

$dumper = new BBDumper();
$dumper->addHandler(new Bold());
$dumper->addHandler(new NoParse());
$dumper->addHandler(new Italic());
$dumper->setDefaultHandler(new NoTag());

$d = $parser->dump($dumper);
echo $d;


echo "\r\n";

?>