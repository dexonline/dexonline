<?php

namespace ParserGenerator\Extension\ItemRestrictions;

class Is implements \ParserGenerator\Extension\ItemRestrictions\ItemRestrictionInterface
{
    protected $grammarNode;
	
	public function __construct($grammarNode)
	{
	    $this->grammarNode = $grammarNode;
	}
	
	public function check($string, $fromIndex, $toIndex, $node)
	{
		$restrictedEnds = array();
		while (true) {
			$parsedNode = $this->grammarNode->rparse($string, $fromIndex, $restrictedEnds);
				
			if (!$parsedNode) {
			    return false;
			} elseif ($parsedNode['offset'] === $toIndex) {
			    return true;
			} else {
			    $restrictedEnds[$parsedNode['offset']] = $parsedNode['offset'];
			}
	    }
	}
}