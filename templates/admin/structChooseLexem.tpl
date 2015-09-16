{extends file="admin/layout.tpl"}

{block name=title}Lexeme ușor de structurat{/block}

{block name=headerTitle}
  Lexeme ușor de structurat ({$lexems|count})
{/block}

{block name=content}
  {foreach from=$lexems key=i item=l}
    <a href="lexemEdit?lexemId={$l->id}">{$l->formNoAccent}</a>
    <div class="blDefinitions">
      {foreach from=$searchResults[$i] item=row}
        {$row->definition->htmlRep} <span class="defDetails">{$row->source->shortName}</span><br/>
      {/foreach}
    </div>
  {/foreach}
{/block}
