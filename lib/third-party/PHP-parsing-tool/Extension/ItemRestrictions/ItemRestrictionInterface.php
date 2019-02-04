<?php

namespace ParserGenerator\Extension\ItemRestrictions;

interface ItemRestrictionInterface
{
    public function check($string, $fromIndex, $toIndex, $node);
}