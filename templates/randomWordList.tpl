{extends "layout.tpl"}

{block "title"}
  {'%d randomly chosen %s'|_|sprintf:count($forms):$type}
{/block}

{block "content"}
  <h3>
    {'%d randomly chosen %s'|_|sprintf:count($forms):$type}
  </h3>

  {foreach $forms as $row_id => $form}
    {if $row_id}|{/if}
    <a href="{$wwwRoot}definitie/{$form.0}">{$form.0}</a>
  {/foreach}
{/block}
