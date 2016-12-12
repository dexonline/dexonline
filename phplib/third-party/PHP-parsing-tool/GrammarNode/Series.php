<?php

namespace ParserGenerator\GrammarNode;

class Series extends \ParserGenerator\GrammarNode\BranchDecorator
{
    protected $resultType = 'list';
    protected $resultDetailType = '';
    protected $tmpNodeName;
    protected $mainNode;
    protected $separator;
    protected $from0;
    protected $greedy;
    protected $parser;

    public function __construct($mainNode, $separator, $from0, $greedy)
    {
        $this->mainNode = $mainNode;
        $this->separator = $separator;
        $this->from0 = $from0;
        $this->greedy = $greedy;
        $this->tmpNodeName = '&series/' . spl_object_hash($this);

        if ($mainNode instanceof \ParserGenerator\GrammarNode\BranchInterface) {
            $this->resultDetailType = $mainNode->getNodeName();
        } elseif ($mainNode instanceof \ParserGenerator\GrammarNode\Text) {
            $this->resultDetailType = $mainNode->getString();
        } elseif ($mainNode instanceof \ParserGenerator\GrammarNode\Regex) {
            $this->resultDetailType = $mainNode->getRegex();
        } elseif ($mainNode instanceof \ParserGenerator\GrammarNode\AnyText) {
            $this->resultDetailType = 'text';
        } else {
            $this->resultDetailType = '';
        }

        $this->node = new \ParserGenerator\GrammarNode\Branch($this->tmpNodeName);

        $ruleGo = $separator ? array($mainNode, $separator, $this->node) : array($mainNode, $this->node);
        $ruleStop = array($mainNode);

        if ($greedy) {
            $node = array('go' => $ruleGo, 'stop' => $ruleStop);
        } else {
            $node = array('stop' => $ruleStop, 'go' => $ruleGo);
        }

        $this->node->setNode($node);
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        if ($this->from0 && !$this->greedy && !isset($restrictedEnd[$fromIndex])) {
            return array('node' => new \ParserGenerator\SyntaxTreeNode\Series($this->resultType, $this->resultDetailType, array(), (bool) $this->separator), 'offset' => $fromIndex);
        }

        if ($rparseResult = $this->node->rparse($string, $fromIndex, $restrictedEnd)) {
            $rparseResult['node'] = $this->getFlattenNode($rparseResult['node']);
            return $rparseResult;
        }

        if ($this->from0 && !isset($restrictedEnd[$fromIndex])) {
            return array('node' => new \ParserGenerator\SyntaxTreeNode\Series($this->resultType, $this->resultDetailType, array(), (bool) $this->separator), 'offset' => $fromIndex);
        }

        return false;
    }

    protected function getFlattenNode($ast)
    {
        $astSubnodes = array();
        while ($ast->getDetailType() == 'go') {
            $astSubnodes[] = $ast->getSubnode(0);
            if ($this->separator) {
                $astSubnodes[] = $ast->getSubnode(1);
                $ast = $ast->getSubnode(2);
            } else {
                $ast = $ast->getSubnode(1);
            }
        }
        $astSubnodes[] = $ast->getSubnode(0);

        return new \ParserGenerator\SyntaxTreeNode\Series($this->resultType, $this->resultDetailType, $astSubnodes, (bool) $this->separator);
    }
	
	public function getNode()
	{
       $node = $this->separator ? array(array($this->mainNode, $this->separator)) : array(array($this->mainNode));
       if ($this->from0) {
           $node[] = array();
       }
	   return $node;
	}

    public function getMainNode() {
        return $this->mainNode;
    }

    public function __toString() {
        $op = array(array('+', '++'), array('*', '**'));
        return $this->mainNode . $op[$this->from0][$this->greedy] . ($this->separator ?: '');
    }
}