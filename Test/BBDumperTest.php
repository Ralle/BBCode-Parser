<?php
  
require_once __DIR__ . '/../BBParser.class.php';
require_once __DIR__ . '/../BBDumper.class.php';

class BBParserTest extends PHPUnit_Framework_TestCase
{
	private $parser;
	private $dumper;
	
	public function SetUp()
	{
		$this->parser = new BBParser;
		$this->dumper = new BBDumper;
    
    $default = new BBCodeDefault;
    $root = new BBCodeRoot;
    $root->addContentType('inline');
    
    $this->dumper->setDefaultHandler($default);
    $this->dumper->setRootHandler($root);
	}
  
  /**
   * Test that a chain of tags that cannot contain tags of the same kind does not parse
  */
  public function testTagChain()
  {
    $repl = new BBCodeReplace('b', '<b>', '</b>', 'inline', array());
    $this->dumper->addHandler($repl);
    $str = '[b][b][b]Test[/b][/b][/b]';
    $result = $this->parser->parse($str);
    $parsed = $this->dumper->dump($result);
    
    $this->assertEquals('<b>[b][b]Test[/b][/b]</b>', $parsed, 'The inner tags are parsed while they should not be');
  }

}

?>