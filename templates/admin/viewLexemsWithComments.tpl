{extends file="admin/layout.tpl"}

{block name=title}Lexeme cu comentarii{/block}

{block name=headerTitle}
  Lexeme cu comentarii ({$lexems|count})
{/block}

{block name=content}
  {foreach from=$lexems item=l key=row_id}
    {include file="bits/lexemLink.tpl" lexem=$l}
    <ul><li>{$l->comment|escape|regex_replace:'/]]\r?\n(?!$)/':']]</li><li>'}</li></ul>
    <br>
  {/foreach}    
{/block}
