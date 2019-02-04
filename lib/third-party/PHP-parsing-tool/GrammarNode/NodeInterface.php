<?php

namespace ParserGenerator\GrammarNode;

interface NodeInterface
{
    public function rparse($string, $fromIndex, $restrictedEnd);
}