<?php

namespace ParserGenerator\Examples;

class ArithmeticExpressionParser extends \ParserGenerator\Parser
{
    public function __construct()
    {
        parent::__construct($this->getExpressionDefinition(), array('ignoreWhitespaces' => true));
    }

    public function getExpressionDefinition()
    {
        return '
            start   :=> expr.
            expr:add => expr "+" expr
                :sub => expr "-" expr
                :mul => expr "*" expr
                :div => expr "/" expr
                :bra => "(" expr ")"
                :val => -inf..inf.
        ';
    }

    public function getValue($inputString)
    {
        $expr = $this->parse($inputString);
        if ($expr) {
            return $this->getExpressionValue($expr->getSubnode(0));
        } else {
            throw new Exception('Cannot parse arithmetic expression.');
        }
    }

    protected function getExpressionValue($expr)
    {
        switch ($expr->getDetailType()) {
            case "add":
                return $this->getExpressionValue($expr->getSubnode(0)) + $this->getExpressionValue($expr->getSubnode(2));
            case "sub":
                return $this->getExpressionValue($expr->getSubnode(0)) - $this->getExpressionValue($expr->getSubnode(2));
            case "mul":
                return $this->getExpressionValue($expr->getSubnode(0)) * $this->getExpressionValue($expr->getSubnode(2));
            case "div":
                return $this->getExpressionValue($expr->getSubnode(0)) / $this->getExpressionValue($expr->getSubnode(2));
            case "bra":
                return $this->getExpressionValue($expr->getSubnode(1));
            case "val":
                return $expr->getSubnode(0)->getValue();
        }
    }
}