{strip}
<tr id="{$e->id}" data-row-id="{$e->id}">
  <td class="col-md-2">
    {include "bits/entry.tpl" entry=$e editLink=true}
  </td>
  <td class="col-md-6 box-padding">
      {foreach $e->getMainLexemes() as $lexeme}
        {include "bits/lexemeLink.tpl" boxed=true}
      {/foreach}
  </td>
  <td class="col-md-1 text-right">
    {', '|implode:$e->getUniqueModelsMain()}
  </td>
  <td class="col-md-1 text-right userNick">{$e->nick}</td>
  <td class="col-md-1 text-right">{$e->modDate|date_format:"%d.%m.%Y"}</td>
</tr>
{/strip}
