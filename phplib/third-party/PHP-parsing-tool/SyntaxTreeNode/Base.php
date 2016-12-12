<?php

namespace ParserGenerator\SyntaxTreeNode;

abstract class Base
{
    const COMPARE_TYPE = 1;
    const COMPARE_SUBTYPE = 2;
    const COMPARE_CLASS = 4;
    const COMPARE_LEAF = 8;
    const COMPARE_CHILDREN_NORMAL = 16;
    const COMPARE_DEFAULT = 31; //self::COMPARE_TYPE | self::COMPARE_SUBTYPE | self::COMPARE_CLASS | self::COMPARE_LEAF | self::COMPARE_CHILDREN_NORMAL;

    const TO_STRING_ORIGINAL = 1;
    const TO_STRING_NO_WHITESPACES = 2;
    const TO_STRING_REDUCED_WHITESPACES = 4;

    abstract public function getLeftLeaf();
    abstract public function getRightLeaf();
    abstract public function toString($mode = \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_NO_WHITESPACES);

    public function setAfterContent($newValue) {
        $this->getRightLeaf()->afterContent = $newValue;
        return $this;
    }

    public function getAfterContent() {
        return $this->getRightLeaf()->afterContent;
    }
}