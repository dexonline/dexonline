{$accent=$accent|default:false}
{$model=$model|default:true}
{strip}
  <a href="{$wwwRoot}admin/lexemEdit.php?lexemId={$lexem->id}" title="editează">
    {include "bits/lexemName.tpl"}
  </a>
{/strip}

{if $model}
  ({$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction})
{/if}
