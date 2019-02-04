<?php

namespace ParserGenerator\GrammarNode;

class Decorator implements \ParserGenerator\GrammarNode\NodeInterface
{
    protected $node;

    public function __construct($node)
    {
        $this->node = $node;
    }

    public function rparse($string, $fromIndex, $restrictedEnd)
    {
        return $this->node->rparse($string, $fromIndex, $restrictedEnd);
    }

    public static function undecorate($node) {
        while($node instanceof self) {
            $node = $node->node;
        }

        return $node;
    }

    public function __toString() {
        return (string) $this->node;
    }

    public function getDecoratedNode() {
        return $this->node;
    }
}