<?php

namespace ParserGenerator\GrammarNode;

class PredefinedSimpleString extends \ParserGenerator\GrammarNode\BaseNode Implements \ParserGenerator\GrammarNode\LeafInterface
{
    public $lastMatch = -1;
    public $lastNMatch = -1;

    protected $eatWhiteChars;
    protected $startCharacters;
    protected $regex = '/("([^"]|"")*")?/';

    public function __construct($eatWhiteChars = false)
    {
        $this->eatWhiteChars = $eatWhiteChars;
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        if (preg_match($this->regex, $string, $match, 0, $fromIndex)) {
            if (isset($match[1])) {
                $offset = strlen($match[$this->eatWhiteChars ? 0 : 1]) + $fromIndex;
                if (!isset($restrictedEnd[$offset])) {
                    $node = new \ParserGenerator\SyntaxTreeNode\PredefinedString($match[1], '', true);
                    $node->setAfterContent($this->eatWhiteChars ? substr($match[0], strlen($match[1])) : '');
                    return array('node' => $node, 'offset' => $offset);
                }
            }
        }

        return false;
    }

    public function __toString()
    {
        return "string";
    }
}