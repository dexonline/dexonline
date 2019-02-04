<?php

namespace ParserGenerator\GrammarNode;

class BranchStringCondition extends \ParserGenerator\GrammarNode\BranchExtraCondition
{
    private $conditionStrings;

    public function __construct($node, $conditionStrings)
    {
        parent::__construct($node);
        $this->setConditionString($conditionStrings);
    }

    public function setConditionString($conditionStrings)
    {
        $this->conditionStrings = $conditionStrings;
        $this->_functions = array();

        foreach ($conditionStrings as $detailType => $conditionString) {
            $this->_functions[$detailType] = create_function('$string,$fromIndex,$toIndex,$node,$s', 'return ' . $conditionString . ';');
        }
    }

    public function check($string, $fromIndex, $toIndex, $node)
    {
        $fn = isset($this->_functions[$node->getDetailType()]) ? $this->_functions[$node->getDetailType()] : null;

        if (isset($fn)) {
            /** @var $fn \Closure */
            return $fn($string, $fromIndex, $toIndex, $node, $node->getSubnodes());
        } else {
            return true;
        }
    }
}