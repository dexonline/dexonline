<?php

namespace ParserGenerator\SyntaxTreeNode;

class PredefinedString extends \ParserGenerator\SyntaxTreeNode\Leaf
{
    protected $escapedByRepetition;

    public function __construct($content, $afterContent = '', $escapedByRepetition = false)
    {
        parent::__construct($content, $afterContent);
        $this->escapedByRepetition = $escapedByRepetition;
    }

    public function getValue()
    {
        if ($this->escapedByRepetition) {
            $startChar = substr($this->content, 0, 1);
            return str_replace($startChar . $startChar, $startChar, substr($this->content, 1, -1));
        } else {
            return stripcslashes(substr($this->content, 1, -1));
        }
    }

    public function getPHPValue()
    {
        if (substr($this->content, 0, 1) === '"') {
            return stripcslashes(substr(str_replace("\\'", "\\\\'", $this->content), 1, -1));
        } else {
            return str_replace(array('\\\\', '\\\''), array('\\', '\''), substr($this->content, 1, -1));
        }
    }
}