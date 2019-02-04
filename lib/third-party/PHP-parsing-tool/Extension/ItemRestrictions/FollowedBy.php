<?php

namespace ParserGenerator\Extension\ItemRestrictions;

class FollowedBy implements \ParserGenerator\Extension\ItemRestrictions\ItemRestrictionInterface
{
    protected $grammarNode;
	
	public function __construct($grammarNode)
	{
	    $this->grammarNode = $grammarNode;
	}
	
	public function check($string, $fromIndex, $toIndex, $node)
	{
	    return (bool) $this->grammarNode->rparse($string, $toIndex, array()); 
	}
}