<?php

namespace ParserGenerator\Extension\ItemRestrictions;

class Contain implements \ParserGenerator\Extension\ItemRestrictions\ItemRestrictionInterface
{
    protected $grammarNode;
	
	public function __construct($grammarNode)
	{
	    $this->grammarNode = $grammarNode;
	}
	
	public function check($string, $fromIndex, $toIndex, $node)
	{
	    for ($currentIndex = $fromIndex; $currentIndex < $toIndex; $currentIndex++) {
		    $restrictedEnds = array();
			while (true) {
			    $parsedNode = $this->grammarNode->rparse($string, $currentIndex, $restrictedEnds);
				
				if (!$parsedNode) {
				    break;
				}
				
				$offset = $parsedNode['offset'] - strlen($parsedNode['node']->getRightLeaf()->getAfterContent());
				if ($offset > $toIndex) {
                    $restrictedEnds[$offset] = $offset;
                } else {
				    return true;
				}
			}
		}
		
		return false;
	}
}