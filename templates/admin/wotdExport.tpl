{foreach from=$wotdSet item=t}
  {$t.wotd->displayDate|date_format:"%e %b"}
  <a href="https://dexonline.ro/definitie/{$t.def->id}">{$t.def->lexicon}</a>
  -
  {$t.wotd->description}
  <br/>
{/foreach}
