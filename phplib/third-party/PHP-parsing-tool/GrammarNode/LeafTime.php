<?php

namespace ParserGenerator\GrammarNode;


class LeafTime extends \ParserGenerator\GrammarNode\BaseNode implements \ParserGenerator\GrammarNode\LeafInterface
{
    public $lastMatch = -1;
    public $lastNMatch = -1;

    protected $format;
    protected $eatWhiteChars;
    protected $maxLength;

    public function __construct($format, $eatWhiteChars)
    {
        $this->format = $format;
        $this->maxLength = strlen($this->format) + 14;
        $this->eatWhiteChars = $eatWhiteChars;
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        $s = substr($string, $fromIndex, $this->maxLength);
        $data = date_parse_from_format($this->format, $s);

        if (!empty($data['errors'])) {
            foreach($data['errors'] as $key => $_) break;
            $s = substr($s, 0, $key);
            $data = date_parse_from_format($this->format, $s);
            if (!empty($data['errors'])) {
                return false;
            }
        }

        $end = $fromIndex + strlen($s);
        if ($this->eatWhiteChars) {
            if (preg_match('/\s*/', $string, $match, 0, $end)) {
                $whiteChars = $match[0];
            }
            $end += strlen($whiteChars);
        } else {
            $whiteChars = '';
        }

        if (isset($restrictedEnd[$end])) {
            if ($this->lastNMatch < $fromIndex) {
                $this->lastNMatch = $fromIndex;
            }

            return false;
        }

        $node = new \ParserGenerator\SyntaxTreeNode\LeafTime($s, $whiteChars, $data);

        if ($this->lastMatch < $fromIndex) {
            $this->lastMatch = $fromIndex;
        }

        return array('node' => $node, 'offset' => $end);
    }
} 