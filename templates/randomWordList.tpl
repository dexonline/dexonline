{extends file="layout.tpl"}

{block name=title}
  O listă de {$forms|@count} de cuvinte{$wotd} alese la întâmplare
{/block}

{block name=content}
  <p class="paragraphTitle">
    O listă de {$forms|@count} de cuvinte{$wotd} alese la întâmplare
  </p>
  {foreach from=$forms item=form key=row_id}
    {if $row_id}|{/if}
    <a href="{$wwwRoot}definitie/{$form.0}">{$form.0}</a>
  {/foreach}
{/block}
