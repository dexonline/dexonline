{strip}
<tr id="{$e->id}" data-row-id="{$e->id}">
  <td class="col-md-2">
    {include "bits/entry.tpl" entry=$e editLink=true}
  </td>
  <td class="col-md-6 box-padding">
      {foreach $e->getMainLexemes() as $lexeme}
        {* create an array of properties*}
        {$props[] = "{$lexeme->modelType}{$lexeme->modelNumber}{$lexeme->restriction}"}
        {include "bits/lexemeLink.tpl" boxed=true}
      {/foreach}
  </td>
  <td class="col-md-1 text-right">
    {', '|implode:$e->getUniqueProps('mainLexemes', ['modelType', 'modelNumber', 'restriction'])}
  </td>
  <td class="col-md-1 text-right userNick">{$e->nick}</td>
  <td class="col-md-1 text-right">{$e->modDate|date_format:"%d.%m.%Y"}</td>
</tr>
{/strip}
