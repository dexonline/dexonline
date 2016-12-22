<?php

namespace ParserGenerator\SyntaxTreeNode;

class Series extends \ParserGenerator\SyntaxTreeNode\Branch {
    protected $isWithSeparator;

    public function __construct($type, $detailType, $subnodes = array(), $isWithSeparator = false)
    {
        parent::__construct($type, $detailType, $subnodes);
        $this->isWithSeparator = $isWithSeparator;
    }

    public function getMainNodes()
    {
        if (!$this->isWithSeparator) {
            return $this->subnodes;
        }

        $subnodesCount = count($this->subnodes);
        $result = array();

        for($i = 0; $i < $subnodesCount; $i += 2) {
            $result[] = $this->subnodes[$i];
        }

        return $result;
    }

    public function getSeparators() {
        if (!$this->isWithSeparator) {
            return array();
        }

        $subnodesCount = count($this->subnodes);
        $result = array();

        for($i = 1; $i < $subnodesCount; $i += 2) {
            $result[] = $this->subnodes[$i];
        }

        return $result;
    }

    public function isWithSeparator()
    {
        return $this->isWithSeparator;
    }

    public function orderBy($callback = null)
    {
        if (is_string($callback)) {
            $compareBy = $callback;
            $callback = function ($a, $b) use ($compareBy) {
                return strnatcmp((string) $a->findFirst($compareBy), (string) $b->findFirst($compareBy));
            };
        } elseif ($callback === null) {
            $callback = function ($a, $b) {
                return strnatcmp((string) $a, (string) $b);
            };
        }

        $mainNodes = $this->getMainNodes();
        usort($mainNodes, $callback);

        foreach($mainNodes as $index => $node) {
            $this->subnodes[$index * 2] = $node;
        }
    }

    public function findFirstInMainNodes($type, $addNullValues = false) {
        $result = array();

        foreach($this->getMainNodes() as $node) {
            if ($node instanceof \ParserGenerator\SyntaxTreeNode\Branch) {
                $value = $node->findFirst($type);
            } else {
                $value = null;
            }

            if ($value || $addNullValues) {
                $result[] = $value;
            }
        }

        return $result;
    }
}