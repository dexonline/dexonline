{extends file="widgets/layout.tpl"}

{block name="widget-header"}
  Articolul lunii
{/block}

{block name="widget-body"}
  <img alt="articolul lunii" src="{$cfg.static.url}img/wotd/thumb/misc/papirus.png">
  <a href="{$wwwRoot}articol/{$articol}">{$articol|replace:'_':' '}</a>
{/block}
