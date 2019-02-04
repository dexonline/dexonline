<?php

namespace ParserGenerator\SyntaxTreeNode;

class Branch extends \ParserGenerator\SyntaxTreeNode\Base
{
    protected $type;
    protected $detailType;
    protected $subnodes = array();

    public function __construct($type, $detailType, $subnodes = array())
    {
        $this->type = $type;
        $this->detailType = $detailType;
        $this->subnodes = $subnodes;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($newValue) {
        $this->type = $newValue;
        return $this;
    }

    public function getDetailType() {
        return $this->detailType;
    }

    public function setDetailType($newValue) {
        $this->detailType = $newValue;
        return $this;
    }

    public function getSubnode($index) {
        return isset($this->subnodes[$index]) ? $this->subnodes[$index] : null;
    }

    public function getNestedSubnode($param1) {
        if (is_array($param1)) {
            $args = $param1;
        } else {
            $args = func_get_args();
        }
        $index = array_shift($args);

        if (empty($this->subnodes[$index])) {
            return null;
        }

        if (count($args) === 0) {
            return $this->subnodes[$index];
        } else {
            return $this->subnodes[$index] instanceof Branch ? $this->subnodes[$index]->getNestedSubnode($args) : null;
        }
    }

    public function setSubnode($index, $newValue) {
        if ($index === null) {
            $this->subnodes[] = $newValue;
        } else {
            $this->subnodes[$index] = $newValue;
        }
        return $this;
    }

    public function getSubnodes() {
        return $this->subnodes;
    }

    public function setSubnodes($subnodes) {
        $this->subnodes = $subnodes;
        return $this;
    }

    public function dump($maxNestLevel = -1, $offset = '') {
        $result = $this->type . ':' . $this->detailType;
        if ($maxNestLevel == 0) {
            return $result;
        }
        if (count($this->subnodes) == 0) {
            return $result . ' ()';
        }

        $result .= " (\n";
        foreach($this->subnodes as $i => $subnode) {
            $result .= $offset . '  ' . $i . ' = ' . $subnode->dump($maxNestLevel - 1, $offset . '  ') . "\n";
        }
        return $result . $offset . ')';
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString($mode = \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_NO_WHITESPACES)
    {
        $result = '';
        $leafs = $this->getLeafs();
        $lastLeafIndex = count($leafs) - 1;

        foreach ($leafs as $index => $leaf) {
            if ($mode === \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_REDUCED_WHITESPACES && $index == $lastLeafIndex) {
                $result .= $leaf->toString(\ParserGenerator\SyntaxTreeNode\Base::TO_STRING_NO_WHITESPACES);
            } else {
                $result .= $leaf->toString($mode);
            }
        }

        return $result;
    }

    public function getLeafs()
    {
        $result = array();
        foreach ($this->subnodes as $subnode) {
            if ($subnode instanceof \ParserGenerator\SyntaxTreeNode\Branch) {
                $result = array_merge($result, $subnode->getLeafs());
            } else {
                $result[] = $subnode;
            }
        }

        return $result;
    }

    protected function is($type)
    {
        list($type, $detailType) = explode(':', $type . ':');

        return ($this->type === $type && ($detailType === '' || $this->detailType === $detailType));
    }

    public function findAll($type, $nest = false, $childrenFirst = false)
    {
        $result = array();

        if ($this->is($type) && !$childrenFirst) {
            $result[] = $this;
        };

        if (!$this->is($type) || $nest) {
            foreach ($this->subnodes as $subnode) {
                if ($subnode instanceof \ParserGenerator\SyntaxTreeNode\Branch) {
                    $result = array_merge($result, $subnode->findAll($type, $nest, $childrenFirst));
                }
            }
        };

        if ($this->is($type) && $childrenFirst) {
            $result[] = $this;
        };

        return $result;
    }

    public function findFirst($type, $startingOnly = false)
    {
        foreach ($this->subnodes as $subnode) {
            if ($subnode instanceof \ParserGenerator\SyntaxTreeNode\Branch) {
                if ($subnode->is($type)) {
                    return $subnode;
                } elseif ($subnodeFirst = $subnode->findFirst($type)) {
                    return $subnodeFirst;
                }
            }

            if ($startingOnly) {
                return null;
            }
        }

        return null;
    }

    public function inPlaceTranslate($type, $callback, $nestedSubnodes = true, $translateThis = true)
    {
        if (!$this->is($type) || $nestedSubnodes || !$translateThis) {
            foreach ($this->subnodes as $index => $subnode) {
                if ($subnode instanceof \ParserGenerator\SyntaxTreeNode\Branch) {
                    if (!$subnode->is($type) || $nestedSubnodes) {
                        $subnode->inPlaceTranslate($type, $callback, $nestedSubnodes, false);
                    };

                    if ($subnode->is($type)) {
                        $result = $callback($subnode, $this);
                        if ($result !== null) {
                            if (is_string($result)) {
                                $this->subnodes[$index] = new \ParserGenerator\SyntaxTreeNode\Leaf($result);
                            } elseif ($result instanceof \ParserGenerator\SyntaxTreeNode\Base) {
                                $this->subnodes[$index] = $result;
                            } else {
                                throw new Exception('Result returned by callback in \ParserGenerator\SyntaxTreeNode\Branch::translate should be null|string|\ParserGenerator\SyntaxTreeNode\Base');
                            }
                        }
                    }
                }
            }
        }

        if ($this->is($type) && $translateThis) {
            $result = $callback($this, null);
            if ($result === null) {
                return $this;
            } elseif (is_string($result)) {
                return new \ParserGenerator\SyntaxTreeNode\Leaf($result);
            } else {
                return $result;
            }
        } else {
            return $this;
        }
    }

    function __clone()
    {
        foreach ($this->subnodes as $index => $subnode) {
            $this->subnodes[$index] = clone $subnode;
        }
    }

    public function translate($type, $callback, $nestedSubnodes = true)
    {
        $x = clone $this;
        $x->inPlaceTranslate($type, $callback, $nestedSubnodes);

        return $x;
    }

    public function compare($anotherNode, $compareOptions = \ParserGenerator\SyntaxTreeNode\Base::COMPARE_DEFAULT)
    {

        if (!($anotherNode instanceof  \ParserGenerator\SyntaxTreeNode\Branch)) {
            return false;
        }

        if (($compareOptions & self::COMPARE_TYPE) && ($this->type !== $anotherNode->type) ||
            ($compareOptions & self::COMPARE_SUBTYPE) && ($this->detailType !== $anotherNode->detailType) ||
            ($compareOptions & self::COMPARE_CLASS) && (get_class($this) !== get_class($anotherNode))
        ) {
            return false;
        }

        if ($compareOptions & self::COMPARE_CHILDREN_NORMAL) {
            if (count($this->subnodes) !== count($anotherNode->subnodes)) {
                return false;
            }

            foreach ($this->subnodes as $index => $subnode) {
                if (!$subnode->compare($anotherNode->subnodes[$index], $compareOptions)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function diff($anotherNode, $returnAsPair = true)
    {
        if (get_class($anotherNode) !== get_class($this) ||
            $anotherNode->type !== $this->type ||
            $anotherNode->detailType !== $this->detailType
        ) {

            if ($returnAsPair) {
                return array(array($this, $anotherNode));
            } else {
                return array($this);
            }
        } else {
            $result = array();

            foreach ($this->subnodes as $index => $subnode) {
                $result = array_merge($result, $subnode->diff($anotherNode->subnodes[$index], $returnAsPair));
            }

            return $result;
        }
    }

    public function getLeftLeaf()
    {
        return $this->subnodes[0]->getLeftLeaf();
    }

    public function getRightLeaf()
    {
        return $this->subnodes[count($this->subnodes) - 1]->getRightLeaf();
    }
	
	public function refreshOwners($recursive = true)
	{
	    foreach($this->subnodes as $subnode) {
		    $subnode->owner = $this;
			if ($subnode instanceof Branch && $recursive) {
			    $subnode->refreshOwners($recursive);
			}
		}
	}

    public function iterateWith($anotherNode, $callback)
    {
        $callback($this, $anotherNode);

        if (!($anotherNode instanceof self)) {
            return ;
        }

        foreach($this->subnodes as $i => $subnode) {
            $subnode->iterateWith(isset($anotherNode->subnodes[$i]) ? $anotherNode->subnodes[$i] : null, $callback);
        }
    }

    public function copy()
    {
        $copy = clone $this;
        if (isset($this->subnodes[0]->owner)) {
            $copy->refreshOwners();
        }
        $copy->owner = null;

        $this->iterateWith($copy, function($that, $copy) {
            $copy->origin = isset($that->origin) ? $that->origin : $that;
        });

        return $copy;
    }

    public function isBranch()
    {
        return true;
    }
}
