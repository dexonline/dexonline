<?php
/**
 * Created by PhpStorm.
 * User: Rafał
 * Date: 09.03.14
 * Time: 13:01
 */

namespace ParserGenerator\GrammarNode;


class ErrorTrackDecorator extends Decorator
{
    protected $maxCheck = -1;

    public function rparse($string, $fromIndex, $restrictedEnd)
    {
        $result = $this->node->rparse($string, $fromIndex, $restrictedEnd);

        if (!$result && $fromIndex > $this->maxCheck) {
            $this->maxCheck = $fromIndex;
        }

        return $result;
    }

    public function getMaxCheck() {
        return $this->maxCheck === -1 ? null : $this->maxCheck;
    }

    public function reset() {
        $this->maxCheck = -1;
    }
} 