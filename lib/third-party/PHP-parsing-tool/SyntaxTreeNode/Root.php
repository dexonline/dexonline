<?php

namespace ParserGenerator\SyntaxTreeNode;

use \ParserGenerator\SyntaxTreeNode\Base;

class Root extends \ParserGenerator\SyntaxTreeNode\Branch
{
	protected $beforeContent = '';

	public function __construct($type, $detailType, $subnodes = array(), $beforeContent = '')
    {
        parent::__construct($type, $detailType, $subnodes);
        $this->beforeContent = $beforeContent;
    }

    public function setBeforeContent($newValue) {
        $this->beforeContent = $newValue;
        return $this;
    }

    public function getBeforeContent() {
        return $this->beforeContent;
    }

	public function toString($mode = Base::TO_STRING_NO_WHITESPACES)
    {
        return ($mode == Base::TO_STRING_ORIGINAL ? $this->beforeContent : '') . parent::toString($mode);
    }

    public static function createFromPrototype(\ParserGenerator\SyntaxTreeNode\Branch $prototype)
    {
    	return new self($prototype->type, $prototype->detailType, $prototype->subnodes);
    }
}