<?php

namespace ParserGenerator\Extension;

class Lookahead extends \ParserGenerator\Extension\SequenceItem
{
    protected function getGrammarGrammarSequence()
    {
        $noWhiteChar = new \ParserGenerator\GrammarNode\WhitespaceNegativeContextCheck(null);
        $whiteChar = new \ParserGenerator\GrammarNode\WhitespaceContextCheck(null);
        $operator = ':/[!?]/';

        return array(
            array($operator, $noWhiteChar, ':sequenceItem', $whiteChar, ':sequenceItem'),
            array(':sequenceItem', $whiteChar, $operator, $noWhiteChar, ':sequenceItem'),
            array($operator, $noWhiteChar, ':sequenceItem'),
        );
    }

    protected function _buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options)
    {
        switch ($this->getDetailTypeIndex($sequenceItem)) {
            case 0:
                $mainNode = $grammarParser->buildSequenceItem($grammar, $sequenceItem->getSubnode(4), $options);
                $lookaheadNode = $grammarParser->buildSequenceItem($grammar, $sequenceItem->getSubnode(2), $options);
                $operator = (string)$sequenceItem->getSubnode(0);
                $before = true;

                break;
            case 1:
                $mainNode = $grammarParser->buildSequenceItem($grammar, $sequenceItem->getSubnode(0), $options);
                $lookaheadNode = $grammarParser->buildSequenceItem($grammar, $sequenceItem->getSubnode(4), $options);
                $operator = (string)$sequenceItem->getSubnode(2);
                $before = false;

                break;
            case 2:
                $mainNode = null;
                $lookaheadNode = $grammarParser->buildSequenceItem($grammar, $sequenceItem->getSubnode(2), $options);
                $operator = (string)$sequenceItem->getSubnode(0);
                $before = null;

                break;

            default:
                throw new \Exception('that was unexpected');
        }

        return new \ParserGenerator\GrammarNode\Lookahead($lookaheadNode, $mainNode, $before, $operator == '?');
    }
}

\ParserGenerator\GrammarParser::$defaultPlugins[] = new Lookahead();