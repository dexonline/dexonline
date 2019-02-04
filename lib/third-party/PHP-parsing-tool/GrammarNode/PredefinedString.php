<?php

namespace ParserGenerator\GrammarNode;

class PredefinedString extends \ParserGenerator\GrammarNode\BaseNode Implements \ParserGenerator\GrammarNode\LeafInterface
{
    public $lastMatch = -1;
    public $lastNMatch = -1;

    protected $eatWhiteChars;
    protected $startCharacters;

    public function __construct($eatWhiteChars = false, $startCharacters = array("'", '"'))
    {
        $this->eatWhiteChars = $eatWhiteChars;
        $this->startCharacters = $startCharacters;
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        $stChar = substr($string, $fromIndex, 1);
        if (in_array($stChar, $this->startCharacters)) {
            $offset = $fromIndex + 1;
            while ($nextPos = strpos($string, $stChar, $offset)) {
                $i = 1;
                while (substr($string, $nextPos - $i++, 1) === '\\') {
                };
                $offset = $nextPos + 1;
                if ($i % 2 === 0) {
                    $val = substr($string, $fromIndex, $nextPos - $fromIndex + 1);
                    if ($this->eatWhiteChars) {
                        preg_match('/\s*/', $string, $match, 0, $nextPos + 1);
                        $nextPos += strlen($match[0]);
                    }
                    if (isset($restrictedEnd[$nextPos + 1])) {

                        return false;
                    } else {
                        $node = new \ParserGenerator\SyntaxTreeNode\PredefinedString($val, $this->eatWhiteChars ? $match[0] : '');

                        return array('node' => $node, 'offset' => $nextPos + 1);
                    }
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