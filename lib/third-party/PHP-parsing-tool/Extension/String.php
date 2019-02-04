<?php

namespace ParserGenerator\Extension;

// cata - replace String with PString to avoid errors in PHP 7.
class PString extends \ParserGenerator\Extension\SequenceItem
{
    protected function getGrammarGrammarSequence()
    {
        return array(
            array('string'),
            array('string/', ':/(apostrophe|simple|quotation|default)/')
        );
    }

    protected function _buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options)
    {
        $type = $sequenceItem->getSubnode(1) ? (string) $sequenceItem->getSubnode(1) : 'default';

        switch ($type) {
            case "default":
                return new \ParserGenerator\GrammarNode\PredefinedString(!empty($options['ignoreWhitespaces']), array("'", '"'));

            case "apostrophe":
                return new \ParserGenerator\GrammarNode\PredefinedString(!empty($options['ignoreWhitespaces']), array("'"));

            case "quotation":
                return new \ParserGenerator\GrammarNode\PredefinedString(!empty($options['ignoreWhitespaces']), array('"'));

            case "simple":
                return new \ParserGenerator\GrammarNode\PredefinedSimpleString(!empty($options['ignoreWhitespaces']));
        }
    }
}

\ParserGenerator\GrammarParser::$defaultPlugins[] = new PString();
