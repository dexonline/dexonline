<?php

namespace ParserGenerator\Extension;

class WhiteCharactersContext extends \ParserGenerator\Extension\SequenceItem
{
    protected function getGrammarGrammarSequence()
    {
        return array(':/(\\\\s|whiteSpace|\\\\space|space|\\\\n|newLine|\\\\newline|\\\\t|\\\\tab|tab)/');
    }

    protected function _buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options)
    {
        $selector = (string)$sequenceItem;
        $negative = false;

        if (substr($selector, 0, 1) === '!') {
            $selector = substr($selector, 1);
            $negative = true;
        }

        switch ($selector) {
            case '\s':
            case 'whiteSpace':
                $char = null;
                break;
            case '\n':
            case '\newLine':
            case 'newLine':
                $char = "\n";
                break;
            case '\space':
            case 'space':
                $char = ' ';
                break;
            case '\t':
            case '\tab':
            case 'tab':
                $char = "\t";
                break;
        }

        if ($negative) {
            return new \ParserGenerator\GrammarNode\WhitespaceNegativeContextCheck($char);
        } else {
            return new \ParserGenerator\GrammarNode\WhitespaceContextCheck($char);
        }
    }
}

\ParserGenerator\GrammarParser::$defaultPlugins[] = new WhiteCharactersContext();