<?php

namespace ParserGenerator\GrammarNode;

class WhitespaceNegativeContextCheck extends \ParserGenerator\GrammarNode\BaseNode
{
    protected $char;

    /* this schoul be const but PHP don't accept array as const */
    static protected $whiteCharacters = array(" ", "\n", "\t", "\r");

    public function __construct($char)
    {
        $this->char = $char;
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        if (!isset($restrictedEnd[$fromIndex])) {
            $index = $fromIndex;
            while (--$index >= 0 && in_array(substr($string, $index, 1), self::$whiteCharacters, true)) {
                if ($this->char === null || substr($string, $index, 1) === $this->char) {
                    return false;
                }
            }

            return array('node' => new \ParserGenerator\SyntaxTreeNode\Leaf(''), 'offset' => $fromIndex);
        }

        return false;
    }
}