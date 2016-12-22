<?php

namespace ParserGenerator\Examples;

class JSONFormater extends \ParserGenerator\Parser
{
    public function __construct() {
        parent::__construct($this->getJSONDefinition(), array('ignoreWhitespaces' => true, 'defaultBranchType' => 'PEG'));
    }

    protected function getJSONDefinition() {
        return '
        start:       => value.
        value:bool   => ("true"|"false")
             :string => string
             :number => -inf..inf
             :array  => "[" value*"," "]"
             :object => "{" objValue*"," "}".
        objValue:    => key ":" value.
        key:         => string
        ';
    }

    public function setObjectsPropertiesOrder($node) {
        $node->inPlaceTranslate('value:object', function ($node) {
            $node->getSubnode(1)->orderBy('key');
        });
    }

    public function setIndention($node, $indention = '    ', $start = "\n") {
        if ($node->getType() === 'start') {
            foreach($node->getLeafs() as $leaf) {
                $leaf->setAfterContent((string) $leaf == ':' ? ' ' : '');
            }

            return $this->setIndention($node->getSubnode(0), $indention, $start);
        } elseif ($node->getType() !== 'value') {
            throw new \Exception('Function JSONFormater::setIndention can be used only on nodes with type start or value');
        }

        if ($node->getDetailType() == 'array' || $node->getDetailType() == 'object') {
            $node->getSubnode(0)->setAfterContent($start . $indention);

            $collection = $node->getSubnode(1);

            foreach($collection->getSeparators() as $separator) {
                $separator->setAfterContent($start . $indention);
            }

            $collection->setAfterContent($start);

            foreach($collection->getMainNodes() as $collectionNode) {
                if ($node->getDetailType() === 'array') {
                    $this->setIndention($collectionNode, $indention, $start . $indention);
                } else {
                    $this->setIndention($collectionNode->getSubnode(2), $indention, $start . $indention);
                }
            }
        }
    }
}