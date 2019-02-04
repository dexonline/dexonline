<?php

namespace ParserGenerator\Extension;

abstract class SequenceItem extends \ParserGenerator\Extension\Base
{
    protected $detailTypeSeparator = '/';

    public function extendGrammar($grammarGrammar)
    {
        $sequence = $this->getGrammarGrammarSequence();
        if ($this->isArrayOfSequences($sequence)) {
            foreach ($sequence as $index => $seq) {
                $grammarGrammar['sequenceItem'][$this->getDetailType($index)] = $seq;
            }
        } else {
            $grammarGrammar['sequenceItem'][$this->getDetailType(1)] = $this->getGrammarGrammarSequence();
        }

        return $grammarGrammar;
    }

    protected function isArrayOfSequences($arr)
    {
        foreach ($arr as $arrayItem) {
            if (is_array($arrayItem)) {
                return true;
            } else {
                return false;
            }
        }
    }

    protected function getDetailType($index = 1)
    {
        if ($index === null) {
            return get_class($this);
        } else {
            return $this->getDetailType(null) . $this->detailTypeSeparator . $index;
        }
    }

    protected function getDetailTypeIndex($sequenceItem)
    {
        $detailType = explode($this->detailTypeSeparator, $sequenceItem->getDetailType());

        if ($this->getDetailType(null) === $detailType[0] && isset($detailType[1])) {
            return $detailType[1];
        } else {
            return null;
        }
    }

    public function buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options)
    {
        if ($this->getDetailTypeIndex($sequenceItem) !== null) {
            return $this->_buildSequenceItem($grammar, $sequenceItem, $grammarParser, $options);
        } else {
            return false;
        }
    }

    abstract protected function getGrammarGrammarSequence();

    abstract protected function _buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options);
}