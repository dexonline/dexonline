{foreach from=$lexems item=l key=row_id}
  {include file="bits/lexemLink.tpl" lexem=$l}
  {strip}
  (
  {foreach from=$l->getLexemModels() item=lm name=loop}
    {$lm->modelType}{$lm->modelNumber}{$lm->restriction}
    {if !$smarty.foreach.loop.last} / {/if}
  {/foreach}
  )
  {/strip} |
{/foreach}    
