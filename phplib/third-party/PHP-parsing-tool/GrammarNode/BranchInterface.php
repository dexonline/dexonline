<?php

namespace ParserGenerator\GrammarNode;

interface BranchInterface extends \ParserGenerator\GrammarNode\NodeInterface
{
    public function setParser($parser);

    public function getParser();

    public function setNode($node);

    public function getNode();

    public function getNodeName();
}