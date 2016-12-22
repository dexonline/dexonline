<?php

namespace ParserGenerator\GrammarNode;

class Regex extends \ParserGenerator\GrammarNode\BaseNode Implements \ParserGenerator\GrammarNode\LeafInterface
{
    public $lastMatch = -1;
    public $lastNMatch = -1;

    protected $givenRegex;
    protected $regex;
    protected $eatWhiteChars;
    protected $caseInsensitive;

    public function __construct($regex, $eatWhiteChars = false, $caseInsensitive = false)
    {
        $this->eatWhiteChars = $eatWhiteChars;
        $this->caseInsensitive = $caseInsensitive;
        $this->givenRegex = $regex;
        if (preg_match('/\/(.*)\/([A-Za-z]*)/s', $regex, $match)) {
            $regexBody = $match[1];
            $regexModifiers = $match[2];
            if (strpos($regexModifiers, 'i') === false && $caseInsensitive) {
                $regexModifiers .= 'i';
            }
            $this->regex = '/(' . $regexBody . ')?\s*/' . $regexModifiers;
        } else {
            throw new Exception ("Wrong regex format [$regex]");
        }
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        if (preg_match($this->regex, $string, $match, 0, $fromIndex)) {
            if (isset($match[1])) {
                $offset = strlen($match[$this->eatWhiteChars ? 0 : 1]) + $fromIndex;
                if (!isset($restrictedEnd[$offset])) {
                    $node = new \ParserGenerator\SyntaxTreeNode\Leaf($match[1], $this->eatWhiteChars ? substr($match[0], strlen($match[1])) : '');

                    if ($this->lastMatch < $fromIndex) {
                        $this->lastMatch = $fromIndex;
                    }
                    return array('node' => $node, 'offset' => $offset);
                }
            }
        }

        if ($this->lastNMatch < $fromIndex) {
            $this->lastNMatch = $fromIndex;
        }

        return false;
    }

    public function getRegex()
    {
        return $this->givenRegex;
    }

    public function __toString()
    {
        return $this->givenRegex;
    }
}