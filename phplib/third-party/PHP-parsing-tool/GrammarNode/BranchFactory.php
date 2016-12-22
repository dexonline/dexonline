<?php

namespace ParserGenerator\GrammarNode;

class BranchFactory
{
    const NAIVE = 'naive';
	const FULL = 'full';
	const PEG = 'PEG';

    public static function createBranch($branchType, $name)
	{
	    switch ($branchType) {
		    case self::NAIVE:
			    return new NaiveBranch($name);
		    case self::FULL:
			    return new Branch($name);
		    case self::PEG:
			    return new PEGBranch($name);
		    default:
			    throw new \Exception("Unknown branch type: $branchType");
		}
	}
}