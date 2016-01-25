{* Argument: $lexemModel *}
{assign var=modelType value=$lexemModel->getModelType()}
{$modelType->description} ({$modelType->code}{$lexemModel->modelNumber})
{if $sUser && ($sUser->moderator & $smarty.const.PRIV_LOC)}
  <a class="paraEdit"
     href="{$wwwRoot}admin/dispatchModelAction.php?editModel=1&amp;modelType={$lexemModel->modelType}&amp;modelNumber={$lexemModel->modelNumber}"
     title="editează modelul">&nbsp;
  </a>
{/if}
{if $lexemModel->tags}
  <br>
  <span class="lexemTags">{$lexemModel->tags|escape}</span>
{/if}
{include file="bits/locInfo.tpl" isLoc=$lexemModel->isLoc}
{include file="bits/lexemSources.tpl" lexemModel=$lexemModel}
