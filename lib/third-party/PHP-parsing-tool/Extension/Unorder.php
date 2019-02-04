<?php

namespace ParserGenerator\Extension;

class Unorder extends \ParserGenerator\Extension\SequenceItem
{
    protected $seqName = 'unorderSequence';

    public function extendGrammar($grammarGrammar)
    {
        $grammarGrammar[$this->seqName] = array(
            'nest' => array(':/[?*+]?/', ':sequenceItem', ',', (':' . $this->seqName)),
            'last' => array(':/[?*+]?/', ':sequenceItem')
        );

        return parent::extendGrammar($grammarGrammar);
    }

    protected function getGrammarGrammarSequence()
    {
        return array(array('unorder(', ':sequenceItem', ',', (':' . $this->seqName), ')'));
    }

    protected function _buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options)
    {
        $separator = $this->buildInternalSequence($grammar, $sequenceItem->getSubnode(1), $grammarParser, $options);
        $node = new \ParserGenerator\GrammarNode\Unorder($separator);
        $sequenceNode = $sequenceItem->getSubnode(3);

        while ($sequenceNode) {
            $n = $this->buildInternalSequence($grammar, $sequenceNode->getSubnode(1), $grammarParser, $options);
            $node->addChoice($n, (string) $sequenceNode->getSubnode(0));
            $sequenceNode = ($sequenceNode->getDetailType() == 'last') ? null : $sequenceNode->getSubnode(3);
        }


        if (isset($options['parser'])) {
            $node->setParser($options['parser']);
        }

        $grammar[$node->getTmpNodeName()] = $node;

        return $node;
    }

    private function buildInternalSequence(&$grammar, $sequence, $grammarParser, $options)
    {
        $choice = array();

        foreach($sequence->findAll('sequenceItem') as $sequenceItem)
        {
            $choice[] = $grammarParser->buildSequenceItem($grammar, $sequenceItem, $options);
        }

        return (count($choice) === 1) ? $choice[0] : $choice;
    }
}

\ParserGenerator\GrammarParser::$defaultPlugins[] = new Unorder();