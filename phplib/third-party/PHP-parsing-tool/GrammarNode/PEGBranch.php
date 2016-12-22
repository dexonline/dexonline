<?php

namespace ParserGenerator\GrammarNode;

class PEGBranch extends \ParserGenerator\GrammarNode\Branch
{
    public function rparse($string, $fromIndex = 0, $restrictedEnd = array())
    {
        $cacheStr = $fromIndex . '-' . $this->nodeName;

        if (!isset($this->parser->cache[$cacheStr])) {
			foreach ($this->node as $_optionIndex => $option) {
				$index = $fromIndex;
				$subnodes = array();
				
				foreach($option as $sequenceItem) {
					$subnode = $sequenceItem->rparse($string, $index, array());
					if ($subnode) {
						$subnodes[] = $subnode['node'];
						$index = $subnode['offset'];
					} else {
						continue 2;
					}
				}

				$node = new \ParserGenerator\SyntaxTreeNode\Branch($this->nodeName, $_optionIndex, $subnodes);
				$r = array('node' => $node, 'offset' => $index);
				$this->parser->cache[$cacheStr] = $r;
				return isset($restrictedEnd[$index]) ? false : $r;
			}
			
			$this->parser->cache[$cacheStr] = false;
			return false;
		}
		
        $r = $this->parser->cache[$cacheStr];
		if ($r !== false && !isset($restrictedEnd[$r['offset']])) {
            return $r;
        } else {
            return false;
        }		
    }
}