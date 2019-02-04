<?php

namespace ParserGenerator\Extension;

class RuleCondition extends \ParserGenerator\Extension\Base
{
    public function extendGrammar($grammarGrammar)
    {
        $grammarGrammar['rule']['standard'][] = ':possibleRuleCondition';
        $grammarGrammar['possibleRuleCondition'] = array(array(':ruleCondition'), array(''));
        $grammarGrammar['ruleCondition']['standard'] = array('<?', ':/([^?]|\?[^>])+/', '?>');

        return $grammarGrammar;
    }

    function modifyBranches($grammar, $parsedGrammar, $grammarParser, $options)
    {
        foreach ($parsedGrammar->findAll('grammarBranch') as $grammarBranch) {
            $functions = array();
            foreach ($grammarBranch->findAll('rule') as $ruleIndex => $rule) {
                $ruleName = (string)$rule->findFirst('ruleName') ? : $ruleIndex;
                if ($condition = $rule->findFirst('ruleCondition')) {
                    $functions[$ruleName] = (string)$condition->getSubnode(1);
                }
            }

            if (count($functions)) {
                $branchName = (string)$grammarBranch->findFirst('branchName');
                $grammar[$branchName] = new \ParserGenerator\GrammarNode\BranchStringCondition($grammar[$branchName], $functions);
            }
        }

        return $grammar;
    }
}

\ParserGenerator\GrammarParser::$defaultPlugins[] = new RuleCondition();