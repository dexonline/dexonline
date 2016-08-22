{* Argument: $lexem *}
{assign var=modelType value=$lexem->getModelType()}
{$modelType->description} ({$modelType->code}{$lexem->modelNumber})
{if $sUser && ($sUser->moderator & $smarty.const.PRIV_EDIT)}
  <a class="paraEdit"
     href="{$wwwRoot}admin/dispatchModelAction.php?editModel=1&amp;modelType={$lexem->modelType}&amp;modelNumber={$lexem->modelNumber}"
     title="editează modelul">&nbsp;
  </a>
{/if}
{if $lexem->notes}
  <br>
  <span class="lexemNotes">{$lexem->notes|escape}</span>
{/if}
{include "bits/locInfo.tpl" isLoc=$lexem->isLoc}
{include "bits/lexemSources.tpl" lexem=$lexem}
