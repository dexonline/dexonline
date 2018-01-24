{$accent=$accent|default:false}
{$class=$class|default:''}
{$model=$model|default:true}
{strip}
  <a href="{$wwwRoot}admin/lexemeEdit.php?lexemeId={$lexeme->id}" class="{$class}" title="editează">
    {include "bits/lexemeName.tpl"}
  </a>
{/strip}

{if $model}
  ({$lexeme->modelType}{$lexeme->modelNumber}{$lexeme->restriction})
{/if}
