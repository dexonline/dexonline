<?php

namespace ParserGenerator\Extension;

interface ExtensionInterface
{
    function extendGrammar($grammarGrammar);

    function modifyBranches($grammar, $parsedGrammar, $grammarParser, $options);

    function createGrammarBranch($grammar, $grammarBranch, $grammarParser, $options);

    function fillGrammarBranch($grammar, $grammarBranch, $grammarParser, $options);

    function buildSequenceItem(&$grammar, $sequenceItem, $grammarParser, $options);

    function buildSequence($grammar, $rule, $grammarParser, $options);
}