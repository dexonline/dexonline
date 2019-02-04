<?php

namespace ParserGenerator\GrammarNode;

abstract class BaseNode implements \ParserGenerator\GrammarNode\NodeInterface
{
    // this function SHOULD be abstract but PHP force to implement interface method -
    // another words disallow to make this function abstract
    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
    }
}