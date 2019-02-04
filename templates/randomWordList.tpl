{extends "layout.tpl"}

{block "title"}{$title}{/block}

{block "content"}
  <h3>{$title}</h3>

  {foreach $forms as $row_id => $form}
    {if $row_id}|{/if}
    <a href="{Config::URL_PREFIX}definitie/{$form.0}">{$form.0}</a>
  {/foreach}
{/block}
