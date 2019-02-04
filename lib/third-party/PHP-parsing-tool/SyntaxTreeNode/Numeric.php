<?php

namespace ParserGenerator\SyntaxTreeNode;

class Numeric extends \ParserGenerator\SyntaxTreeNode\Leaf
{
    protected $base;

    public function __construct($content, $base = 10)
    {
        parent::__construct($content);
        $this->base = $base;
    }

    public function getBase()
    {
        return $this->base;
    }

    public function getFixedCharacters()
    {
        $str = str_replace(array('0x', '0b'), array('', ''), $this->content);

        if (substr($str, 0, 1) === '-') {
            $str = substr($str, 1);
        }

        if ($this->base === 8) {
            $str = substr($str, 1);
        }

        if (substr($str, 0, 1) === '0' && strlen($str) > 1) {
            return strlen($str);
        } else {
            return 0;
        }
    }

    public function getValue()
    {
        $str = str_replace(array('0x', '0b'), array('', ''), $this->content);
        return intval($str, $this->base);
    }
}