{* Argument: $lexeme *}
{assign var=modelType value=$lexeme->getModelType()}
{include "paradigm/paradigm`$modelType->canonical`.tpl" lexem=$lexem}
