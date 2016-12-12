<?php

namespace ParserGenerator\Extension\ItemRestrictions;

class ItemRestrictionOr implements \ParserGenerator\Extension\ItemRestrictions\ItemRestrictionInterface
{
    protected $children;
	
	public function __construct($children)
	{
	    $this->children = $children;
	}
	
	public function check($string, $fromIndex, $toIndex, $node)
	{
	    foreach($this->children as $child) {
		    if ($child->check($string, $fromIndex, $toIndex, $node)) {
			    return true;
			}
		}
		
		return false;
	}
}