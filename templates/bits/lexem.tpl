{* Argument: $lexem *}
{$lexem->getPartOfSpeeech()}

{if $lexem->compound}
  compus
{else}
  ({$lexem->modelType}{$lexem->modelNumber})
{/if}

{if $sUser && ($sUser->moderator & User::PRIV_EDIT)}
  <a href="{$wwwRoot}admin/dispatchModelAction.php?editModel=1&amp;modelType={$lexem->modelType}&amp;modelNumber={$lexem->modelNumber}"
     title="editează modelul">
    <i class="glyphicon glyphicon-pencil"></i>    
  </a>
{/if}
{if $lexem->notes}
  <br>
  <span class="lexemNotes">{$lexem->notes|escape}</span>
{/if}
{include "bits/locInfo.tpl" isLoc=$lexem->isLoc}
{include "bits/lexemSources.tpl" lexem=$lexem}
