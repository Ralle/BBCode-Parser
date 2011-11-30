<?php

require_once __DIR__ . '/../BBParser.class.php';
require_once __DIR__ . '/../BBDumper.class.php';

class BBParserTest extends PHPUnit_Framework_TestCase {
	private $parser;
	private $dumper;
	
	public function SetUp() {
		$this->parser->debug = true;
		$this->parser = new BBParser;
		$this->dumper = new BBDumper;
	}
	
	public function testParseReturnsEmptyRoot() {
		$result = $this->parser->parse('');
		
		$this->assertTrue($result instanceof BBRoot);
		$this->assertEmpty($result->children, 'BBRoot has children');
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
		
		$this->assertCount(1, $result->children, 'BBRoot does not have 1 child');
		$this->assertTrue($result->children[0] instanceof BBTag, 'BBRoot does not contain a BBTag');
		$this->assertEquals($result->children[0]->tagName, 'b', 'The BBTag tagName is not b');
		$this->assertEmpty($result->children[0]->children, 'The BBTags has children');
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
		$this->assertCount(1, $btag->children, 'The BBTag not not contain 1 child');
		
		$textTag = $btag->children[0];
		$this->assertTrue($textTag instanceof BBText, 'The child of BBTag is not a BBText tag');
		$this->assertEquals($textTag->text, 'some text', 'The text in BBText is not \'some text\'');
		$this->assertEmpty($textTag->children, 'The BBText contains children');
	}
	
	/**
	 * Test that the following string:
	 * [b][u][/b][/u]
	 * parse into:
	 * <b>[u]</b>[/u]
	 *
	 * Parse must return a root with a BBTag (b) that contains a
	 * BBTag (u) that has no end tag. The root must also contain a
	 * BBEndTag (u).
	 */
	public function testBadOrderedEndTagsWillBeFixed() {
		$str = "[b][u][/b][/u]";
		$result = $this->parser->parse($str);
		
		$this->assertCount(2, $result->children);
		
		$this->assertEquals(get_class($result->children[0]), 'BBTag');
		$this->assertEquals($result->children[0]->tagName, 'b');
		
		$inner_u = $result->children[0]->children[0];
		$this->assertEquals(get_class($inner_u), 'BBTag');
		$this->assertEquals($inner_u->tagName, 'u');
		$this->assertFalse($inner_u->hasEndTag);
		
		$this->assertEquals(get_class($result->children[1]), 'BBEndTag');
		$this->assertEquals($result->children[1]->tagName, 'u');	
	}
}

?>