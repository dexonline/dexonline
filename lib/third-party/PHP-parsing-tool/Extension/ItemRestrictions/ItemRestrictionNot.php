<?php

namespace ParserGenerator\Extension\ItemRestrictions;

class ItemRestrictionNot implements \ParserGenerator\Extension\ItemRestrictions\ItemRestrictionInterface
{
    protected $child;
	
	public function __construct($child)
	{
	    $this->child = $child;
	}
	
	public function check($string, $fromIndex, $toIndex, $node)
	{
	    return !$this->child->check($string, $fromIndex, $toIndex, $node);
	}
}