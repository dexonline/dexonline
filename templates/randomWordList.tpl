{extends file="layout.tpl"}

{block name=title}
  {$forms|@count} de cuvinte{$wotd} alese la întâmplare
{/block}

{block name=content}
  <h3>
    {$forms|@count} de cuvinte{$wotd} alese la întâmplare
  </h3>

  {foreach from=$forms item=form key=row_id}
    {if $row_id}|{/if}
    <a href="{$wwwRoot}definitie/{$form.0}">{$form.0}</a>
  {/foreach}
{/block}
