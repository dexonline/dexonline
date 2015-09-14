{foreach from=$lexems item=l key=row_id}
  <a href="{$wwwRoot}admin/lexemEdit.php?lexemId={$l->id}">{include file="bits/lexemName.tpl" lexem=$l}</a>
  {strip}
    (
    {foreach from=$l->getLexemModels() item=lm name=loop}
      {$lm->modelType}{$lm->modelNumber}{$lm->restriction}
      {if !$smarty.foreach.loop.last} / {/if}
    {/foreach}
    )
  {/strip} |
{/foreach}    
