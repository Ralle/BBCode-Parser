<?php

require_once __DIR__ . '/../BBParser.class.php';
require_once __DIR__ . '/../BBDumper.class.php';

class DefaultsTest extends PHPUnit_Framework_TestCase {
	private $parser;
	private $dumper;
	
	public function SetUp() {
		$this->parser = new BBParser;
		$this->dumper = new BBDumper;
	}
	
	public function testParseReturnsEmptyRoot() {
		$result = $this->parser->parse('');
		
		$this->assertTrue($result instanceof BBRoot);
		$this->assertEmpty($result->children);
	}
	
	/**
	 * Test that the following string parses:
	 * [b][/b]
	 *
	 * Parse must return a BBRoot containing a single BBTag with
	 * the tag name 'b' and the tag must have no children.
	 */
	public function testEmptyBoldTagParses(){
		$str = "[b][/b]";
		$result = $this->parser->parse($str);
		
		$this->assertCount(1, $result->children);
		$this->assertTrue($result->children[0] instanceof BBTag);
		$this->assertEquals($result->children[0]->tagName, 'b');
		$this->assertEmpty($result->children[0]->children);
	}
	
	/**
	 * Test that the following string parses:
	 * [b]some text[/b]
	 *
	 * Parse must return a tree with the b tag containing a single
	 * BBText tag with the text 'some text'.
	 *
	 * @depends testEmptyBoldTagParses
	 */
	public function testBoldTagWithTextParses() {
		$str = "[b]some text[/b]";
		$result = $this->parser->parse($str);
		
		$btag = $result->children[0];
		$this->assertCount(1, $btag->children);
		
		$textTag = $btag->children[0];
		$this->assertTrue($textTag instanceof BBText);
		$this->assertEquals($textTag->text, 'some text');
		$this->assertEmpty($textTag->children);
	}
}

?>