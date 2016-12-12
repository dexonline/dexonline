<?php

namespace ParserGenerator\Extension;

class TextNode extends \ParserGenerator\Extension\SequenceItem
{
    protected function getGrammarGrammarSequence()
    {
        return array(':string');
    }

    protected function _buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options)
    {
        if (!empty($options['caseInsensitive'])) {
		    $regex = \ParserGenerator\RegexUtil::buildRegexFromString((string)$sequenceItem->getSubnode(0)->getValue());
            return new \ParserGenerator\GrammarNode\Regex($regex, !empty($options['ignoreWhitespaces']), !empty($options['caseInsensitive']));
		} elseif (empty($options['ignoreWhitespaces'])) {
            return new \ParserGenerator\GrammarNode\Text($sequenceItem->getSubnode(0)->getValue());
        } else {
            return new \ParserGenerator\GrammarNode\TextS($sequenceItem->getSubnode(0)->getValue());
        }
    }
}

\ParserGenerator\GrammarParser::$defaultPlugins[] = new TextNode();