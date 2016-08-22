{* Argument: $lexem *}
{assign var=modelType value=$lexem->getModelType()}
{include "paradigm/paradigm`$modelType->canonical`.tpl" lexem=$lexem}
