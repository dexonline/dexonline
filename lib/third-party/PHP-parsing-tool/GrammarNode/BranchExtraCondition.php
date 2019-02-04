<?php

namespace ParserGenerator\GrammarNode;

abstract class BranchExtraCondition Extends \ParserGenerator\GrammarNode\BranchDecorator
{
    public function rparse($string, $fromIndex, $restrictedEnd)
    {
        while ($newResult = $this->node->rparse($string, $fromIndex, $restrictedEnd)) {
            if ($this->check($string, $fromIndex, $newResult['offset'], $newResult['node'])) {
                return $newResult;
            }
            $restrictedEnd[$newResult['offset']] = $newResult['offset'];
        }

        return false;
    }

    abstract public function check($string, $fromIndex, $toIndex, $node);
}