<?php

namespace ParserGenerator\Extension\ItemRestrictions;

class ItemRestrictionAnd implements \ParserGenerator\Extension\ItemRestrictions\ItemRestrictionInterface
{
    protected $children;
	
	public function __construct($children)
	{
	    $this->children = $children;
	}
	
	public function check($string, $fromIndex, $toIndex, $node)
	{
	    foreach($this->children as $child) {
		    if (!$child->check($string, $fromIndex, $toIndex, $node)) {
			    return false;
			}
		}
		
		return true;
	}
}