<?php

namespace ParserGenerator\GrammarNode;

class Branch extends \ParserGenerator\GrammarNode\BaseNode implements \ParserGenerator\GrammarNode\BranchInterface
{
    static $spc = '';
    public $ignoreWhitespaces = false;

    protected $parser;
    protected $nodeName;
    protected $node;
    public $startCharsCache;

    public function __construct($nodeName)
    {
        $this->nodeName = $nodeName;
    }

    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        $cacheStr = $fromIndex . '-' . $this->nodeName . '-' . implode(',', $restrictedEnd);
        $lastResult = 31;

        if (isset($this->parser->cache[$cacheStr])) {
            if (is_int($this->parser->cache[$cacheStr])) {
                $this->parser->cache[$cacheStr] = false;
            } else {
                return $this->parser->cache[$cacheStr];
            }
        } else {
            $this->parser->cache[$cacheStr] = 0;
        }
        beforeForeach:
        foreach ($this->node as $_optionIndex => $option) {
            $subnodes = array();
            $optionIndex = 0;
            $indexes = array(-1 => $fromIndex);
            $optionCount = count($option);
            //!!! TODO:
            for($i =0; $i < $optionCount; $i++) {
                $restrictedEnds[$i] = array();
            }
            $restrictedEnds[$optionCount - 1] = $restrictedEnd;
            while (true) {
                $subNode = $option[$optionIndex]->rparse($string, $indexes[$optionIndex - 1], $restrictedEnds[$optionIndex]);
                if ($subNode) {
                    $subNodeOffset = $subNode['offset'];
                    $subnodes[$optionIndex] = $subNode['node'];
                    $restrictedEnds[$optionIndex][$subNodeOffset] = $subNodeOffset;
                    $indexes[$optionIndex] = $subNodeOffset;
                    if (++$optionIndex === $optionCount) {
                        break;
                    };
                } elseif ($optionIndex-- === 0) {
                    continue 2;
                }
            }
            // match
            $index = $indexes[$optionCount - 1];
            $node = new \ParserGenerator\SyntaxTreeNode\Branch($this->nodeName, $_optionIndex, $subnodes);
            $r = array('node' => $node, 'offset' => $index);
            $this->parser->cache[$cacheStr] = $r;
            if ($r != $lastResult) {
                $lastResult = $r;
                goto beforeForeach;
            }
            return $r;
        }
        return false;
    }

    public function setParser($parser)
    {
        $this->parser = $parser;
    }

    public function getParser()
    {
        return $this->parser;
    }

    public function setNode($node)
    {
        $this->node = $node;
    }

    public function getNode()
    {
        return $this->node;
    }

    public function getNodeName()
    {
        return $this->nodeName;
    }

    public function __toString()
    {
        return $this->getNodeName();
    }
}