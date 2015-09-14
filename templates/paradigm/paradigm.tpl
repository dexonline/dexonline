{* Argument: $lexemModel *}
{assign var=modelType value=$lexemModel->getModelType()}
{include file="paradigm/paradigm`$modelType->canonical`.tpl" lexemModel=$lexemModel}
