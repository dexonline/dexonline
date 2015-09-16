{extends file="admin/layout.tpl"}

{block name=title}Descarcă cuvântul zilei{/block}

{block name=headerTitle}
  Descărcarea cuvintelor zilei - {$month}/{$year}
{/block}

{block name=content}
  {foreach from=$wotdSet item=t}
    {$t.wotd->displayDate|date_format:"%e %b"}
    <a href="https://dexonline.ro/definitie/{$t.def->id}">{$t.def->lexicon}</a>
    -
    {$t.wotd->description}
    <br/>
  {/foreach}
{/block}
