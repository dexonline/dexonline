<?php
/**
 * Created by PhpStorm.
 * User: Rafał
 * Date: 01.05.14
 * Time: 10:58
 */

namespace ParserGenerator\Extension;

use \ParserGenerator\GrammarNode\LeafTime;

class Time extends \ParserGenerator\Extension\SequenceItem
{
    protected function getGrammarGrammarSequence()
    {
        return array(
            array('time(', ':/[^)]+/', ')')
        );
    }

    protected function _buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options)
    {
        return new LeafTime((string) $sequenceItem->getSubnode(1), !empty($options['ignoreWhitespaces']));
    }
}

\ParserGenerator\GrammarParser::$defaultPlugins[] = new Time();