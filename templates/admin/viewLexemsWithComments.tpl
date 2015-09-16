{extends file="admin/layout.tpl"}

{block name=title}Lexeme cu comentarii{/block}

{block name=headerTitle}
  Lexeme cu comentarii ({$lexems|count})
{/block}

{block name=content}
  {foreach from=$lexems item=l key=row_id}
    {strip}
    <a href="{$wwwRoot}admin/lexemEdit.php?lexemId={$l->id}">
      {include file="bits/lexemName.tpl" lexem=$l}
    </a>
  {/strip}
  {$l->comment|escape}
  <br>
  {/foreach}    
{/block}
