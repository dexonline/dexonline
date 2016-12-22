<?php

namespace ParserGenerator\GrammarNode;

class Unorder extends \ParserGenerator\GrammarNode\BaseNode
{
    const MAX = 1000000;

    protected $separator;
    protected $resultType;
    protected $tmpNodeName;
    protected $choices = array();
    protected $mod = array();

    public function __construct($separator, $resultType = 'unorder')
    {
        $this->separator = $separator;
        $this->resultType = $resultType;
        $this->tmpNodeName = '&unorder/' . spl_object_hash($this);
    }

    public function addChoice($choice, $mod) {
        $this->choices[] = $choice;
        $this->mod[] = $mod;
    }

    protected function internalParse($string, $fromIndex, $restrictedEnd, $required, $left) {
        foreach($this->choices as $key => $choice) {
            if ($left[$key] > 0) {
                $choiceRestrictedEnd = array();
                $isRequired = !empty($required[$key]);
                unset($required[$key]);
                $left[$key]--;
                while($choiceResult = $choice->rparse($string, $fromIndex, $choiceRestrictedEnd)) {
                    $afterChoiceIndex = $choiceResult['offset'];
                    $separatorRestrictedEnd = array();
                    while($separatorResult = $this->separator->rparse($string, $afterChoiceIndex, $separatorRestrictedEnd)) {
                        $afterSeparatorIndex = $separatorResult['offset'];
                        if ($next = $this->internalParse($string, $afterSeparatorIndex, $restrictedEnd, $required, $left)) {
                            array_push($next['nodes'], $separatorResult['node'], $choiceResult['node']);
                            return $next;
                        }

                        $separatorRestrictedEnd[$afterSeparatorIndex] = $afterSeparatorIndex;
                    }


                    $choiceRestrictedEnd[$afterChoiceIndex] = $afterChoiceIndex;
                }

                if (empty($required)) {
                    $choiceResult = $choice->rparse($string, $fromIndex, $restrictedEnd);
                    if ($choiceResult) {
                        return array('nodes' => array($choiceResult['node']), 'offset' => $choiceResult['offset']);
                    }
                }

                $left[$key]++;
                if ($isRequired) {
                    $required[$key] = true;
                }
            }
        }

        return false;
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        $required = array();
        foreach($this->choices as $key => $choice) {
            $mod = $this->mod[$key];
            $left[$key] = ($mod == '*' || $mod == '+') ? static::MAX : 1;
            if ($mod == '' || $mod == '1' || $mod == '+') {
                $required[$key] = 1;
            }
        }

        if ($result = $this->internalParse($string, $fromIndex, $restrictedEnd, $required, $left)) {
            $node = new \ParserGenerator\SyntaxTreeNode\Series($this->resultType, '', array_reverse($result['nodes']), true);
            return array('node' => $node, 'offset' => $result['offset']);
        }

        return false;
    }

    public function getTmpNodeName()
    {
        return $this->tmpNodeName;
    }

    public function setParser($parser)
    {
        $this->parser = $parser;
        foreach($this->choices as $choice) {
            if (method_exists($choice, 'setParser')) {
                $choice->setParser($parser);
            }
        }
    }

    public function __toString() {
        return "unorder";
        return '(' . implode(' | ', $this->choices) . ')';
    }
}