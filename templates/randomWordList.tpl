{extends file="layout.tpl"}

{block name=title}{$page_title}{/block}

{block name=content}
  <p class="paragraphTitle">{$page_title}</p>
  {foreach from=$forms item=form key=row_id}
    {if $row_id}|{/if}
    <a href="{$wwwRoot}definitie/{$form.0}">{$form.0}</a>
  {/foreach}
{/block}
