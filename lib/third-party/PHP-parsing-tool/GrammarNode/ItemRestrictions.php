<?php

namespace ParserGenerator\GrammarNode;

class ItemRestrictions extends \ParserGenerator\GrammarNode\Decorator
{
    protected $condition;

    public function __construct($node, $condition)
    {
	    parent::__construct($node);
        $this->condition = $condition;
    }
	
	public function rparse($string, $fromIndex, $restrictedEnd) {
	    while ($newResult = $this->node->rparse($string, $fromIndex, $restrictedEnd)) {
		    //$toIndex = $newResult['offset'];
		    //echo "Checking [$string] $fromIndex : $toIndex gives " . ($this->condition->check($string, $fromIndex, $newResult['offset'], $newResult['node']) ? "true\n" : "false\n");
            if ($this->condition->check($string, $fromIndex, $newResult['offset'], $newResult['node'])) {
                return $newResult;
            }
            $restrictedEnd[$newResult['offset']] = $newResult['offset'];
        }

        return false;
	}
}