{$accent=$accent|default:false}
{$class=$class|default:''}
{$model=$model|default:true}
{strip}
  <a href="{$wwwRoot}admin/lexemEdit.php?lexemeId={$lexem->id}" class="{$class}" title="editează">
    {include "bits/lexemName.tpl"}
  </a>
{/strip}

{if $model}
  ({$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction})
{/if}
