{extends file="admin/layout.tpl"}

{block name=title}Intrări ușor de structurat{/block}

{block name=headerTitle}
  Intrări ușor de structurat ({$entries|count})
{/block}

{block name=content}
  {foreach from=$entries key=i item=e}
    {include file="bits/entryLink.tpl" entry=$e}
    <div class="blDefinitions">
      {foreach from=$searchResults[$i] item=row}
        {$row->definition->htmlRep} <span class="defDetails">{$row->source->shortName}</span><br/>
      {/foreach}
    </div>
  {/foreach}
{/block}
