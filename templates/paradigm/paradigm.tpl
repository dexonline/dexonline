{* Argument: $lexeme *}
{$modelType=$lexeme->getModelType()}
{include "paradigm/paradigm`$modelType->canonical`.tpl" lexeme=$lexeme}
