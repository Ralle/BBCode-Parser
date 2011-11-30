<?php

require_once __DIR__ . '/../BBParser.class.php';
require_once __DIR__ . '/../BBDumper.class.php';

/**
 * BBNode Test
 *
 * This class tests the BBNode class.
 *
 * @author Kristian Andersen
 * @package BBCode-Parser.Test
 */
class BBNodeTest extends PHPUnit_Framework_TestCase
{
	private $parser;
	private $dumper;
	
	/**
	 * Set up routine running before tests.
	 */
	public function SetUp()
	{
		$this->parser = new BBParser;
		$this->dumper = new BBDumper;
	}
	
	/**
	 * Tests that the add function inserts a child into the
	 * children array on a BBNode.
	 */
	public function testAddInsertsChild()
	{
		$root = new BBRoot;
		$child = new BBTag('b');
		
		$root->add($child);
		
		$this->assertCount(1, $root->children);
		$this->assertContains($child, $root->children);
	}
	
	/**
	 * Test that the add function inserts the child into the
	 * end of the children array on a BBNode.
	 */
	public function testAddInsertsInEnd()
	{
		$root = new BBRoot;
		
		$child = new BBTag('b');
		$root->add($child);
		
		$nextChild = new BBTag('u');
		$root->add($nextChild);
		
		$this->assertEquals($root->children[1]->tagName, 'u');
	}
	
	/**
	 * Tests that the remove function removes the object from
	 * the children array on a BBNode.
	 *
	 * @depends testAddInsertsChild
	 */
	public function testRemoveRemovesChild()
	{
			$root = new BBRoot;
			$child = new BBTag('b');

			$root->add($child);
			$root->remove($child);
			
			$this->assertEmpty($root->children);
	}
	
	/**
	 * Tests that the remove function doesn't change anything
	 * when trying to remove a node that is not in the children
	 * array on a BBNode.
	 */
	public function testRemoveDoesNotRemoveNonExistingChild()
	{
		$root = new BBRoot;
		$child = new BBTag('b');

		$root->add($child);
		$root->remove(new BBTag('u'));
		
		$this->assertContains($child, $root->children);
	}
	
	/**
	 * Tests that the prepend function inserts a child into the
	 * children array on a BBNode.
	 */
	public function testPrependInsertsChild()
	{
		$root = new BBRoot;
		$child = new BBTag('b');
		
		$root->prepend($child);
		
		$this->assertCount(1, $root->children);
		$this->assertContains($child, $root->children);
	}
	
	/**
	 * Tests that the prepend function inserts the child into
	 * the beginning of the children array on the BBNode.
	 */
	public function testPrependInsertsInBeginning()
	{
		$root = new BBRoot;
		
		$child = new BBTag('b');
		$root->add($child);
		
		$nextChild = new BBTag('u');
		$root->prepend($nextChild);
		
		$this->assertEquals($root->children[0]->tagName, 'u');
	}
	
	
}

?>