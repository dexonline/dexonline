<?php

namespace ParserGenerator\SyntaxTreeNode;

class Leaf extends \ParserGenerator\SyntaxTreeNode\Base
{
    protected $content;
    protected $afterContent;

    public function __construct($content, $afterContent = '')
    {
        $this->content = $content;
        $this->afterContent = $afterContent;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($newValue) {
        $this->content = $newValue;
        return $this;
    }

    public function dump($maxNestLevel = -1) {
        return $this->content;
    }

    public function toString($mode = \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_NO_WHITESPACES)
    {
        switch ($mode) {
            case \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_NO_WHITESPACES:
                return $this->content;
            case \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_ORIGINAL:
                return $this->content . $this->afterContent;
            case \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_REDUCED_WHITESPACES:
                $afterContent = '';
                if (strlen($this->afterContent)) {
                    $afterContent = ' ';
                }
                if (strpos($this->afterContent, "\t") !== false) {
                    $afterContent = "\t";
                }
                if (strpos($this->afterContent, "\n") !== false) {
                    $afterContent = "\n";
                }

                return $this->content . $afterContent;
        }
    }

    public function __toString()
    {
        return $this->content;
    }

    public function compare($anotherNode, $compareOptions = \ParserGenerator\SyntaxTreeNode\Base::COMPARE_DEFAULT)
    {
        if (!($anotherNode instanceof \ParserGenerator\SyntaxTreeNode\Leaf)) {
            return false;
        }

        if (($compareOptions & self::COMPARE_LEAF) && $this->content !== $anotherNode->content) {
            return false;
        }

        return true;
    }

    public function diff($anotherNode, $returnAsPair = true)
    {
        if ($this->content === $anotherNode->content) {
            return array();
        } else {
            if ($returnAsPair) {
                return array(array($this, $anotherNode));
            } else {
                return array($this);
            }
        }
    }

    public function getLeftLeaf()
    {
        return $this;
    }

    public function getRightLeaf()
    {
        return $this;
    }

    public function iterateWith($anotherNode, $callback)
    {
        $callback($this, $anotherNode);
    }

    public function isBranch()
    {
        return false;
    }
}